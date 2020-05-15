<?php


namespace App\Controller;
use App\Entity\Post;
use App\Form\PostFormType;
use App\Service\MarkdownHelper;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("post/{uniquekey}", name="show_post")
     * @param MarkdownHelper $markdownHelper
     * @return Response
     */
    public function show($uniquekey, MarkdownHelper $markdownHelper, EntityManagerInterface $em, Post $post){

        $repository = $em->getRepository(Post::class);
        $postInfo = array();
        $postInfo = $repository->findOneBy([
            'uniquekey'=> $uniquekey
        ]);
        if(!$postInfo){
            throw $this->createNotFoundException('The Post is not exist');
        }
        $comment = $postInfo->getComments();

        $currentUserLooged = $this->security->getUser();

       // dd($currentUserLooged);
      // var_dump($comment);die;

        $postContentCache = $postInfo->getContent();
        if(!is_null($postContentCache)){
            $postContentCache = $markdownHelper->cacheInfo($postContentCache);
        }


        return $this->render('post/show_post.html.twig',[
                'postInfo' => $postInfo,
                'comment'=> $comment,
                'UserLogged' => $currentUserLooged
            ]
        );
    }


    /**
     * @Route("/participant_project/{uniquekey}", name="add_participant_in_project", methods="POST", requirements={"id":"\d+"})
     * @IsGranted("ROLE_USER")
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

    /**
     * @Route("create_new/post", name="app_post_new")
     * @IsGranted("ROLE_USER")
     */
    public function new(EntityManagerInterface $em, Request $request){
        $form = $this->createForm(PostFormType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            //get data in form
            $createNew = $form->getData();
            // add date and user = user created
            $createNew->setPublishedAt(new \DateTime('now'));
            $createNew->setUser($this->getUser());

            //get title and hash md5 for the uniquekey
            $title = $createNew->getTitle();
            $uniquekey =  substr(md5($title),0,10);

            //check if the unique key is in db
            $repository = $em->getRepository(Post::class);
            $postExisted = $repository->findOneBy([
                'uniquekey'=> $uniquekey
            ]);
            if(is_null($postExisted)){
                $createNew->setUniquekey($uniquekey);
            }else{
                $uniquekey =  substr(md5($title.rand(0,10000)),0,10);
                $createNew->setUniquekey($uniquekey);
            }

            $em->persist($createNew);
          //  dd($createNew);
            $em->flush();

            $this->addFlash('success', 'Cảm ơn bạn đã tạo chủ để này');
            $id_post = $createNew->getId();
            $repo = $em->getRepository(Post::class);

            $newPostCreated = $repo->findOneBy([
                'id' => $id_post,
            ]);
            $key = $newPostCreated->getUniquekey();

            return $this->redirectToRoute('show_post',[
                'uniquekey' => $key
            ]);
        }

        return $this->render('post/create_post.html.twig',[
            'postForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("edit/post/{id}")
     * @IsGranted("ROLE_USER")
     */
    public function edit($id,EntityManagerInterface $em, Request $request, Post $post){
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $updatePost = $form->getData();
            //dd($updatePost);
            $id_post = $updatePost->getId();

            $em->persist($updatePost);
            $em->flush();
            $this->addFlash('success', 'Sửa đổi thành công');
            $repo = $em->getRepository(Post::class);
            $postUpdated = $repo->findOneBy([
                'id' => $id_post,
            ]);
            $slug = $postUpdated->getSlug();

            return $this->redirectToRoute('show_post',[
                'slug' => $slug,
            ]);
        }

        return $this->render('post/create_post.html.twig',[
            'postForm' => $form->createView(),
        ]);

    }

}