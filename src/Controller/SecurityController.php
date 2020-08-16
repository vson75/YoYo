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
use Symfony\Contracts\Translation\TranslatorInterface;


class SecurityController extends AbstractController
{

    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        $user = $this->getUser();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'userInfo' => $user
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

                $message = $this->translator->trans('message.security.ExistedEmail');
                $this->addFlash('notice', $message);
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);

            }else{
                $user->setToken(hash('haval160,3',$userEmail.rand(0,1000),false));
                $user->setTokencreateAt(new \DateTime('now'));
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();



                $token = $user->getToken();
                $tokenCreateAt = $user->getTokencreateAt();
                $template = 'email/password.html.twig';
                $subject = "Chào ".$user->getFirstname()." tới với YoYo - Tạo mật khẩu mới";
                $mailer->SendMailPassword($user,$token,$tokenCreateAt,$template,$subject);

                $message = $this->translator->trans('message.security.CreateNewAccount');
                $this->addFlash('success',$message);

                //method allow to redirect in the page if authentificator sucess
                return $this->redirectToRoute('app_login');
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

                    $message = $this->translator->trans('message.security.CongratulationNewAccount');
                    $this->addFlash('success',$message);

                //method allow to redirect in the page if authentificator sucess
                return $this->redirectToRoute('app_login');

            }

        }else{
            $message = $this->translator->trans('message.security.WrongLinkPassword');
            $this->addFlash('echec',$message);
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
            $checkUserEmail = $repo->findOneBy([
                'email' => $userEmail
            ]);


            if(!empty($checkEmail)){

               // dd($checkEmail);
                //$id = $checkEmail->getId();
                $checkUserEmail->setToken(hash('haval160,3',$userEmail.rand(0,1000),false));
                $checkUserEmail->setTokencreateAt(new \DateTime('now'));
                $em = $this->getDoctrine()->getManager();
                $em->flush();
                $token = $checkUserEmail->getToken();


                $tokenCreateAt = $user->getTokencreateAt();



                $template = 'email/password.html.twig';
                $subject = "Thay đổi mật khẩu tại YoYo";
                $mailer->SendMailPassword($checkUserEmail, $token, $tokenCreateAt,$template,$subject);

                $message = $this->translator->trans('message.security.resetPassword');
                $this->addFlash('success',$message);
                //method allow to redirect in the page if authentificator sucess
                return $this->redirectToRoute("app_homepage");

            }else{

                $message = $this->translator->trans('message.security.EmailNotExist');
                $this->addFlash('echec', $message);
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
