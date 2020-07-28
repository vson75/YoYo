<?php


namespace App\Controller;
use Cassandra\Date;
use App\Entity\{AdminParameter,
    DocumentType,
    Favorite,
    Post,
    PostStatus,
    RequestOrganisationDocument,
    RequestOrganisationInfo,
    Transaction,
    User};
use App\Form\{CommentFormType, ExtendPostType, PostFormType, PaymentType};
use App\Repository\PostRepository;
use App\Repository\TransactionRepository;
use App\Service\Mailer;
use App\Service\MarkdownHelper;
use App\Service\UploadService;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Stripe\{Stripe, PaymentIntent};

class PostController extends AbstractController
{
    private $security;
    use TargetPathTrait;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage(EntityManagerInterface $em, TransactionRepository $transactionRepository){

        $repository = $em->getRepository(Post::class);
        $post = $repository->findPostByNewest();
        $userInfo = $this->getUser();
      
        

       // dump($post);die;
       // dd($post->getFinishAt());
      

        return $this->render('homepage.html.twig',[
                'post' => $post,
                'userInfo'=> $userInfo,
            ]
        );
    }

    /**
     * @Route("/post/{uniquekey}", name="show_post")
     * @param MarkdownHelper $markdownHelper
     * @return Response
     */
    public function show($uniquekey, MarkdownHelper $markdownHelper, EntityManagerInterface $em, Request $request, TransactionRepository $transactionRepository){

        $repository = $em->getRepository(Post::class);
        $postInfo = $repository->findOneBy([
            'uniquekey'=> $uniquekey
        ]);

        if (is_null($postInfo)) {
            throw $this->createNotFoundException('The Post is not exist');
        }
        $comment     = $postInfo->getComments();
      //  $transaction = $postInfo->getTransactions();

        $nb_participant = $transactionRepository->getNumberParticipantbyPost($postInfo->getId());

        $totalAmount = round($transactionRepository->getTotalAmountbyPost($postInfo->getId()),2);
        $arrayDonationNotAnonymous = $transactionRepository->getTransactionbyPost($postInfo->getId());
       // dd($arrayDonationNotAnonymous);

        $TransactionAnonymous = $postInfo->getTransactionAnonymousSum();

     //  dd($TransactionThisPost);

        $currentUserLooged = $this->security->getUser();

        $datediff = date_diff($postInfo->getFinishAt(),new \DateTime('now'));
        $datediff = $datediff->format('%d');

        $postContentCache = $postInfo->getContent();
        if(!is_null($postContentCache)){
            $postContentCache = $markdownHelper->cacheInfo($postContentCache);
        }

        // save the old URL to redirecte after login
        $this->saveTargetPath($request->getSession(),'main', $request->getUri());

        // comment section

        $form = $this->createForm(CommentFormType::class);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $new_comment = $form->getData();
            $user = $this->getUser();
            $new_comment->setUser($user);
            $new_comment->setPost($postInfo);
            $new_comment->setCreatedAt(new \DateTime('now'));

            // dd($new_comment);
            $em->persist($new_comment);
            $em->flush();

            return $this->redirectToRoute('show_post',[
                'uniquekey' => $uniquekey,
            ]);
        }
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

        $userEmail = $postInfo->getUser()->getEmail();
        $repository = $em->getRepository(User::class);
        $userInfo = $repository->findOneBy(['email' => $userEmail]);
        $organisationInfo = $em->getRepository(RequestOrganisationInfo::class)->findOneBy([
            'user' => $userInfo
        ]);

        $certificate = $em->getRepository(RequestOrganisationDocument::class)->findLastDocumentByUserIdAndTypeDoc($userInfo, DocumentType::Certificate_organisation);

        $awards = $em->getRepository(RequestOrganisationDocument::class)->findAllDocumentByUserId($userInfo, DocumentType::Awards_justification);
      //  dd($certificate->getDocumentPath());

