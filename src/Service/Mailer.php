<?php


namespace App\Service;


use App\Entity\Post;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Mailer\MailerInterface;

class Mailer
{
    private $mailer;
    private $admin_email;


    public function __construct(MailerInterface $mailer, $admin_email)
    {
        $this->mailer = $mailer;
        $this->admin_email = $admin_email;

    }

    public function SendMailPassword(User $user,$token, $tokenCreateAt, $template, $subject){
        $email = (new TemplatedEmail())
            ->from('YoYo@gmail.com')
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
            ->from('YoYo@gmail.com')
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
            ->from('YoYo@gmail.com')
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

    public function sendMailAfterExpiredPost(User $user, Post $post,?string $excelFile){
        $email = (new TemplatedEmail())
            ->from('YoYo@gmail.com')
            ->to($user->getEmail())
            ->subject('Dự án của bạn tại YoYo đã đến hạn chót')
            ->htmlTemplate('email/EmailExpiredPost.html.twig');
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


    public function sendMailAlertToAdminWhenCreatingOrganisation(){
        $email = (new TemplatedEmail())
            ->from($this->admin_email)
            ->to($this->admin_email)
            ->subject('New organisation was created')
            ->htmlTemplate('email/Alert_Admin_When_Creating_Organisation.html.twig')
           ;
        $this->mailer->send($email);
    }


}