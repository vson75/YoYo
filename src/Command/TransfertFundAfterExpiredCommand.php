<?php

namespace App\Command;

use App\Entity\PostStatus;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;

class TransfertFundAfterExpiredCommand extends Command
{
    protected static $defaultName = 'app:transfert-fund-after-expired';
    private $postRepository;
    private $mailer;
    private $em;
    private $transactionRepository;

    public function __construct(PostRepository $postRepository, Mailer $mailer, EntityManagerInterface $em, TransactionRepository $transactionRepository)
    {
        parent::__construct(null);
        $this->postRepository = $postRepository;
        $this->mailer = $mailer;
        $this->em = $em;
        $this->transactionRepository = $transactionRepository;

    }

    protected function configure()
    {
        $this
            ->setDescription('Transfert fund after expired date')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $expiredPost = $this->postRepository->findAllExpiredDate();


        $output->writeln([
            '============'
        ]);
        foreach ($expiredPost as $expiredPost){

            $userId = $expiredPost->getUser();

            $amountCollected = $expiredPost->getTransactionSum();
            $targetAmount = $expiredPost->getTargetAmount();


            // if Amount collected greater then target amount
            if($targetAmount <= $amountCollected){

                // update status to transfert Fund

                $repo = $this->em->getRepository(PostStatus::class);
                $postStep = $repo->findOneBy([
                    'id' => PostStatus::POST_TRANSFERT_FUND
                ]);
                $expiredPost->setStatus($postStep);
                $this->em->persist($expiredPost);
                $this->em->flush();


                //create excel
                $excelFile = new Spreadsheet();
                $sheet = $excelFile->getActiveSheet();

                //style of Header and content
                $styleArrayHeader = [
                    'font' => [
                        'bold' => true,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ],
                    'borders' => [
                        'top' => [
                            'borderStyle' => Border::BORDER_THICK
                        ],
                        'right' => [
                            'borderStyle' => Border::BORDER_THICK
                        ],
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THICK
                        ],
                        'left' => [
                            'borderStyle' => Border::BORDER_THICK
                        ],
                    ]
                ];

                $styleArrayContent = [
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT
                    ],
                    'borders' => [
                        'right' => [
                            'borderStyle' => Border::BORDER_THIN
                        ],
                        'bottom' => [
                            'borderStyle' => Border::BORDER_THIN
                        ],
                        'left' => [
                            'borderStyle' => Border::BORDER_THIN
                        ],
                    ]
                ];

                //put value

                $sheet->setCellValue('A1', 'Summary of donation');
                $sheet->setCellValue('A2', 'Project: '.$expiredPost->getTitle());
                   // $sheet->getActiveSheet()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
                $sheet->setCellValue('A4', 'Last and first name of the donation');

                $sheet->setCellValue('B4', 'Amount');
                $sheet->setCellValue('C4', 'Date of transaction');

                $sheet->setCellValue('A5','Anonymous donation');
                $sheet->setCellValue('B5',$expiredPost->getTransactionAnonymousSum());

                // apply style
                $sheet->getStyle('A4')->applyFromArray($styleArrayHeader);
                $sheet->getStyle('B4')->applyFromArray($styleArrayHeader);
                $sheet->getStyle('C4')->applyFromArray($styleArrayHeader);

                $sheet->getStyle('A5')->applyFromArray($styleArrayContent);
                $sheet->getStyle('B5')->applyFromArray($styleArrayContent);
                $sheet->getStyle('C5')->applyFromArray($styleArrayContent);

                $sheet->getStyle('A2')->getAlignment()->setWrapText(true);

                $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB(Color::COLOR_GREEN);
                $sheet->getColumnDimension('A')->setAutoSize(true);
                $sheet->getColumnDimension('B')->setAutoSize(true);
                $sheet->getColumnDimension('C')->setAutoSize(true);


                // find information of not anonymous transaction to Add in excel
                $arrayTransaction = $this->transactionRepository->getNotAnonymousByPost($expiredPost);


                for ($i=0; $i < count($arrayTransaction); $i++) {
                    $col = $i + 6;
                    // add data in excel file
                    $donatorInfo = $arrayTransaction[$i]->getUser()->getFirstname().' '.$arrayTransaction[$i]->getUser()->getLastname();
                    $sheet->setCellValue('A'.$col, $donatorInfo);
                    $sheet->setCellValue('B'.$col, $arrayTransaction[$i]->getAmount());
                    $sheet->setCellValue('C'.$col, $arrayTransaction[$i]->getTransfertAt());

                    $sheet->getStyle('A'.$col)->applyFromArray($styleArrayContent);
                    $sheet->getStyle('B'.$col)->applyFromArray($styleArrayContent);
                    $sheet->getStyle('C'.$col)->applyFromArray($styleArrayContent);
                  //  $io->success($arrayTransaction[$i]->getUser()->getFirstname());

                }
                // send email recap with detail of each transaction


                $sheet->setTitle("Detail of donation");

                // Create your Office 2007 Excel (XLSX Format)
                $writer = new Xlsx($excelFile);

                // Create a Temporary file in the system
                $fileName = 'Public/tempFile/Summary_Fund_Collected.xlsx';
                // Create the excel file in the tmp directory of the system
                $writer->save($fileName);

                $this->mailer->sendMailAfterExpiredPost($userId,$expiredPost,$fileName);

            }else{
                // if we aren't collect our target amount
                // send mail to organisateur, they can chose to continue or renew their post
                $this->mailer->sendMailAfterExpiredPost($userId,$expiredPost,null);
             //   $io->success($expiredPost->getId());

            }


        }


        return 0;
    }
}
