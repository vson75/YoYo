<?php


namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController
{
    /**
     * @Route("/")
     */
    public function homepage(){
        return new Response("this is a new page");
    }

    /**
     * @Route("question/{slug}")
     */
    public function show($slug){
        return $this->render('question/show.html.twig',[
            'question' => ucwords(str_replace('-',' ', $slug))
            ]
        );
    }
}