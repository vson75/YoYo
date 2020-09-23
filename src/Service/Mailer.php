<?php


namespace App\Service;


use App\Entity\Post;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Mailer
{
    private $mailer;
    private $translator;
    private $admin_email;


    public function __construct(MailerInterface $mailer, $admin_email, TranslatorInterface $translator)
    {
        $this->mailer = $mailer;
        $this->admin_email = $admin_email;
        $this->translator = $translator;

    }

    public function SendMailPassword(User $user,$token, $tokenCreateAt, $template, $subject){
        $email = (new TemplatedEmail())
            ->from($this->admin_email)
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate($template)
            ->context([
                'tokenUser' => $token,
                'tokencreateAt' => $tokenCreateAt,
                'id' => $user->getId()
            ]);
        $this->mailer->send($email);

    }

    public function sendMailCreateOrDonationPost(User $user, Post $post, $template, $subject,$title, $action, $caption_link){
        $email = (new TemplatedEmail())
            ->from($this->admin_email)
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate($template)
            ->context([
                'post_uniqueKey'=> $post->getUniquekey(),
                'post_name' => $post->getTitle(),
                'id' => $user->getId(),
                'title' => $title,
                'action' => $action,
                'caption_link_to_post' => $caption_link
            ]);
        $this->mailer->send($email);
    }

    public function sendMailAdminStopOrPublishedPost(User $user, Post $post, $template, $subject,$raison){
        $email = (new TemplatedEmail())
            ->from($this->admin_email)
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate($template)
            ->context([
                'post_uniqueKey'=> $post->getUniquekey(),
                'post_name' => $post->getTitle(),
                'id' => $user->getId(),
                'raison' => $raison
            ]);
        $this->mailer->send($email);
    }

    public function sendMailToAuthorWhenFinishedCollectingPost(User $user, Post $post,?string $excelFile){
        $email = (new TemplatedEmail())
            ->from($this->admin_email)
            ->to($user->getEmail())
            ->subject($this->translator->trans('email.subject.FinishPost').' '.$post->getTitle().' '.$this->translator->trans('email.subject.finish'))
            ->htmlTemplate('email/FinishCollectingPost.html.twig');
            if(!is_null($excelFile)){
                $email->attachFromPath($excelFile);
            }
            $email->context([
                'post_uniqueKey'=> $post->getUniquekey(),
                'post_name' => $post->getTitle(),
                'target_amount' => $post->getTargetAmount(),
                'collected_amount' => $post->getTransactionSum(),
                'id' => $user->getId()
            ]);

        $this->mailer->send($email);
    }


    public function sendMailToAllFundedUser(User $user, Post $post){
        $email = (new TemplatedEmail())
            ->from($this->admin_email)
            ->to($user->getEmail())
            ->subject($this->translator->trans('email.subject.WarningUserPostFinish').': '.$post->getTitle().' '.$this->translator->trans('email.subject.finish'))
            ->htmlTemplate('email/FinishCollectWarningAllUser.html.twig');
        $email->context([
            'post_uniqueKey'=> $post->getUniquekey(),
            'post_name' => $post->getTitle(),
            'target_amount' => $post->getTargetAmount(),
            'collected_amount' => $post->getTransactionSum(),
        ]);

        $this->mailer->send($email);
    }


    public function sendMailAlertToAdminWhenCreatingOrganisation($username){
        $email = (new TemplatedEmail())
            ->from($this->admin_email)
            ->to($this->admin_email)
            ->subject($this->translator->trans('email.subject.AlertAdminNewOrganisation'))
            ->htmlTemplate('email/Alert_Admin_When_Creating_Organisation.html.twig')
            ->context([
                'username'=> $username
            ])
           ;
        $this->mailer->send($email);
    }


    public function sendMailCongratulationNewOrganisation(User $user){
        $email = (new TemplatedEmail())
            ->from($this->admin_email)
            ->to($user->getEmail())
            ->bcc($this->admin_email)
            ->subject($this->translator->trans('email.subject.CongratNewOrganisation'))
            ->htmlTemplate('email/WelcomeNewOrganisation.html.twig')
        ;
        $this->mailer->send($email);
    }

    public function ThankToCreateOrganisation(User $user){
        $email = (new TemplatedEmail())
            ->from($this->admin_email)
            ->to($user->getEmail())
            ->bcc($this->admin_email)
            ->subject($this->translator->trans('email.subject.InfoOrganisationSentToAdmin'))
            ->htmlTemplate('email/ThankToCreateOrganisation.html.twig')
        ;
        $this->mailer->send($email);
    }

    public function sendMailAskMoreInfoOrStopAboutOrganisation($choice, User $user, $content){
        $email = (new TemplatedEmail())
            ->from($this->admin_email)
            ->to($user->getEmail())
            ->bcc($this->admin_email)
            ->htmlTemplate('email/Ask_More_Info_Organisation.html.twig')
            ->context([
                'content'=> $content,
                'id' => $user->getId()
            ]);
        if($choice == "ask"){
            $email->subject($this->translator->trans('email.subject.CompleteInfoOrganisation'));
        }elseif ($choice == "stop"){
            $email->subject($this->translator->trans('email.subject.CannotValidOrganisation'));
        }

        $this->mailer->send($email);
    }

    public function contactUs($subject, $content, $username, $phonenumber){
        $email = (new TemplatedEmail())
            ->from($this->admin_email)
            ->to($this->admin_email)
            ->htmlTemplate('email/contactUs.html.twig')
            ->subject($subject)
            ->context([
                'content'=> $content,
                'username' => $username,
                'phoneNumber' => $phonenumber,
            ]);
        $this->mailer->send($email);
    }

    public function AlertAuthorAfterTransferFund(User $user, Post $post, $path){
        $email = (new TemplatedEmail())
            ->from($this->admin_email)
            ->to($user->getEmail())
            ->htmlTemplate('email/alert_author_after_transfer_fund.html.twig')
            ->subject($this->translator->trans('email.subject.AlertAuthorAfterTransferFund').': '.$post->getTitle())
            ->attachFromPath($path)
            ->context([
                'post'=> $post,
                'user' => $user
            ])
        ;
        $this->mailer->send($email);
    }

    public function sendMailInTableEmails(User $user, Post $post,?array $pj, $object, $content){
        $email = (new TemplatedEmail())
            ->from($this->admin_email)
            ->to($user->getEmail())
            ->subject($object)
            ->htmlTemplate('email/UpdateInfoPostInProgress.html.twig');
            if(!empty($pj)){
                for ($i=0; $i < count($pj); $i++) { 
                    $email->attachFromPath($pj[$i]);
                }
            
            }
            $email->context([
                'post_uniqueKey'=> $post->getUniquekey(),
                'post_name' => $post->getTitle(),
                'email_content' => $content
            ]);

        $this->mailer->send($email);
    }

}