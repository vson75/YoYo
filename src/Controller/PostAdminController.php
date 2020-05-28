<?php


namespace App\Controller;


use App\Entity\Post;

use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;



class PostAdminController extends AbstractController
{

    /**
     * @IsGranted("ROLE_ADMIN")
     * @Route("/admin/post", name="app_post_admin")
     */
    public function index(PostRepository $postRepository,Request $request, PaginatorInterface $paginator){

        // get query find_post parameter. like $_GET
        $q = $request->query->get('find_post');
        $user = $this->getUser();


        $post = $postRepository->findAllWithSearch($q);
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

}