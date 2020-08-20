<?php

namespace App\Controller;

use App\Entity\AdminParameter;
use App\Form\ContactUsType;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class GlobalController extends AbstractController
{

    private $translator;
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/global", name="global")
     */
    public function index()
    {
        return $this->render('global/index.html.twig', [
            'controller_name' => 'GlobalController',
        ]);
    }

    /**
    * @Route("/change_langue/{locale}", name="change_langue")
    */
    public function changeLangue($locale,Request $request){

        $request->getSession()->set('_locale', $locale);
        //go back to the last URL
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/mention_legal", name="mention_legal")
     */
    public function MentionLegal(EntityManagerInterface $em){

        $parameterSite = $em->getRepository(AdminParameter::class)->findLastestId();

        return $this->render('global/mention_legal.html.twig', [
            'userInfo' => $this->getUser(),
            'parameter' => $parameterSite
        ]);
    }

    /**
     * @Route("/contact_us", name="contact_us")
     */
    public function ContactUs(Mailer $mailer, Request $request)
    {
        $form = $this->createForm(ContactUsType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sender = $form['userEmail']->getData();
            $subject = $form['subject']->getData();
            $content = $form['content']->getData().'<br>'.$sender;
            $username = $form['firstandlastname']->getData();
            $phone = $form['PhoneNumber']->getData();

            $mailer->contactUs($subject, $content, $username, $phone);
            $message = $this->translator->trans('message.global.contactUsOK');
            $this->addFlash('success', $message);
            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('global/contact_us.html.twig', [
            'userInfo' => $this->getUser(),
            'form' => $form->createView()
        ]);
    }
}
