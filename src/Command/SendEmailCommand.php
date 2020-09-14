<?php

namespace App\Command;

use App\Entity\Emails;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SendEmailCommand extends Command
{
    protected static $defaultName = 'app:send-email';

    private $mailer;
    private $em;

    public function __construct(Mailer $mailer, EntityManagerInterface $em)
    {
        parent::__construct(null);
        $this->mailer = $mailer;
        $this->em = $em;
    }

    protected function configure()
    {
        $this
            ->setDescription('send Email in the Emails table')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $EmailNotSent = $this->em->getRepository(Emails::class)->getEmailNotSend();
        $output->writeln([
            '============'
        ]);
        foreach ($EmailNotSent as $email => $value) {
            # code...
        }

        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }
}
