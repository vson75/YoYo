<?php
namespace App\Service;

use App\Entity\Post;
use App\Repository\TransactionRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SpreadsheetService
{
    private $transactionRepository;

    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }

    public function CreateSummaryDonateByPost(Post $post)
    {
        $excelFile = new Spreadsheet();
        $sheet = $excelFile->getActiveSheet();

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
        $sheet->setCellValue('A2', 'Project: '.$post->getTitle());
           // $sheet->getActiveSheet()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
        $sheet->setCellValue('A4', 'Last and first name of the donation');

        $sheet->setCellValue('B4', 'Amount');
        $sheet->setCellValue('C4', 'Date of transaction');

        $sheet->setCellValue('A5','Anonymous donation');
        $sheet->setCellValue('B5',$post->getTransactionAnonymousSum());

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


                $arrayTransaction = $this->transactionRepository->getNotAnonymousByPost($post);


                for ($i=0; $i < count($arrayTransaction); $i++) {
                    $col = $i + 6;

                    $donatorInfo = $arrayTransaction[$i]->getUser()->getFirstname().' '.$arrayTransaction[$i]->getUser()->getLastname();
                    $sheet->setCellValue('A'.$col, $donatorInfo);
                    $sheet->setCellValue('B'.$col, $arrayTransaction[$i]->getAmountAfterFees());
                    $sheet->setCellValue('C'.$col, $arrayTransaction[$i]->getTransfertAt());

                    $sheet->getStyle('A'.$col)->applyFromArray($styleArrayContent);
                    $sheet->getStyle('B'.$col)->applyFromArray($styleArrayContent);
                    $sheet->getStyle('C'.$col)->applyFromArray($styleArrayContent);

                }

                $lastcol = count($arrayTransaction) + 7;
                $sheet->setCellValue('A'.$lastcol, 'Total');
                $sheet->setCellValue('B'.$lastcol, round($this->transactionRepository->getTotalAmountbyPost($post),2));
                // send email recap with detail of each transaction


                $sheet->setTitle("Detail of donation");

                // Create your Office 2007 Excel (XLSX Format)
                $writer = new Xlsx($excelFile);

                // Create a Temporary file in the system
                $fileName = 'Public/tempFile/Summary_Fund_Collected_' .$post->getUniquekey() .'.xlsx';
                // Create the excel file in the tmp directory of the system
                $writer->save($fileName);

                return $fileName;

    }

}