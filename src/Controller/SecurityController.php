<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CreateOrResetPasswordType;
use App\Form\ResetPasswordType;
use App\Form\UserRegistrationFormType;
use App\Security\LoginFormAuthenticator;

use App\Service\Mailer;
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
    public function registration(EntityManagerInterface $em, Request $request, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $formAuthenticator, Mailer $mailer){
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
                $user->setToken(hash('haval160,3',$userEmail.rand(0,1000),false));
                $user->setTokencreateAt(new \DateTime('now'));
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->addFlash('success','Cam on ban da dang ky, chung toi se gui ban EMail de ban hoan tat dang ky cua minh');

                $token = $user->getToken();
                $id = $user->getId();
                $tokenCreateAt = $user->getTokencreateAt();
                $template = 'email/password.html.twig';
                $subject = "Chào ".$user->getFirstname()." tới với YoYo - Tạo mật khẩu mới";
                $mailer->SendMailPassword($user,$token,$tokenCreateAt,$id,$template,$subject);

                //method allow to redirect in the page if authentificator sucess
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

    /**
     * @Route("/password/{id}/{hash}", name="app_create_resetPassword")
     */
    public function createOrResetPassword($id, $hash, EntityManagerInterface $em, Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, LoginFormAuthenticator $formAuthenticator){

        $repo = $em->getRepository(User::class);
        $user = $repo->findOneBy([
            'id' => $id,
            'token' => $hash
        ]);
        if($user){
            $tokenCreateAt = $user->getTokencreateAt();
            $tokenCreateAt = $tokenCreateAt->format('Y-m-d H:i:s');
            //dd($tokenCreateAt);
            $checkUserToken = $repo->findOneForResetCreatePassword($id,$hash,$tokenCreateAt);
        }else{
            $checkUserToken = null;
        }


        // check if token, user_Id and token create_at is valid
        if(!is_null($checkUserToken)){

            $form = $this->createForm(CreateOrResetPasswordType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                /** @var User $user */
                    $user = $form->getData();

                    $user->setPassword($passwordEncoder->encodePassword(
                    $user,
                    $user->getPassword()
                    ));
                    $user->setTokencreateAt(new \DateTime('now'));
                    $user->setToken('0');

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();

                    $this->addFlash('success','mât khâu cua ban da OK ! chào mung ban toi voi YoYo');

                //method allow to redirect in the page if authentificator sucess
                return $guardHandler->authenticateUserAndHandleSuccess(
                    $user,
                    $request,
                    $formAuthenticator,
                    'main'
                );

            }

        }else{
            $this->addFlash('echec','duong dân thay doi mât khâu khong ton tai hoac tai khoan cua ban da qua thoi gian kich hoat cho phep (5h)');
            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('security/password.html.twig', [
            'passwordForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("reset/password", name="app_forgot_password")
     */
    public function resetPassword(EntityManagerInterface $em, Request $request, Mailer $mailer){
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();
            $userEmail = $user->getEmail();
            $repo = $em->getRepository(User::class);
            $checkEmail = $repo->findOneBy([
                'email' => $userEmail
            ]);


            if(!empty($checkEmail)){

               // dd($checkEmail);
                $id = $checkEmail->getId();
                $checkEmail->setToken(hash('haval160,3',$userEmail.rand(0,1000),false));
                $checkEmail->setTokencreateAt(new \DateTime('now'));
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $token = $checkEmail->getToken();


                $tokenCreateAt = $user->getTokencreateAt();

                $this->addFlash('success','Chung toi se gui toi EMail cua ban duong link the thay doi mat khâu');

                $template = 'email/password.html.twig';
                $subject = "Thay đổi mật khẩu tại YoYo";
                $mailer->SendMailPassword($checkEmail, $token, $tokenCreateAt,$id,$template,$subject);


                //method allow to redirect in the page if authentificator sucess
                return $this->redirectToRoute("app_homepage");

            }else{

                $this->addFlash('echec', 'Email cua ban khong tôn tai trong hê thong cua chung toi');
                return $this->render('security/register.html.twig', [
                    'resetForm' => $form->createView(),
                ]);
            }

        }
        return $this->render('security/reset.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
