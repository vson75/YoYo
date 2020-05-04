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
        return $this->render('homepage.html.twig',[
                'post' => $post
            ]
        );
    }

    /**
     * @Route("post/{slug}", name="show_post")
     * @param $slug
     * @param MarkdownHelper $markdownHelper
     * @return Response
     */
    public function show($slug, MarkdownHelper $markdownHelper, EntityManagerInterface $em){
        $comment = ['answers 1', 'answer 2', 'answer 3','answer 4'];


        $repository = $em->getRepository(Post::class);
        $postInfo = array();
        $postInfo = $repository->findOneBy([
            'slug'=> $slug
        ]);
        if(!$postInfo){
            throw $this->createNotFoundException('The Post is not exist');
        }
        $postContentCache = $postInfo->getContent();
        $postContentCache = $markdownHelper->cacheInfo($postContentCache);
        //dump($postContent);die;
        //$postContent = $markdownHelper->parse($postContent);
       //dump($postInfo);die;
        return $this->render('post/show_post.html.twig',[
                'postInfo' => $postInfo,
                'comment' => $comment
            ]
        );
    }


    /**
     * @Route("/participant_project/{slug}", name="add_participant_in_project", methods="POST", requirements={"id":"\d+"})
     * @param EntityManagerInterface $em
     * @param Post $post
     * @param LoggerInterface $logger
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addNumberParticipant(EntityManagerInterface $em, Post $post, LoggerInterface $logger){

       // dump($post);die;
        $post->setNumberParticipant($post->getNumberParticipant() + 1);
        $em->flush();

        $logger->info('a new participant has been finance this project');
        //the key to increase nb Participant = nb_Participant (use this key in post.js)
        return $this->json(['nb_Participant'=> $post->getNumberParticipant(),
            'id_post'=>$post->getTitle()]);
    }

}