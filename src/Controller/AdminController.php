<?php

namespace App\Controller;

use App\Entity\AdminParameter;
use App\Entity\DocumentType;
use App\Entity\Favorite;
use App\Entity\Post;
use App\Entity\PostSearch;
use App\Entity\PostStatus;
use App\Entity\RequestOrganisationDocument;
use App\Entity\RequestOrganisationInfo;
use App\Entity\RequestStatus;
use App\Entity\User;
use App\Entity\UserDocument;
use App\Form\AdminParameterType;
use App\Form\PostSearchType;
use App\Form\StopPostType;
use App\Repository\PostRepository;
use App\Repository\RequestOrganisationDocumentRepository;
use App\Repository\RequestOrganisationInfoRepository;
use App\Repository\RequestStatusRepository;
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

        return $this->render('admin/post_admin/post_admin.index.html.twig', [
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
        $totalAmount = round($transactionRepository->getTotalAmountbyPost($postInfo->getId()),2);
        $TransactionThisPost = $transactionRepository->getTransactionbyPost($postInfo->getId());

        $datediff = date_diff($postInfo->getFinishAt(),new \DateTime('now'));
        $datediff = $datediff->format('%d');

        $postStatus = new \ReflectionClass('App\Entity\PostStatus');
        $statusArray = $postStatus->getConstants();


        $userFavorite = $em->getRepository(Favorite::class)->findOneBy([
            'user' => $this->getUser(),
            'post' => $postInfo
        ]);
        if(is_null($userFavorite)){
            $favorite = null;
        }else{
            $favorite = $userFavorite->getisFavorite();
        }

        $userEmail = $postInfo->getUser()->getUsername();
        $repository = $em->getRepository(User::class);
        $userInfo = $repository->findOneBy(['email' => $userEmail]);
        $organisationInfo = $em->getRepository(RequestOrganisationInfo::class)->findOneBy([
            'user' => $userInfo
        ]);

        $certificate = $em->getRepository(RequestOrganisationDocument::class)->findLastDocumentByUserIdAndTypeDoc($userInfo, DocumentType::Certificate_organisation);
        $bankAccount =  $em->getRepository(RequestOrganisationDocument::class)->findLastDocumentByUserIdAndTypeDoc($userInfo, DocumentType::Bank_account_information);
        $awards = $em->getRepository(RequestOrganisationDocument::class)->findAllDocumentByUserId($userInfo, DocumentType::Awards_justification);
        //  dd($certificate->getDocumentPath());



        return $this->render('admin/post_admin/show_post.html.twig', [
            'postInfo' => $postInfo,
            'nb_participant' => $nb_participant,
            'totalAmount' => $totalAmount,
            'TransactionThisPost' => $TransactionThisPost,
            'userInfo' => $user,
            'datediff' => $datediff,
            'statusArray' => $statusArray,
            'userFavorite' => $favorite,
            'organisationInfo' => $organisationInfo,
            'certificate' => $certificate,
            'bank'  => $bankAccount,
            'awards' => $awards
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

        return $this->render('admin/post_admin/stop_post.html.twig',[
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


        $this->addFlash('success', 'Post status changed');
        return $this->redirectToRoute('admin_show_post', [
            'uniquekey' => $uniquekey
        ]);
    }

    /**
     * @Route("admin/list_validating_organisation", name="app_list_validate_organisation")
     */
    public function listAskForOrganisationRole(RequestOrganisationInfoRepository $requestOrganisationInfoRepository, PaginatorInterface $paginator, Request $request){

        $waiting_organisation = $requestOrganisationInfoRepository->getDetailWaitingOrganisation();
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
     * @Route("admin/control_demande_organisation/{userId}", name="app_admin_viewDetail_asking_organisation")
     */
    public function ControleOrganisation($userId, UserRepository $userRepository, EntityManagerInterface $em, RequestOrganisationDocumentRepository $requestOrganisationDocumentRepository){

        $checkUser = $userRepository->checkUserIsCreatingOrganisation($userId);
        if(empty($checkUser)){
            $this->addFlash('echec','Người dùng này không yêu cầu tạo tài khoản organisation. Bạn vui lòng xem xét lại');
            return $this->redirectToRoute('app_homepage');
        }

        $OrganisationInfo = $em->getRepository(RequestOrganisationInfo::class)->findOneBy([
            'user' => $userId
        ]);


        $userDocumentId = [DocumentType::Certificate_organisation,DocumentType::Bank_account_information];
       // $userdocumentId  = $userDocument->getId();

        for($i = 0; $i <sizeof($userDocumentId); $i++){
                $document[$i] = $requestOrganisationDocumentRepository->findLastDocumentByUserIdAndTypeDoc($userId,$userDocumentId[$i]);
        }

        $award_documents = $requestOrganisationDocumentRepository->findAllDocumentByUserId($userId,DocumentType::Awards_justification);


        // get all content in RequestStatus
        $requestStatus = new \ReflectionClass('App\Entity\RequestStatus');
        $statusArray = $requestStatus->getConstants();

        //dd($certificateOrganisation);

        return $this->render('admin/valide_organisation.html.twig',[
            'userInfo' => $this->getUser(),
            'userDocumentId' => $document,
            'IDUserOrganisation' => $userId,
            'organisationInfo' => $OrganisationInfo,
            'award_document' => $award_documents,
            'statusArray' => $statusArray
        ]);
    }


    /**
     * @Route("/admin/allow_to_be_organisation/{userId}", name="app_admin_allow_to_be_organisation")
     */
    public function AllowTobeOrganisation($userId, UserRepository $userRepository, EntityManagerInterface $em, Mailer $mailer, RequestOrganisationInfoRepository $infoRepository, RequestStatusRepository $requestStatus, RequestOrganisationDocumentRepository $requestDocument){
        $user = $userRepository->findOneBy([
           'id' => $userId
        ]);
        $role = ['ROLE_ORGANISATION'];

        $user->setIsOrganisation(true)
             ->setAskOrganisation(false)
             ->setRoles($role);
        $em->persist($user);
        $em->flush();

        $validateStatus = $requestStatus->findOneBy([
           'id' => RequestStatus::Request_Validated
        ]);

        $requestInfo = $infoRepository->findOneBy([
            'user' => $userId
        ]);
        //dd($validateStatus);

        $requestInfo->setRequestStatus($validateStatus);

        $em->persist($requestInfo);
        $em->flush();
        //dump($user);
        $document = $requestDocument->findBy([
            'user' => $userId
        ]);

        for($i=0;$i<sizeof($document);$i++){
            $document[$i]->setRequestStatus($validateStatus);
            $em->persist($document[$i]);
            $em->flush();
        }


        $mailer->sendMailCongratulationNewOrganisation($user);

        $this->addFlash('success','Yêu cầu tạo tổ chức đã được kiểm định. 1 email sẽ được gửi tới người sử dụng');
        return $this->redirectToRoute('app_admin_overview');
    }


    /**
     * @Route("/admin/valide/Info/organisation/{userId}", name="app_admin_validateInfo_organisation")
     */
    public function validateInfoOrganisation($userId, RequestStatusRepository $statusRepository, EntityManagerInterface $em){

        $status = $statusRepository->findOneBy([
            'id' => RequestStatus::Request_Validated
        ]);

        $request =  $em->getRepository(RequestOrganisationInfo::class)->findOneBy([
           'user' =>  $userId
        ]);
        $request->setRequestStatus($status);
       // dd($request);

        $em->persist($request);
        $em->flush();

        $this->addFlash("success","OK. This document has been verified");

        return $this->redirectToRoute("app_admin_viewDetail_asking_organisation", [
            'userId' => $userId
        ]);
    }



    /**
     * @Route("/admin/ask_more_detail/Info/organisation/{userId}", name="app_admin_needMoreInfo_organisation")
     */
    public function NeedMoreInfoOrganisation($userId,RequestOrganisationInfo $requestInfo, RequestStatusRepository $statusRepository, EntityManagerInterface $em, RequestOrganisationInfoRepository $infoRepo){

        $status = $statusRepository->findOneBy([
            'id' => RequestStatus::Request_Information_tobe_completed
        ]);

        $request =  $infoRepo->findOneBy([
            'user' =>  $userId
        ]);
        $request->setRequestStatus($status);
        //  dd($requestOrganisationDocument);

        $em->persist($requestInfo);
        $em->flush();

        $this->addFlash("success","OK. This document has been verified");

        return $this->redirectToRoute("app_admin_viewDetail_asking_organisation", [
            'userId' => $userId
        ]);
    }

    /**
     * @Route("/admin/valide/document/organisation/{userId}/{id}", name="app_admin_validateDocument")
     */
    public function validateDocumentOrganisation($userId,RequestOrganisationDocument $requestOrganisationDocument, RequestStatusRepository $statusRepository, EntityManagerInterface $em){

        $status = $statusRepository->findOneBy([
           'id' => RequestStatus::Request_Validated
        ]);
        $requestOrganisationDocument->setRequestStatus($status);
      //  dd($requestOrganisationDocument);

        $em->persist($requestOrganisationDocument);
        $em->flush();

        $this->addFlash("success","OK. This document has been verified");

        return $this->redirectToRoute("app_admin_viewDetail_asking_organisation", [
            'userId' => $userId
        ]);
    }

    /**
     * @Route("/admin/ask_more_detail/document/organisation/{userId}/{id}", name="app_admin_tobe_complete_Document")
     */
    public function NeedMoreDocumentOrganisation($userId,RequestOrganisationDocument $requestOrganisationDocument, RequestStatusRepository $statusRepository, EntityManagerInterface $em){

        $status = $statusRepository->findOneBy([
            'id' => RequestStatus::Request_Information_tobe_completed
        ]);
        $requestOrganisationDocument->setRequestStatus($status);
        //  dd($requestOrganisationDocument);

        $em->persist($requestOrganisationDocument);
        $em->flush();

        $this->addFlash("success","OK. This document has been checked");

        return $this->redirectToRoute("app_admin_viewDetail_asking_organisation", [
            'userId' => $userId
        ]);
    }

    /**
     * @Route("/admin/Demande_info/organisation/{userId}/{choice}<ask|stop>", name="app_admin_Demande_info_Organisation")
     */
    public function askMoreInfoOrStopOrganisation($userId,$choice ,Request $request, Mailer $mailer, UserRepository $userRepository, RequestStatusRepository $statusRepository, EntityManagerInterface $em){
        $form = $this->createForm(StopPostType::class);
        $form->handleRequest($request);
        $user = $userRepository->findOneBy([
           'id' => $userId
        ]);

        $requestInfo = $em->getRepository(RequestOrganisationInfo::class)->findOneBy([
            'user' => $userId
        ]);

        if($choice == "ask"){
            if($form->isSubmitted() && $form->isValid()){

                $status = $statusRepository->findOneBy([
                    'id' => RequestStatus::Request_Information_tobe_completed
                ]);

                $raison = $form['Raison']->getData();

                $requestInfo->setRequestStatus($status);
                //  dd($requestOrganisationDocument);

                $em->persist($requestInfo);
                $em->flush();

                $mailer->sendMailAskMoreInfoOrStopAboutOrganisation($choice, $user, $raison);

                $this->addFlash("success", "1 email sent for user with the content");


                return $this->redirectToRoute('app_list_validate_organisation');
            }
        }elseif ($choice = "stop"){
            if($form->isSubmitted() && $form->isValid()){

                $status = $statusRepository->findOneBy([
                    'id' => RequestStatus::Request_Cancelled
                ]);

                $requestInfo->setRequestStatus($status);
                //  dd($requestOrganisationDocument);

                $em->persist($requestInfo);
                $em->flush();

                $raison = $form['Raison']->getData();
                $mailer->sendMailAskMoreInfoOrStopAboutOrganisation($choice, $user, $raison);

                $this->addFlash("success", "1 email sent for user with the content");
                return $this->redirectToRoute('app_list_validate_organisation');
            }
        }



        return $this->render('admin/demandeInfo_Organisation.htlm.twig',
        [
            'form' => $form->createView(),
            'userInfo' => $this->getUser()
        ]
        );
    }


    /**
     * @Route("/admin/modify_parameter", name="app_admin_parameter")
     */
    public function AdminModifyParameter(Request $request, EntityManagerInterface $em){
            $existedParameter = $em->getRepository(AdminParameter::class)->findLastestParamId();
        //  dd($existedParameter);
            if(is_null($existedParameter)){
                $form = $this->createForm(AdminParameterType::class);
            }else{
                //$existedParameter = $em->getRepository(AdminParameter::class)->findAll();
                $form = $this->createForm(AdminParameterType::class,$existedParameter);
            }

            $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $parameter = $form->getData();
            $em->persist($parameter);
            $em->flush();

            $this->addFlash("success","Parameter saved");
            return $this->redirectToRoute("app_admin_overview");
        }

        return $this->render('admin/parameter.html.twig',[
            'form' => $form->createView(),
            'userInfo' => $this->getUser()
        ]);
    }


}