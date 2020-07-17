<?php

namespace App\Controller;


use App\Entity\DocumentType;
use App\Entity\OrganisationInfo;
use App\Entity\Post;
use App\Entity\PostSearch;
use App\Entity\PostStatus;
use App\Entity\RequestOrganisationDocument;
use App\Entity\RequestOrganisationInfo;
use App\Entity\RequestStatus;
use App\Entity\Transaction;
use App\Entity\User;
use App\Entity\UserDocument;
use App\Form\CreateOrganisationType;
use App\Form\EditDocumentOrganisationType;
use App\Form\OrganisationInfoType;
use App\Form\PostSearchType;
use App\Form\UserProfileFormType;
use App\Repository\RequestStatusRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use App\Service\Mailer;
use App\Service\UploadService;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use PhpParser\Comment\Doc;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
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

        $organisationInfo = $em->getRepository(RequestOrganisationInfo::class)->findOneBy([
            'user' => $userInfo
        ]);

        $certificate = $em->getRepository(RequestOrganisationDocument::class)->findLastDocumentByUserIdAndTypeDoc($userInfo, DocumentType::Certificate_organisation);
        $bankAccount =  $em->getRepository(RequestOrganisationDocument::class)->findLastDocumentByUserIdAndTypeDoc($userInfo, DocumentType::Bank_account_information);
        $awards = $em->getRepository(RequestOrganisationDocument::class)->findAllDocumentByUserId($userInfo, DocumentType::Awards_justification);

        //  dd($userInfo);
        return $this->render('user_profil/user_profil.html.twig', [
            'controller_name' => 'UserProfilController',
            'userInfo' => $userInfo,
            'organisationInfo' => $organisationInfo,
            'certificate' => $certificate,
            'bank'  => $bankAccount,
            'awards' => $awards
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
                $newFilename = $uploadService->UploadIconImage($uploadedFile, $user->getID(),$user->getIcon());
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
     * @return \Symfony\Component\HttpFoundation\Response
     * @Route("/my_profil/testChart", name="app_test_chart")
     */
    public function testChart(EntityManagerInterface $em){
        $pieChart = new PieChart();
        $pieChart->getData()->setArrayToDataTable(
            [['Task', 'Hours per Day'],
                ['Work',     11],
                ['Eat',      2],
                ['Commute',  2],
                ['Watch TV', 2],
                ['Sleep',    7]
            ]
        );
        $pieChart->getOptions()->setTitle('My Daily Activities');
        $pieChart->getOptions()->setHeight(500);
        $pieChart->getOptions()->setWidth(900);
        $pieChart->getOptions()->getTitleTextStyle()->setBold(true);
        $pieChart->getOptions()->getTitleTextStyle()->setColor('#009900');
        $pieChart->getOptions()->getTitleTextStyle()->setItalic(true);
        $pieChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $pieChart->getOptions()->getTitleTextStyle()->setFontSize(20);

        $repo = $em->getRepository(Transaction::class);
        $nbProjectFinanced = $repo->getTotalPostFinancedByUser($this->getUser()->getId());

        $projectFinanced = $repo->getDistinctPostFinancedByUser($this->getUser());
        dump($projectFinanced);

        return $this->render('user_profil/testChart.html.twig', [
            'userInfo' => $this->getUser(),
            'piechart' => $pieChart,
            'nbProjectFinanced' => $nbProjectFinanced[0]["count(*)"]
        ]);
    }


}
