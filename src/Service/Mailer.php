<?php


namespace App\Service;


use App\Entity\Post;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class Mailer
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
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

    public function sendMailCreateOrFinancePost(User $user, Post $post, $template, $subject){
        $email = (new TemplatedEmail())
            ->from('YoYo@gmail.com')
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate($template)
            ->context([
                'post_uniqueKey'=> $post->getUniquekey(),
                'post_name' => $post->getTitle(),
                'id' => $user->getId()
            ]);
        $this->mailer->send($email);
    }

}