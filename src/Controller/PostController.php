<?php


namespace App\Controller;

use App\Service\MarkdownHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{

    /**
     * @Route("/")
     */
    public function homepage(){
        return $this->render('question/homepage.html.twig');
    }

    /**
     * @Route("post/{slug}")
     * @param $slug
     * @param MarkdownHelper $markdownHelper
     * @return Response
     */
    public function show($slug, MarkdownHelper $markdownHelper){
        $comment = ['answers 1', 'answer 2', 'answer 3','answer 4'];

        $postContent = <<<EOF
Spicy **jalapeno bacon** ipsum dolor amet veniam shank in dolore. Ham hock nisi landjaeger cow,
lorem proident [beef ribs](https://google.com/) aute enim veniam ut cillum pork chuck picanha. Dolore reprehenderit
labore minim pork belly spare ribs cupim short loin in. Elit l'exercitation eiusmod dolore cow
turkey shank eu pork belly meatball non cupim.zae
EOF;

        $postContent = $markdownHelper->parse($postContent);

        // dump($cache);die();

        return $this->render('post/show_post.html.twig',[
                'title' => ucwords(str_replace('-',' ', $slug)),
                'postContent' => $postContent,
                'comment' => $comment
            ]
        );
    }
}