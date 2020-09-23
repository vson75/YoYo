<?php

namespace App\Command;

use App\Entity\PostDateHistoric;
use App\Entity\PostDateType;
use App\Entity\PostStatus;
use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use App\Service\Mailer;
use App\Service\PostDateHistoricService;
use App\Service\SpreadsheetService;
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
    private $postDateHistoric;
    private $spreadsheetService;

    public function __construct(PostRepository $postRepository, Mailer $mailer, EntityManagerInterface $em, TransactionRepository $transactionRepository, PostDateHistoricService $postDateHistoric, SpreadsheetService $spreadsheetService)
    {
        parent::__construct(null);
        $this->postRepository = $postRepository;
        $this->mailer = $mailer;
        $this->em = $em;
        $this->transactionRepository = $transactionRepository;
        $this->postDateHistoric = $postDateHistoric;
        $this->spreadsheetService = $spreadsheetService;

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

        // find Expired project after 1 day finishAt
        $expiredPosts = $this->postRepository->findAllExpiredDate();

        //admin user
        $userRepo = $this->em->getRepository(User::class);
        $admin = $userRepo->findAdminUserByASC();

        $output->writeln([
            '============'
        ]);

        foreach ($expiredPosts as $expiredPost){

            $userId = $expiredPost->getUser();

            $amountCollected = $expiredPost->getTransactionSum();
            $targetAmount = $expiredPost->getTargetAmount();



            // if Amount collected greater then target amount
            if($targetAmount <= $amountCollected){

                // update status to transfert Fund

                $repo = $this->em->getRepository(PostStatus::class);
                $postStep = $repo->findOneBy([
                    'id' => PostStatus::POST_FINISH_COLLECTING
                ]);
                $expiredPost->setStatus($postStep);
                $this->em->persist($expiredPost);
                $this->em->flush();

                //update the date in historic Post
                $this->postDateHistoric->InsertNewPostDateHistorical($expiredPost,$admin, PostDateType::Date_end_collect_fund, null);
                //create excel
                
                $excel_summary_file = $this->spreadsheetService->CreateSummaryDonateByPost($expiredPost);

                $this->mailer->sendMailToAuthorWhenFinishedCollectingPost($userId,$expiredPost,$excel_summary_file);

                // get list distinct of the donator  and send mail to annonce that we are finish the "collect step":

                $ArrayUserDonate = $this->transactionRepository->findDistinctDonatorByPost($expiredPost);
                // for each user donate in the post, send Email alert
                foreach ($ArrayUserDonate as $userId){
                    $output->writeln($userId);
                    $user = $this->em->getRepository(User::class)->findOneBy([
                        'id' => $userId
                    ]);

                    $this->mailer->sendMailToAllFundedUser($user, $expiredPost);
                }
            }else{

                $this->mailer->sendMailToAuthorWhenFinishedCollectingPost($userId,$expiredPost,null);

            }


        }


        return 0;
    }
}