        return $this->render('post/show_post.html.twig',[
                'postInfo' => $postInfo,
                'datediff' => $datediff,
                'comment'=> $comment,
                'commentForm' => $form->createView(),
                'userInfo' => $currentUserLooged,
                'nb_participant' => $nb_participant,
                'totalAmount' => $totalAmount,
                'ArrayDonation' => $arrayDonationNotAnonymous,
                'TotalAnonymous' => $TransactionAnonymous,
                'financeForm' => $this->createForm(PaymentType::class)->createView(),
                'statusArray' => $statusArray,
                'userFavorite' => $favorite,
                'organisationInfo' => $organisationInfo,
                'certificate' => $certificate,
                'awards' => $awards
            ]
        );
    }

    /**
     * @Route("/ajax/add_favorite/{uniquekey}/{isFavorite<0|1>}", name="add_favorite", methods="POST")
     * @IsGranted("ROLE_USER")
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addAjaxFavorite(EntityManagerInterface $em, Post $post, LoggerInterface $logger, $isFavorite){

        $favorite = $em->getRepository(Favorite::class)->findOneBy([
            'user' => $this->getUser(),
            'post' => $post
        ]);
        if($isFavorite == 0){
            $setFav = false;
        }else{
            $setFav = true;
        }
        if(is_null($favorite)){
            $favorite = new Favorite();
            $favorite->setUser($this->getUser())
                ->setPost($post)
                ->setIsFavorite($setFav);
            $em->persist($favorite);
            $em->flush();
        }else{
            $favorite->setIsFavorite($setFav);
            $em->persist($favorite);
            $em->flush();
        }


        $logger->info('a new participant has been finance this project');
        //the key to increase nb Participant = nb_Participant (use this key in post.js)
        return $this->json([
            'nb_Participant'=> $post->getNumberParticipant(),
            'id_post'=>$post->getTitle()
        ]);
    }

    /**
     * @Route("create_new/post", name="app_post_new")
     * @IsGranted("ROLE_USER")
     */
    public function new(EntityManagerInterface $em, Request $request, UploadService $uploadService, Mailer $mailer){
        $form = $this->createForm(PostFormType::class);
        $form->handleRequest($request);
        $user = $this->getUser();

        if($form->isSubmitted() && $form->isValid()){
            //get data in form
            $createNew = $form->getData();
           // dd($createNew);
            
            // add date and user = user created
            $createNew->setPublishedAt(new \DateTime('now'));
            $createNew->setUser($user);

            $finishAt = $form['finishAt']->getData();
            //dd($finishAt);
            if(is_null($finishAt)){
                $finishAt = date_add(new \DateTime('now'),new \DateInterval('P30D'));
            }

            $repo = $em->getRepository(PostStatus::class);
            $postStep = $repo->findOneBy([
                'id' => PostStatus::POST_DRAFT
            ]);
            $createNew->setStatus($postStep);


            $createNew->setFinishAt($finishAt);
          //
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

            $uploadedFile = $form['imageFile']->getData();

            if ($uploadedFile) {
                $newFilename = $uploadService->UploadPostImage($uploadedFile, null);
                $createNew->setImageFilename($newFilename);
            }

            $em->persist($createNew);
          //  dd($createNew);
            $em->flush();

            $this->addFlash('success', 'Cảm ơn bạn đã tạo chủ để này. Bạn có thể gửi ngay dự án cho chúng tôi để chúng tôi kiểm duyệt');

            $template='email/EmailCreateOrDonation.html.twig';
            $subject ="Cảm ơn bạn đã tạo chủ đề mới";
            $title ="YoYo - Tạo dự án mới";
            $action = "tạo dự án mới";
            $caption_link = "Bạn có thể xem dự án mới tạo tại đây: ";
            $mailer->sendMailCreateOrDonationPost($user,$createNew,$template,$subject,$title,$action, $caption_link);

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
            'userInfo' => $user
        ]);
    }

    /**
     * @param EntityManagerInterface $em
     * @param Request $request
     * @param Post $post
     * @Route("post_submit/{uniquekey}", name="app_submit_post")
     */
    public function submitPostToAdmin($uniquekey,EntityManagerInterface $em, Mailer $mailer){
        $repository = $em->getRepository(Post::class);
        $post = $repository->findOneBy([
            'uniquekey'=> $uniquekey
        ]);

        $repo = $em->getRepository(PostStatus::class);

        if (is_null($post)) {
            throw $this->createNotFoundException('The Post is not exist');
        }

        $draft = $repo->findOneBy([
            'id' => PostStatus::POST_DRAFT
        ]);
        $waitingInfo = $repo->findOneBy([
            'id' => PostStatus::POST_WAITING_INFO
        ]);

        if( $post->getStatus() == $draft || $post->getStatus() == $waitingInfo){
            $submit_admin = $repo->findOneBy([
                'id' => PostStatus::POST_SUBMIT_TO_ADMIN
            ]);
            $postNewStatus = $post->setStatus($submit_admin);
            $em->persist($postNewStatus);
            $em->flush();

            $template='email/EmailCreateOrDonation.html.twig';
            $subject ="Dự án ".$post->getTitle()." đã được gửi tới ban quản trị";
            $title ="";
            $action = "gửi ban quản trị dự án: ";
            $caption_link = "Chúng tôi sẽ nhanh chóng kiểm tra dự án của bạn trước khi cho phép đăng lên trang của chúng tôi ";
            $mailer->sendMailCreateOrDonationPost($post->getUser(),$post,$template,$subject,$title,$action, $caption_link);

            $this->addFlash('success', 'Dự án của bạn đã được gửi tới ban quản trị. Chúng tôi sẽ kiểm duyệt dự án của bạn trước khi cho phép đăng lên trang của chúng tôi');

        }else{

            $this->addFlash('echec', 'Dự án của bạn không được phép gửi tới chúng tôi');

        }

        return $this->redirectToRoute('show_post', [
            'uniquekey' => $uniquekey
        ]);

    }

    /**
     * @Route("edit/post/{uniquekey}")
     * @IsGranted("ROLE_USER")
     */
    public function edit($uniquekey,EntityManagerInterface $em, Request $request, Post $post, UploadService $uploadService){
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);
        $user = $this->getUser();

        if($form->isSubmitted() && $form->isValid()){
            $updatePost = $form->getData();
           // $id_post = $updatePost->getId();

            $uploadedFile = $form['imageFile']->getData();

            if ($uploadedFile) {
                $newFilename = $uploadService->UploadPostImage($uploadedFile, $post->getImageFilename());
                $updatePost->setImageFilename($newFilename);
            }
           

            $em->persist($updatePost);
            $em->flush();
            $this->addFlash('success', 'Sửa đổi thành công');

            return $this->redirectToRoute('show_post',[
                'uniquekey' => $uniquekey,
            ]);
        }

        return $this->render('post/edit_post.html.twig',[
            'postForm' => $form->createView(),
            'post'=> $post,
            'userInfo' => $user
        ]);

    }

    /**
     * @Route("/funding/{uniquekey}", name="app_payment")
     * @IsGranted("ROLE_USER")
     */
    public function fundingStep1($uniquekey, Request $request, EntityManagerInterface $em){

        $user = $this->getUser();
        $financeForm = $this->createForm(PaymentType::class);
        $financeForm->handleRequest($request);

        $repository = $em->getRepository(Post::class);
        $postInfo = $repository->findOneBy([
            'uniquekey'=> $uniquekey
        ]);

        //dd($postInfo);
        if (is_null($postInfo)) {
            throw $this->createNotFoundException('The Post is not exist');
        }

        $managementFees = $em->getRepository(AdminParameter::class)->findLastestParamId();


        return $this->render('post/funding_step_1.html.twig', [
            'userInfo' => $user,
            'financeForm' => $financeForm->createView(),
            'postInfo' => $postInfo,
            'ManagementFees' => $managementFees->getManagementFees(),
            'FixedFees' => $managementFees->getFixedFees()
        ]);
    }

    /**
     * @Route("finance/{uniquekey}", name="app_finance")
     * @IsGranted("ROLE_USER")
     * this function is called to add payment intent
     */
    public function fundingStep2(Post $post, Request $request, EntityManagerInterface $em, $uniquekey)
    {
        $financeForm = $this->createForm(PaymentType::class);
        $financeForm->handleRequest($request);

        $user = $this->getUser();


        $repo = $em->getRepository(Post::class);
        $postInfo = $repo->findOneBy([
            'uniquekey' => $uniquekey,
        ]);



        //dd($financeForm->getData());

        if ($financeForm->isSubmitted() && $financeForm->isValid()) {
            $donationAmount = $financeForm->getData()['amount'];
            $givingAmount = $financeForm->getData()['giveForSite'];
         //   dd($givingAmount);
            if(is_null($givingAmount)){
                $givingAmount = 0;
            }
            $amount = $donationAmount + $givingAmount;
            //dd($amount);

            $paramAdmin = $em->getRepository(AdminParameter::class)->findLastestParamId();
            $percentManagementFees = $paramAdmin->getManagementFees() * $donationAmount;
            $fixedFees = $paramAdmin->getFixedFees();
            $totalFees = $percentManagementFees + $fixedFees;
            $donationAfterFees = $donationAmount - $totalFees;


            Stripe::setApiKey('sk_test_gxLCkDYIJRoJXx7Ovh4RqBTB00aHGuN3mt');
            $intent = PaymentIntent::create([
                'amount'   => $amount*100,
                'currency' => 'eur',
                'description' => $postInfo->getId().' - '.$postInfo->getUniquekey(),
                'metadata' => ['integration_check' => 'accept_a_payment']
            ]);



        } else {
            $this->addFlash('echec', 'Something wrong with your paiement, the valid form is not correct.');
            return $this->redirectToRoute('show_post', ['uniquekey' => $post->getUniqueKey()]);
        }
        

        return $this->render('post/funding_step_2.html.twig',[
            'userInfo'      => $user,
            'clientSecret' => $intent->client_secret,
            'amount'        => $amount,
            'givingAmount' => $givingAmount,
            'postInfo' => $postInfo,
            'donationIncludeFees' => $donationAfterFees,
            'totalFees' => $totalFees
        ]);
    }


    /**
     * @Route("/add_transaction/{uniquekey}/{clientSecret}/{amount}/{give}/{anonyme}", methods="POST", name="app_add_transaction")
     */
    public function addTransactioninDB($uniquekey, $clientSecret, $amount,$give, $anonyme, EntityManagerInterface $em, Mailer $mailer, Request $request){

        $repo = $em->getRepository(Post::class);
        $post = $repo->findOneBy([
           'uniquekey' => $uniquekey
        ]);

        $submittedToken = $request->request->get('token');

        /**
         add log to debug

        $logger->debug('submitted token : ',[
        'submittoken' => $submittedToken
        ]
        );
         */
        $repoAdminParameter = $em->getRepository(AdminParameter::class);
        $AdminParameter = $repoAdminParameter->findLastestParamId();
        $percentManagementFees = $AdminParameter->getmanagementFees();
        $fixedFees = $AdminParameter->getfixedFees();
        $fees = ($amount-$give) *$percentManagementFees + $fixedFees;


        if ($this->isCsrfTokenValid('funding_step', $submittedToken)) {

            $transaction = new Transaction();

            $transaction->setUser($this->getUser())
                ->setPost($post)
                ->setAmount($amount)
                ->setFees($fees)
                ->setAmountAfterFees($amount - $fees)
                ->setCustomDonationForSite($give)
                ->setClientSecret($clientSecret)
                ->setTransfertAt(new \DateTime('now'));
            // $anonyme is consider for as a string

            if($anonyme == 'true'){
                $transaction->setAnonymousDonation(1);
            }else{
                $transaction->setAnonymousDonation(0);
            }
            //dd($transaction);

            $em->persist($transaction);
            $em->flush();


            $template='email/EmailCreateOrDonation.html.twig';
            $subject ="Cảm ơn bạn đã quyên góp cho dự án ".$post->getTitle();
            $title ="YoYo - quyên góp dự án";
            $action = "quyên góp cho dự án: ";
            $caption_link = "Bạn có thể xem dự án bạn mới quyên góp tại đây: ";
            $mailer->sendMailCreateOrDonationPost($this->getUser(),$post,$template,$subject,$title,$action, $caption_link);

            //$this->addFlash('success', 'Cảm ơn bạn đã quyên góp tiền');
            return $this->json([
                'transaction' => $transaction
            ]);
        }else{
         //   $logger->log('500','Token is not valid in paiement Step 2');
            return $this->redirectToRoute('app_homepage');
        }




    }

    /**
     * @Route("/extend_post/{uniquekey}", name="app_extend_post")
     * @IsGranted("ROLE_USER")
     */
    public function extendPost($uniquekey, EntityManagerInterface $em, Request $request, Post $post){

        $repository = $em->getRepository(Post::class);
        $postInfo = $repository->findOneBy([
            'uniquekey'=> $uniquekey
        ]);

        if (is_null($postInfo)){
            throw $this->createNotFoundException('The Post is not exist');
        }
        if($this->getUser() == $postInfo->getUser()){
            $form = $this->createForm(ExtendPostType::class, $post);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){
                $newExpiredDate = $form->getData();
                $em->persist($newExpiredDate);
                $em->flush();
                $this->addFlash("success", "Dự án của bạn đã gia hạn thành công");
                return $this->redirectToRoute('show_post',['uniquekey'=>$uniquekey]);
            }

        }else{
           $this->addFlash('echec', 'Sorry you are not the author of this project, you cant extend this project');
           return $this->redirectToRoute('app_homepage');
        }

        return $this->render('post/extend_post.html.twig',[
            'postForm' => $form->createView(),
            'postInfo' => $postInfo,
            'userInfo' => $this->getUser()
        ]);

    }

    /**
     * @param EntityManagerInterface $em
     * @Route("/transfert_fund/post/{uniquekey}", name="app_transfert_fund")
     */
    public function transfertFundPost(EntityManagerInterface $em, $uniquekey, Request $request){
        $repository = $em->getRepository(Post::class);
        $postInfo = $repository->findOneBy([
            'uniquekey'=> $uniquekey
        ]);

        if (is_null($postInfo)){
            throw $this->createNotFoundException('The Post is not exist');
        }
        if($this->getUser() == $postInfo->getUser()){
            $repo = $em->getRepository(PostStatus::class);
            $postStep = $repo->findOneBy([
                'id' => PostStatus::POST_TRANSFERT_FUND
            ]);
            $postInfo->setStatus($postStep);
            $em->persist($postInfo);
            $em->flush();
            $this->addFlash("success", "Chúng tôi sẽ bắt đầu quá trình chuyển khoản. Bạn sẽ nhận được các thông tin chi tiết cho mỗi lần chuyển khoản cũng như danh sách đã ủng hộ cho dự án của bạn.");
            return $this->redirectToRoute('show_post',['uniquekey'=>$uniquekey]);
        }else{
            $this->addFlash('echec', 'Sorry you are not the author of this project, you cant decide to transfert fund this project');
            return $this->redirectToRoute('app_homepage');
        }
    }


}