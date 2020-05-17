<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserRegistrationFormType;
use App\Security\LoginFormAuthenticator;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;



class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error
        ]);
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout(){
        throw new \Exception('Merci de visiter notre site. See you soon !');
    }

    /**
     * @Route("/registration", name="app_registration")
     * @param EntityManagerInterface $em
     * @param MailerInterface $mailer
     * @param Request $request
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param GuardAuthenticatorHandler $guardHandler
     * @param LoginFormAuthenticator $formAuthenticator
     * @return \Symfony\Component\HttpFoundation\Response|null
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function registration(EntityManagerInterface $em, MailerInterface $mailer, Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $formAuthenticator){
        $form = $this->createForm(UserRegistrationFormType::class);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $userEmail = $user->getEmail();
            $repo = $em->getRepository(User::class);
            $checkEmail = $repo->findBy([
                'email' => $userEmail
            ]);
       //  dd(!empty($checkEmail));
            if(!empty($checkEmail)){
                $this->addFlash('notice', 'Email của bạn đã được sử dụng. Bạn có thể reset password để tạo mật khẩu mới');
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);

            }else{
                $user->setPassword($passwordEncoder->encodePassword(
                    $user,
                    $user->getPassword()
                ));
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                // after insert new user in our DB, send mail congratulation
                $email = new TemplatedEmail();
                $email  ->from('YoYo@gmail.com')
                    ->to($user->getEmail())
                    ->subject("Welcome in YoYo {$user->getFirstname()}")
                    ->htmlTemplate('email/email_welcome.html.twig');
                $mailer->send($email);


                return $guardHandler->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $formAuthenticator,
                    'main'
                );
            }

        }
        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
