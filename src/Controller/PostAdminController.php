<?php


namespace App\Controller;


use App\Entity\Post;

use App\Entity\PostStatus;
use App\Form\StopPostType;
use App\Repository\PostRepository;
use App\Repository\TransactionRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * @IsGranted("ROLE_ADMIN")
 */
class PostAdminController extends AbstractController
{

    /**
     * @Route("/admin/post", name="app_post_admin")
     */
    public function index(PostRepository $postRepository, PaginatorInterface $paginator, Request $request, TransactionRepository $transactionRepository){

        // get query find_post parameter. like $_GET
        $q = $request->query->get('find_post');
        $user = $this->getUser();


        $post = $postRepository->findAllWithSearch($q);
       // $amount = $transactionRepository->getAnonymousTransactionbyPost($post);
      //  dd($amount);
    
        $pagination = $paginator->paginate(
            $post, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );
        return $this->render('post_admin/post_admin.index.html.twig', [
            'pagination' => $pagination,
            //'postInfo'=> $post,
            'userInfo' => $user
        ]);
    }

    /**
     *@Route("/admin/post_detail/{uniquekey}", name="admin_show_post")
     */
    public function detailPost($uniquekey,EntityManagerInterface $em, TransactionRepository $transactionRepository){

        $user = $this->getUser();
        $repository = $em->getRepository(Post::class);
        $postInfo = $repository->findOneBy([
            'uniquekey'=> $uniquekey
        ]);

        if (is_null($postInfo)) {
            throw $this->createNotFoundException('The Post is not exist');
        }
        $nb_participant = $transactionRepository->getNumberParticipantbyPost($postInfo->getId());
        $totalAmount = $transactionRepository->getTotalAmountbyPost($postInfo->getId());
        $TransactionThisPost = $transactionRepository->getTransactionbyPost($postInfo->getId());

        $datediff = date_diff($postInfo->getFinishAt(),new \DateTime('now'));
        $datediff = $datediff->format('%d');


        return $this->render('post_admin/show_post.html.twig', [
            'postInfo' => $postInfo,
            'nb_participant' => $nb_participant,
            'totalAmount' => $totalAmount,
            'TransactionThisPost' => $TransactionThisPost,
            'userInfo' => $user,
            'datediff' => $datediff,
        ]);
    }


    /**
     * @Route("/admin/stopPost/{uniquekey}", name="admin_stop_post")
     */
    public function stopPost($uniquekey, EntityManagerInterface $em, Request $request, Mailer $mailer){

        $form = $this->createForm(StopPostType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $repo = $em->getRepository(Post::class);
            $post = $repo->findOneBy([
                'uniquekey' => $uniquekey,
            ]);
            // dd(PostStatus::POST_STOP);
            $repo_status = $em->getRepository(PostStatus::class);
            $status = $repo_status->findOneBy([
                'id' => PostStatus::POST_STOP
            ]);
            $post->setStatus($status);

            $em->persist($post);
            $em->flush();

            $context = $form['Raison']->getData();
            // dd(htmlspecialchars_decode($context));

            $user_post = $post->getUser();
            $template = 'email/EmailPublicOrStopPost.html.twig';
            $subject = 'Tạm ngưng dự án của bạn tại YoYo';
            $mailer->sendMailAdminStopOrPublishedPost($user_post,$post,$template,$subject,$context);

            $this->addFlash('success', 'Post status changed');
            return $this->redirectToRoute('admin_show_post', [
                'uniquekey' => $uniquekey
            ]);
        }

        return $this->render('post_admin/stop_post.html.twig',[
            'post' => $form->createView(),
            'userInfo' => $this->getUser()
        ]);

    }

    /**
     * @Route("admin/publicPost/{uniquekey}", name="admin_allow_to_public")
     */
    public function AllowToPublicPost($uniquekey, EntityManagerInterface $em, Request $request){

    }

}