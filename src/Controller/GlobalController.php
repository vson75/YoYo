<?php

namespace App\Controller;

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
}
