<?php

namespace App\Command;

use App\Entity\Emails;
use App\Entity\PostDocument;
use App\Service\Mailer;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class SendEmailCommand extends Command
{
    protected static $defaultName = 'app:send-email';

    private $mailer;
    private $em;
    private $params;

    public function __construct(Mailer $mailer, EntityManagerInterface $em, ParameterBagInterface $params)
    {
        parent::__construct(null);
        $this->mailer = $mailer;
        $this->em = $em;
        $this->params = $params;
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
        if(!empty($EmailNotSent)){
            for ($i=0; $i < count($EmailNotSent); $i++) { 
                # code...
    
                // Get User recipient, get Email content and get Post link to this mail
                $email = $EmailNotSent[$i];
                $emailContent = $email->getEmailContent();
                $user = $email->getUserRecipient();
                $post = $emailContent->getPost();
    
                $postDocumentAttached = $this->em->getRepository(PostDocument::class)->findBy([
                    'EmailContent' => $emailContent
                ]);
                
                // Get the list document link to this mail
                $privatePath = $this->params->get('public_upload_file');
    
                for ($i=0; $i < count($postDocumentAttached); $i++) { 
                    $path[$i] = $privatePath.$postDocumentAttached[$i]->getProofOFProjectInProgressPath();
                }
                
                // Send mail
                $this->mailer->sendMailInTableEmails($user,$post,$path, $emailContent->getObject(), $emailContent->getContent());
    
                // Update Send Date in Emails table
                $email->setSentDate(new DateTime('now'));
                $this->em->persist($email);
                $this->em->flush();
    
    
            }
        }else{
            $io->success('No email waiting to be send');
        }
        


        $io->success('You have a new command! Now make it your own! Pass --help to see your options.');

        return 0;
    }
}
