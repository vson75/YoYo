<?php

namespace App\Command;

use App\Repository\PostRepository;
use App\Service\Mailer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class TransfertFundAfterExpiredCommand extends Command
{
    protected static $defaultName = 'app:transfert-fund-after-expired';
    private $postRepository;
    private $mailer;

    public function __construct(PostRepository $postRepository, Mailer $mailer)
    {
        parent::__construct(null);
        $this->postRepository = $postRepository;
        $this->mailer = $mailer;
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
            $title = $expiredPost->getTitle();
            $amountCollected = $expiredPost->getTransactionSum();
            $targetAmount = $expiredPost->getTargetAmount();
            $userId = $expiredPost->getUser();
            $this->mailer->sendMailAfterExpiredPost($userId,$expiredPost);
            $io->success($targetAmount);
        }


        return 0;
    }
}
