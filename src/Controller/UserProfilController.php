<?php

namespace App\Controller;


use App\Entity\Post;
use App\Entity\PostSearch;
use App\Entity\PostStatus;
use App\Entity\User;
use App\Form\PostSearchType;
use App\Form\UserProfileFormType;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;




/**
 * @IsGranted("ROLE_USER")
 */
class UserProfilController extends AbstractController
{

    /**
     * @Route("/profil", name="app_profil")
     *
     */
    public function index(EntityManagerInterface $em, Request $request)
    {
        // use the methode getUser() existe in AbstractController
        $userEmail = $this->getUser()->getUsername();

        $repository = $em->getRepository(User::class);
        $userInfo = $repository->findOneBy(['email' => $userEmail]);


        //  dd($userInfo);
        return $this->render('user_profil/user_profil.html.twig', [
            'controller_name' => 'UserProfilController',
            'userInfo' => $userInfo,
        ]);
    }

    /**
     * @Route("/edit_profil", name="app_edit_profil")
     */
    public function editProfil(Request $request, UploadService $uploadService, EntityManagerInterface $em)
    {
        $user = $this->getUser();

        $form = $this->createForm(UserProfileFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //$user = $this->getUser();
            $user =$form->getData();
            //$icon->setUser($user);
            $uploadedFile = $form['iconFile']->getData();


            if ($uploadedFile) {
                $newFilename = $uploadService->UploadIconImage($uploadedFile, $user->getID());
                $user->setIcon($newFilename);
              //dd($user);
                $em->persist($user);

                $em->flush();

                $this->addFlash('success', 'Thay doi thanh cong');

                return $this->redirectToRoute('app_profil');
            }
        }

        return $this->render('user_profil/edit_profil.html.twig', [
            'userIcon' => $form->createView(),
            'userInfo' => $user
        ]);
    }

    /**
     * @Route("/my_project", name="app_my_project")
     */
    public function myProject(Request $request, EntityManagerInterface $em, PaginatorInterface $paginator){

        $search = new PostSearch();
        $form = $this->createForm(PostSearchType::class, $search);
        $form->handleRequest($request);

        $repo = $em->getRepository(Post::class);
        $post = $repo->findByUserWithPostSearch($this->getUser(),$search);

        $pagination = $paginator->paginate(
            $post, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        // get all content in PostStatus
        $postStatus = new \ReflectionClass('App\Entity\PostStatus');
        $statusArray = $postStatus->getConstants();

        return $this->render('post_admin/post_admin.index.html.twig',[
            'userInfo' => $this->getUser(),
            'pagination' => $pagination,
            'form' => $form->createView(),
            'statusArray' => $statusArray
        ]);
    }

    /**
     * @Route("/create_organisation", name="app_create_organisation")
     */
    public function askForRoleOrganisation(){


        return $this->render('organisation/create_organisation.html.twig', [
            'userInfo' => $this->getUser()
        ]);
    }

}
