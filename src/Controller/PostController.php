<?php


namespace App\Controller;

use App\Entity\Post;
use App\Service\MarkdownHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PostController extends AbstractController
{

    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage(EntityManagerInterface $em){

        $repository = $em->getRepository(Post::class);
        $post = $repository->findPostByNewest();
       // dump($post);die;
        return $this->render('post/homepage.html.twig',[
                'post' => $post
            ]
        );
    }

    /**
     * @Route("post/{slug}")
     * @param $slug
     * @param MarkdownHelper $markdownHelper
     * @return Response
     */
    public function show($slug, MarkdownHelper $markdownHelper, EntityManagerInterface $em){
        $comment = ['answers 1', 'answer 2', 'answer 3','answer 4'];


        $repository = $em->getRepository(Post::class);
        $postInfo = array();
        $postInfo = $repository->findOneBy([
            'id'=> 1
        ]);
        if(!$postInfo){
            throw $this->createNotFoundException('The Post is not exist');
        }
        $postContentCache = $postInfo->getContent();
        $postContentCache = $markdownHelper->cacheInfo($postContentCache);
        //dump($postContent);die;
        //$postContent = $markdownHelper->parse($postContent);

        return $this->render('post/show_post.html.twig',[
                'postInfo' => $postInfo,
                'comment' => $comment
            ]
        );
    }


    /**
     * @Route("/participant_project/{id}", name="add_participant_in_project", methods="POST")
     */
    public function addNumberParticipant(EntityManagerInterface $em, Post $post, LoggerInterface $logger){

       // dump($slug);die;
        $post->setNumberParticipant($post->getNumberParticipant() + 1);
        $em->flush();

        $logger->info('a new participant has been finance this project');
        //the key to increase nb Participant = nb_Participant
        return $this->json(['nb_Participant'=> $post->getNumberParticipant()]);
    }
}