<?php


namespace App\Controller;
use App\Entity\Post;
use App\Entity\User;
use App\Repository\CommentRepository;
use App\Repository\UserRepository;
use App\Service\MarkdownHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

class PostController extends AbstractController
{
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

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
    public function show($slug, MarkdownHelper $markdownHelper, EntityManagerInterface $em, Post $post){

        $repository = $em->getRepository(Post::class);
        $postInfo = array();
        $postInfo = $repository->findOneBy([
            'slug'=> $slug
        ]);
        if(!$postInfo){
            throw $this->createNotFoundException('The Post is not exist');
        }
        $comment = $postInfo->getComments();

        $currentUserLooged = $this->security->getUser();

      // var_dump($comment);die;

        $postContentCache = $postInfo->getContent();
        $postContentCache = $markdownHelper->cacheInfo($postContentCache);

        return $this->render('post/show_post.html.twig',[
                'postInfo' => $postInfo,
                'comment'=> $comment,
                'UserLogged' => $currentUserLooged
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