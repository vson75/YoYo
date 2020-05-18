<?php


namespace App\Service;


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

    public function SendMailPassword(User $user,$token, $tokenCreateAt, $id, $template, $subject){
        $email = (new TemplatedEmail())
            ->from('YoYo@gmail.com')
            ->to($user->getEmail())
            ->subject($subject)
            ->htmlTemplate($template)
            ->context([
                'tokenUser' => $token,
                'tokencreateAt' => $tokenCreateAt,
                'id' => $id
            ]);
        $this->mailer->send($email);

    }

}