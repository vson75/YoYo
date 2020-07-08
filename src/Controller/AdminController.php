<?php

namespace App\Controller;

use App\Entity\DocumentType;
use App\Entity\Post;
use App\Entity\PostSearch;
use App\Entity\PostStatus;
use App\Entity\User;
use App\Entity\UserDocument;
use App\Form\PostSearchType;
use App\Form\StopPostType;
use App\Repository\PostRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserDocumentRepository;
use App\Repository\UserRepository;
use App\Service\Mailer;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController
 * @package App\Controller
 * @IsGranted("ROLE_ADMIN")
 */
class AdminController extends AbstractController
{
    /**
     * @Route("/admin/overview", name="app_admin_overview")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(UserRepository $userRepository)
    {
        $number_waiting_validate_organisation = $userRepository->NumberWaitingOrganisation();
        return $this->render('admin/overview.html.twig',[
                'userInfo' => $this->getUser(),
                'nbWaitingOrganisation' => $number_waiting_validate_organisation
            ]
        );
    }

    /**
     * @Route("/admin/post", name="app_post_admin")
     */
    public function adminPost(PostRepository $postRepository, PaginatorInterface $paginator, Request $request, EntityManagerInterface $em){

        // get query find_post parameter. like $_GET
        $q = $request->query->get('q');


@
        $search = new PostSearch();
        $form = $this->createForm(PostSearchType::class, $search);
        $form->handleRequest($request);

        $repo = $em->getRepository(Post::class);
        $post = $repo->findAllWithSearch($search);
        // $postRepository->findAllWithSearch($search);

        $pagination = $paginator->paginate(
            $post, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        // get all content in PostStatus
        $postStatus = new \ReflectionClass('App\Entity\PostStatus');
        $statusArray = $postStatus->getConstants();

        return $this->render('post_admin/post_admin.index.html.twig', [
            'pagination' => $pagination,
            'userInfo' => $this->getUser(),
            'form' => $form->createView(),
            'statusArray' => $statusArray
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

        $postStatus = new \ReflectionClass('App\Entity\PostStatus');
        $statusArray = $postStatus->getConstants();

        return $this->render('post_admin/show_post.html.twig', [
            'postInfo' => $postInfo,
            'nb_participant' => $nb_participant,
            'totalAmount' => $totalAmount,
            'TransactionThisPost' => $TransactionThisPost,
            'userInfo' => $user,
            'datediff' => $datediff,
            'statusArray' => $statusArray
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
     * @Route("admin/publicPost/{uniquekey}", name="admin_allow_to_collecting")
     */
    public function AllowToCollectingPost($uniquekey, EntityManagerInterface $em, Request $request, Mailer $mailer){
        $repo = $em->getRepository(Post::class);
        $post = $repo->findOneBy([
            'uniquekey' => $uniquekey,
        ]);
        // dd(PostStatus::POST_STOP);
        $repo_status = $em->getRepository(PostStatus::class);
        $status = $repo_status->findOneBy([
            'id' => PostStatus::POST_COLLECTING
        ]);
        $post->setStatus($status);

        $em->flush();

        /**

        $user_post = $post->getUser();
        $template = 'email/EmailPublicOrStopPost.html.twig';
        $subject = 'Tạm ngưng dự án của bạn tại YoYo';
        $mailer->sendMailAdminStopOrPublishedPost($user_post,$post,$template,$subject,$context);

         */
        // dd(htmlspecialchars_decode($context));

        $this->addFlash('success', 'Post status changed');
        return $this->redirectToRoute('admin_show_post', [
            'uniquekey' => $uniquekey
        ]);
    }

    /**
     * @Route("admin/list_validating_organisation", name="app_list_validate_organisation")
     */
    public function listAskForOrganisationRole(UserRepository $userRepository, PaginatorInterface $paginator, Request $request){

        $waiting_organisation = $userRepository->getDetailWaitingOrganisation();
        $pagination = $paginator->paginate(
            $waiting_organisation, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            10/*limit per page*/
        );

        return $this->render('admin/list_waiting_organisation.html.twig',[
            'userInfo' => $this->getUser(),
            'pagination' => $pagination
        ]);
    }

    /**
     * @Route("admin/control_demande_organisation/{userId}", name="app_admin_valide_organisation")
     */
    public function ControleOrganisation($userId, UserRepository $userRepository, EntityManagerInterface $em, UserDocumentRepository $documentRepository){

        $checkUser = $userRepository->checkUserIsCreatingOrganisation($userId);
        if(empty($checkUser)){
            $this->addFlash('echec','Người dùng này không yêu cầu tạo tài khoản organisation. Bạn vui lòng xem xét lại');
            return $this->redirectToRoute('app_homepage');
        }

        $userInfo = $em->getRepository(User::class)->findOneBy([
            'id' => $userId
        ]);
        $organisationName = $userInfo->getOrganisationname();
        $address = $userInfo->getAddress();
        $zipCode = $userInfo->getZipcode();
        $city = $userInfo->getCity();
        $country = $userInfo->getCountry();
        $phone = $userInfo->getPhonenumber();

        $userDocumentId = [DocumentType::Certificate_organisation,DocumentType::Bank_account_information,DocumentType::Awards_justification];
       // $userdocumentId  = $userDocument->getId();

        for($i = 0; $i <sizeof($userDocumentId); $i++){
                $document[$i] = $documentRepository->findDocumentByUserIdAndTypeDoc($userId,$userDocumentId[$i]);
        }

        $certificateOrganisation = $documentRepository->findDocumentByUserIdAndTypeDoc($userId,DocumentType::Certificate_organisation);

        //dd($certificateOrganisation);

        return $this->render('admin/valide_organisation.html.twig',[
            'userInfo' => $this->getUser(),
            'userDocumentId' => $document,
            'IDUserOrganisation' => $userId,
            'organisationName' => $organisationName,
            'zipCode' => $zipCode,
            'city' => $city,
            'country' => $country,
            'address' => $address,
            'phone' => $phone,
            'certificate' => $certificateOrganisation
        ]);
    }


    /**
     * @Route("/admin/user/document/donwload/{id}", name="app_admin_download_user_document",methods={"GET"})
     */
    public function AdminDownloadUserDocument(UserDocument $userDocument, UploadService $uploadService){
       // dd($userDocument->getDocumentUserPath());
        $response = new StreamedResponse(function() use ($userDocument, $uploadService) {
            $outputStream = fopen('php://output', 'wb');
            $fileStream = $uploadService->readStream($userDocument->getDocumentUserPath(), false);
            stream_copy_to_stream($fileStream, $outputStream);
        });
        $response->headers->set('Content-Type', $userDocument->getMimeType());

        // Forced download instead of show in the new table
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $userDocument->getOriginalFilename()
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }


}
