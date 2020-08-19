<?php

namespace App\Controller;

use App\Entity\AdminParameter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GlobalController extends AbstractController
{
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
}
