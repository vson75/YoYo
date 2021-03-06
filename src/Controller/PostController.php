<?php


namespace App\Controller;


use App\Entity\{AdminParameter,
    DocumentType,
    EmailContent,
    Emails,
    Favorite,
    Post,
    PostDateHistoric,
    PostDateType,
    PostDocument,
    PostStatus,
    PostTranslation,
    RequestOrganisationDocument,
    RequestOrganisationInfo,
    Transaction,
    User,
    WebsiteLanguage};
use App\Service\PostDateHistoricService;
use App\Form\{CommentFormType,
    ExtendPostType,
    PostFormType,
    PaymentType,
    ReceivedFundType,
    TranslationPostType,
    UpdateAdvancementType};
use App\Repository\PostRepository;
use App\Repository\TransactionRepository;
use App\Service\Mailer;
use App\Service\MarkdownHelper;
use App\Service\SpreadsheetService;
use App\Service\UploadService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Sluggable\Util\Urlizer;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Stripe\{Stripe, PaymentIntent};
use Symfony\Component\Validator\Constraints\Unique;
use Symfony\Contracts\Translation\TranslatorInterface;

class PostController extends AbstractController
{
    private $security;
    use TargetPathTrait;
    private $translator;

    public function __construct(Security $security, TranslatorInterface $translator)
    {
        $this->security = $security;
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="app_homepage")
     */
    public function homepage(EntityManagerInterface $em, Request $request){

        $repository = $em->getRepository(Post::class);
        $userInfo = $this->getUser();
      //  dd($userInfo);
        $array_user_fav_post = [];
        $q = $request->query->get('isFavorite');
      //var_dump($q);
        //show post by newest with the status = Collectinf or filter favorite
        if(is_null($userInfo)){
            $post = $repository->findPostByNewest();
            //show post by newest with status = Finish collect OR Transfering Fund
            $postFinishCollect = $repository->findPostFinishCollect();
        }else{

            // get the list post favorite by user
            $repo_Favorite = $em->getRepository(Favorite::class);
            $user_fav_post = $repo_Favorite->getDistinctFavoriteByUser($userInfo->getId());
       
            
            for ($i=0; $i < count($user_fav_post) ; $i++) { 
                # code...
                array_push($array_user_fav_post, $user_fav_post[$i][1]);
            }
            //dd($array_user_fav_post);

            // if user filter in favorite 
            if(is_null($q) or $q === '0'){
                $post = $repository->findPostByNewest();
                $postFinishCollect = $repository->findPostFinishCollect();
            }else{
                $post = $repository->findPostByFavorite($userInfo,true);
                $postFinishCollect = $repository->findPostByFavorite($userInfo,false);
            }
        }

        //get the list of Status
        $postStatus = new \ReflectionClass('App\Entity\PostStatus');
        $statusArray = $postStatus->getConstants();

        //get post in collect
        $number_post_in_collect = $repository->countDistinctPostByStatus(PostStatus::POST_COLLECTING);
        
        return $this->render('homepage.html.twig',[
                'post' => $post,
                'postFinishCollect' => $postFinishCollect,
                'status' => $statusArray,
                'userInfo'=> $userInfo,
                'nb_post_in_collect' => $number_post_in_collect,
                'array_user_fav_post' => $array_user_fav_post,
            ]
        );
    }


    /**
     * @Route("/post/{uniquekey}", name="show_post")
     * @param MarkdownHelper $markdownHelper
     * @return Response
     */
    public function show($uniquekey, MarkdownHelper $markdownHelper, EntityManagerInterface $em, Request $request, TransactionRepository $transactionRepository){

        // find the post
        $repository = $em->getRepository(Post::class);
        $postInfo = $repository->findOneBy([
            'uniquekey'=> $uniquekey
        ]);

        // search if the post have a translation
        $en = $em->getRepository(WebsiteLanguage::class)->findOneBy([
            'id' => WebsiteLanguage::lang_en
        ]);
        $fr = $em->getRepository(WebsiteLanguage::class)->findOneBy([
            'id' => WebsiteLanguage::lang_fr
        ]);

        //find the translation of the post. If the post is not translated, use the original version.
        $repoPostTranslation = $em->getRepository(PostTranslation::class);
        $postTranslateEN = $repoPostTranslation->findOneBy([
           'post' => $postInfo,
            'lang' => $en
        ]);
        $postTranslateFR = $repoPostTranslation->findOneBy([
            'post' => $postInfo,
            'lang' => $fr
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

        $datenow = new \DateTime('now');
        $datediff = date_diff($datenow,$postInfo->getFinishAt())->format("%R%a");
       // dd($datediff);

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

        //get the postStatus const in order to check by status in front
        $postStatus = new \ReflectionClass('App\Entity\PostStatus');
        $statusArray = $postStatus->getConstants();

        // save or unsave favorite post
        $userFavorite = $em->getRepository(Favorite::class)->findOneBy([
            'user' => $this->getUser(),
            'post' => $postInfo
        ]);
        if(is_null($userFavorite)){
            $favorite = null;
        }else{
            $favorite = $userFavorite->getisFavorite();
        }
        //dd($favorite);
        // show the information of the Organisation
        $userEmail = $postInfo->getUser()->getEmail();
        $repository = $em->getRepository(User::class);
        $userInfo = $repository->findOneBy(['email' => $userEmail]);
        $organisationInfo = $em->getRepository(RequestOrganisationInfo::class)->findOneBy([
            'user' => $userInfo
        ]);

        //show document of the organisation
        $certificate = $em->getRepository(RequestOrganisationDocument::class)->findLastDocumentByUserIdAndTypeDoc($userInfo, DocumentType::Certificate_organisation);

        $awards = $em->getRepository(RequestOrganisationDocument::class)->findAllDocumentByUserId($userInfo, DocumentType::Awards_justification);
      //  dd($certificate->getDocumentPath());

        //get Post Date historic for the timeLine
        $postDateRepo = $em->getRepository(PostDateHistoric::class);

        $Date_Start_Collect = $postDateRepo->findPostDateHistoricByPost($postInfo, PostDateType::Date_start_collect_fund, null);
        $Date_End_Collect = $postDateRepo->findPostDateHistoricByPost($postInfo, PostDateType::Date_end_collect_fund, null);
        $Date_Received_Fund = $postDateRepo->findPostDateHistoricByPost($postInfo, PostDateType::Date_author_received_fund, null);
        $Array_Date_UpDateInfo = $postDateRepo->selectDistinctByDate($postInfo, PostDateType::Date_update_info_project_in_progress);
        $Date_Finish_Project = $postDateRepo->findPostDateHistoricByPost($postInfo, PostDateType::Date_close_project, null);
        
        //dd($DateReceivedFund);
        
        if(!empty($Array_Date_UpDateInfo)){
            for ($i=0; $i < count($Array_Date_UpDateInfo) ; $i++) { 
                $Date_Update_Info[$i] = $postDateRepo->findPostDateHistoricByPost($postInfo, PostDateType::Date_update_info_project_in_progress,$Array_Date_UpDateInfo[$i]['date']);     
            }
        }else{
            $Date_Update_Info = [];
        }
        //dd($Date_Update_Info);

        return $this->render('show_post_base.html.twig',[
                'postInfo' => $postInfo,
                'postTranslateEN' => $postTranslateEN,
                'postTranslateFR' => $postTranslateFR,
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
                'awards' => $awards,
                'startCollectDate' => $Date_Start_Collect,
                'endCollectDate' => $Date_End_Collect,
                'receivedFundDate' => $Date_Received_Fund,
                'updateInfoDate' => $Date_Update_Info,
                'DateCloseProject' => $Date_Finish_Project
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

            $repoLang = $em->getRepository(WebsiteLanguage::class);

            $langEN = $repoLang->findOneBy([
               'id' => WebsiteLanguage::lang_en
            ]);
            $langFR = $repoLang->findOneBy([
                'id' => WebsiteLanguage::lang_fr
            ]);
            $langOther = $repoLang->findOneBy([
                'id' => WebsiteLanguage::lang_other
            ]);

            $locale = $request->getLocale();
            if($locale === "en"){
                $createNew->setLang($langEN);
            }elseif ($locale === "fr"){
                $createNew->setLang($langFR);
            }else{
                $createNew->setLang($langOther);
            }

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

            $message = $this->translator->trans('message.post.thankToCreatePost');
            $this->addFlash('success', $message);

            $template='email/EmailCreateOrDonation.html.twig';
            $subject = $this->translator->trans('email.subject.thankToCreateNewProject');
            $title = $this->translator->trans('email.title.CreateNewProject');
            $action = $this->translator->trans('email.action.CreateNewProject');
            $caption_link = $this->translator->trans('email.captionLink.checkproject').": ";
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
                'id' => PostStatus::POST_WAITING_VALIDATION
            ]);
            $postNewStatus = $post->setStatus($submit_admin);
            $em->persist($postNewStatus);
            $em->flush();

            $template='email/EmailCreateOrDonation.html.twig';
            $subject =$this->translator->trans('email.subject.project')." ".$post->getTitle()." ".$this->translator->trans('email.subject.sentToAdmin');
            $title ="";
            $action = $this->translator->trans('email.action.sendToAdmin');
            $caption_link = $this->translator->trans('email.captionLink.CheckProjectBeforePublish');
            $mailer->sendMailCreateOrDonationPost($post->getUser(),$post,$template,$subject,$title,$action, $caption_link);


            $message = $this->translator->trans('message.post.confirmSentPost');
            $this->addFlash('success', $message);

        }else{
            $message = $this->translator->trans('message.post.PostCannotSent');
            $this->addFlash('echec', $message);

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

            $message = $this->translator->trans('message.post.changedSuccess');
            $this->addFlash('success', $message);

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
        $FundingStep1 = $this->createForm(PaymentType::class);
        $FundingStep1->handleRequest($request);

        $repository = $em->getRepository(Post::class);
        $postInfo = $repository->findOneBy([
            'uniquekey'=> $uniquekey
        ]);

        //dd($postInfo);
        $date_expired = $postInfo->getFinishAt();
        $date_now = new DateTime('now');
        if($date_now->format('d/m/Y') < $date_expired->format('d/m/Y')){
            $message = $this->translator->trans('message.post.expriredDate');
            $this->addFlash('echec', $message);
            return $this->redirectToRoute('show_post', [
                'uniquekey' => $postInfo->getUniqueKey()
                ]);
        }
        

        if (is_null($postInfo)) {
            throw $this->createNotFoundException('The Post is not exist');
        }

        $managementFees = $em->getRepository(AdminParameter::class)->findLastestId();
        $transactionRepository = $em->getRepository(Transaction::class);
        $totalAmount = round($transactionRepository->getTotalAmountbyPost($postInfo->getId()),2);

        return $this->render('post/funding_step_1.html.twig', [
            'userInfo' => $user,
            'FundingStep1' => $FundingStep1->createView(),
            'postInfo' => $postInfo,
            'ManagementFees' => $managementFees->getvariableFees(),
            'FixedFees' => $managementFees->getFixedFees(),
            'totalAmount' => $totalAmount
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

        $transactionRepository = $em->getRepository(Transaction::class);
        $totalAmount = round($transactionRepository->getTotalAmountbyPost($postInfo->getId()),2);

        if ($financeForm->isSubmitted() && $financeForm->isValid()) {
            $donationAmount = $financeForm->getData()['amount'];
            $givingAmount = $financeForm->getData()['giveForSite'];
         //   dd($givingAmount);
            if(is_null($givingAmount)){
                $givingAmount = 0;
            }
            $amount = $donationAmount + $givingAmount;
            //dd($amount);

            $paramAdmin = $em->getRepository(AdminParameter::class)->findLastestId();
            $variableFees = $paramAdmin->getvariableFees() * $donationAmount;
            $fixedFees = $paramAdmin->getFixedFees();
            $totalFees = $variableFees + $fixedFees;
            $donationAfterFees = $donationAmount - $totalFees;

            $stripe_pk_key = $this->getParameter('stripe_pk_key');
            $stripe_sk_key = $this->getParameter('stripe_sk_key');
          //  dd($stripe_sk_key);

            Stripe::setApiKey($stripe_sk_key);
            $intent = PaymentIntent::create([
                'amount'   => $amount*100,
                'currency' => 'eur',
                'description' => $postInfo->getId().' - '.$postInfo->getUniquekey(),
                'metadata' => ['integration_check' => 'accept_a_payment']
            ]);



        } else {

            $message = $this->translator->trans('message.post.PaymentInvalid');
            $this->addFlash('echec', $message);
            return $this->redirectToRoute('show_post', ['uniquekey' => $post->getUniqueKey()]);
        }
        

        return $this->render('post/funding_step_2.html.twig',[
            'userInfo'      => $user,
            'clientSecret' => $intent->client_secret,
            'amount'        => $amount,
            'givingAmount' => $givingAmount,
            'postInfo' => $postInfo,
            'donationIncludeFees' => $donationAfterFees,
            'totalFees' => $totalFees,
            'stripe_pk_key' => $stripe_pk_key,
            'totalAmount' => $totalAmount
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
        $AdminParameter = $repoAdminParameter->findLastestId();
        $variableFees = $AdminParameter->getvariableFees();
        $fixedFees = $AdminParameter->getfixedFees();
        $fees = ($amount-$give) *$variableFees + $fixedFees;


        if ($this->isCsrfTokenValid('funding_step', $submittedToken)) {

            $transaction = new Transaction();

            $transaction->setUser($this->getUser())
                ->setPost($post)
                ->setAmount($amount)
                ->setFees($fees)
                ->setAmountAfterFees($amount - $fees - $give)
                ->setCustomDonationForSite($give)
                ->setClientSecret($clientSecret)
                ->setTransfertAt(new \DateTime('now'));
            // $anonyme is consider for as a string

            if($anonyme == 'true'){
                $transaction->setAnonymousDonation(1);
            }else{
                $transaction->setAnonymousDonation(0);
            }
            $em->persist($transaction);
            $em->flush();


            $template='email/EmailCreateOrDonation.html.twig';
            $subject = $this->translator->trans('email.subject.ThanktoFund')." ".$post->getTitle();
            $title = $this->translator->trans('email.title.ThanktoFund');
            $action = $this->translator->trans('email.action.fundproject').": ";
            $caption_link = $this->translator->trans('email.captionLink.checkproject').": ";
            $mailer->sendMailCreateOrDonationPost($this->getUser(),$post,$template,$subject,$title,$action, $caption_link);

            return $this->json([
                'transaction' => $transaction
            ]);
        }else{
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

                $message = $this->translator->trans('message.post.extendPostSuccess');
                $this->addFlash("success", $message);
                return $this->redirectToRoute('show_post',['uniquekey'=>$uniquekey]);
            }

        }else{
           $message = $this->translator->trans('message.post.notAuthor');
           $this->addFlash('echec', $message);
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
     * @Route("/stop_collect/post/{uniquekey}", name="app_stop_collect_post")
     */
    public function StopCollectFund(EntityManagerInterface $em, $uniquekey, PostDateHistoricService $postDateHistoric, Mailer $mailer, SpreadsheetService $spreadsheetService){
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
                'id' => PostStatus::POST_FINISH_COLLECTING
            ]);
            $postInfo->setStatus($postStep);
            $em->persist($postInfo);
            $em->flush();

            $postDateHistoric->InsertNewPostDateHistorical($postInfo,$this->getUser(),PostDateType::Date_end_collect_fund, null);
            $ExcelFileSummary = $spreadsheetService->CreateSummaryDonateByPost($postInfo);
            $mailer->sendMailToAuthorWhenFinishedCollectingPost($this->getUser(), $postInfo, $ExcelFileSummary); 

            $message = $this->translator->trans('message.post.StartedTransfertFund');
            $this->addFlash("success", $message);
            return $this->redirectToRoute('show_post',['uniquekey'=>$uniquekey]);
        }else{
            $message = $this->translator->trans('message.post.notAuthor');
            $this->addFlash('echec', $message);
            return $this->redirectToRoute('show_post',['uniquekey'=>$uniquekey]);
        }
    }


    /**
     * @Route("/translation_post/{lang<en|fr>}/{uniquekey}", name="app_post_translation")
     * languague by default = EN
     */
    public function translationPost($uniquekey, $lang, EntityManagerInterface $em, Request $request){
        $repo = $em->getRepository(Post::class);
        $post = $repo->findOneBy([
            'uniquekey' => $uniquekey
        ]);

        $repoWebLang = $em->getRepository(WebsiteLanguage::class);

        if($lang === 'en'){
            $langToTranslate = $repoWebLang->findOneBy([
               'id' => WebsiteLanguage::lang_en
            ]);
        }elseif($lang === 'fr'){
            $langToTranslate = $repoWebLang->findOneBy([
                'id' => WebsiteLanguage::lang_fr
            ]);
        }

        $repoTranslation = $em->getRepository(PostTranslation::class);
        $postTranslation = $repoTranslation->findOneBy([
            'post' => $post,
            'lang' => $langToTranslate
        ]);
        if(is_null($postTranslation)){
            $form = $this->createForm(TranslationPostType::class);
            $form->handleRequest($request);
        }else{
            $form = $this->createForm(TranslationPostType::class,$postTranslation);
            $form->handleRequest($request);
        }

        // check if user is a author of the post
        if($this->getUser() == $post->getUser()){
            // if null : create new. If not: update

            if($form->isSubmitted() && $form->isValid()){
                $trad = $form->getData();
                $trad->settitle($form['title']->getData())
                    ->setContent($form['content']->getData())
                    ->setPost($post)
                    ->setLang($langToTranslate);


                $em->persist($trad);
                $em->flush();
                $message = $this->translator->trans('message.post.translateOK');
                $this->addFlash("success",$message);
                return $this->redirectToRoute('show_post', ['uniquekey' => $uniquekey]);
            }
        }else{
            $message = $this->translator->trans('message.post.notAuthor');
            $this->addFlash("echec",$message);
            return $this->redirectToRoute('show_post', ['uniquekey' => $uniquekey]);

        }


        return $this->render('post/translation_post.html.twig',[
            'userInfo' => $this->getUser(),
            'postInfo' => $post,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/confirm_received_fund/{uniquekey}", name="app_confirmReceivedFund")
     */
    public function confirmReceivedFund($uniquekey, EntityManagerInterface $em, Post $post, Request $request, UploadService $uploadService, PostDateHistoricService $postDateHistoric){
        $repo = $em->getRepository(Post::class);
        $post = $repo->findOneBy([
            'uniquekey' => $uniquekey
        ]);

        $form = $this->createForm(ReceivedFundType::class);
        $form->handleRequest($request);

        if(!is_null($post)) {
            //check if the current user is a author of the post

            if ($post->getUser() != $this->getUser() && $this->security->isGranted('ROLE_ADMIN') == false ) {
                $message = $this->translator->trans('message.post.notAuthor');
                $this->addFlash('echec', $message);
                return $this->redirectToRoute('show_post', [
                    'uniquekey' => $uniquekey
                ]);
            } else {
                if ($form->isSubmitted() && $form->isValid()) {
                    $docType = $em->getRepository(DocumentType::class)->findOneBy([
                        'id' => DocumentType::Proof_Of_Received_Fund
                    ]);

                    $PostProjectInProgress = $em->getRepository(PostStatus::class)->findOneBy([
                        'id' => PostStatus::POST_IN_PROGRESS
                    ]);
                    //upload proof of transfer in private document
                    //$uploadService->uploadPrivateProofBank($form['proofOfReveived']->getData(),$post,DocumentType::Proof_Of_Received_Fund);

                    // get the filename of the uploaded document (because the upload method return the newFilename)
                    $filename = $uploadService->uploadProofOfProject($form['proofOfReveived']->getData(), $post, DocumentType::Proof_Of_Received_Fund);
                    //query find DocumentType with ID


                    // create new line in DB for the document
                    $proofTransfer = new PostDocument();
                    $proofTransfer->setFilename($filename)
                        ->setPost($post)
                        ->setOriginalFilename($form['proofOfReveived']->getData()->getClientOriginalName())
                        ->setDocumentType($docType)
                        ->setMimeType($form['proofOfReveived']->getData()->getMimeType() ?? 'application/octet-stream')
                        ->setDepositeDate(new \DateTime('now'));

                    $em->persist($proofTransfer);
                    $em->flush();

                    // change post status
                    $post->setStatus($PostProjectInProgress);
                    $em->persist($post);
                    $em->flush();

                    //update the date in historic Post
                    $postDateHistoric->InsertNewPostDateHistorical($post, $this->getUser(), PostDateType::Date_author_received_fund, $proofTransfer);

                    $message = $this->translator->trans('message.admin.confirmReceivedFund');
                    $this->addFlash('success', $message);
                    return $this->redirectToRoute('show_post', [
                        'uniquekey' => $uniquekey
                    ]);

                }
            }
        }else {
            $message = $this->translator->trans('message.post.NotExiste');
            $this->addFlash('success', $message);
            return $this->redirectToRoute('app_homepage');
        }

        return $this->render('admin/confirm_received_fund.html.twig', [
            'userInfo' => $this->getUser(),
            'form' => $form->createView(),
            'post' => $post
        ]);
    }


    /**
     * @Route("/download/post_document/{id}", name="app_download_post_document",methods={"GET"})
     */
    public function DownloadProofReceivedDocument(PostDocument $postDocument, UploadService $uploadService){

            //dd($postDocument->getDocumentPath());
            $response = new StreamedResponse(function() use ($postDocument, $uploadService) {
                $outputStream = fopen('php://output', 'wb');
                $fileStream = $uploadService->readStream($postDocument->getProofReceivedPathForDownload(), true);

                stream_copy_to_stream($fileStream, $outputStream);
            });
            $response->headers->set('Content-Type', $postDocument->getMimeType());
       // dd($response);

            // Forced download instead of show in the new table
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $postDocument->getOriginalFilename()
        );

            $response->headers->set('Content-Disposition', $disposition);
        //dd($response);
            return $response;
    }

    /**
     * @Route("/update/advancement/{choice<update|close>}/{uniquekey}", name="app_update_advancement")
     */
    public function updateAdvancementOfProject($choice ,$uniquekey ,Post $post, EntityManagerInterface $em, Request $request, UploadService $uploadService, Mailer $mailer){
        $post = $em->getRepository(Post::class)->findOneBy([
            'uniquekey' => $uniquekey
        ]);
        if(is_null($post)){
            $message = $this->translator->trans('message.post.NotExiste');
            $this->addFlash('echec', $message);
        }elseif( $this->getUser() == $post->getUser() || $this->getUser() == $this->security->isGranted('ROLE_ADMIN') ){

            $form = $this->createForm(UpdateAdvancementType::class);
            $form->handleRequest($request);

            if($form->isSubmitted() && $form->isValid()){

                // create new Email content
                $emailContent = new EmailContent();
                $emailContent   ->setObject($form['objectEmail']->getData())
                                ->setContent($form['email']->getData())
                                ->setPost($post);
                $em->persist($emailContent);
                $em->flush();


                // get list contributor and  for each recipient create a line data in Email table
                $listUser = $em->getRepository(Transaction::class)->findDistinctDonatorByPost($post);
                // For each contributor, put the data into the table Emails
                for ($i=0; $i < count($listUser); $i++) { 
                    
                    $user = $em->getRepository(User::class)->findOneBy([
                        'id' => $listUser[$i][1]
                    ]);
                    //dd($user->getEmail());
                    $email = new Emails();
                    $email  ->setEmailContent($emailContent)
                            ->setUserRecipient($user);
                    $em->persist($email);
                    $em->flush();
                }

                //get DocType Proof Of Project in progress
                if($choice === 'update'){
                    $docType = $em->getRepository(DocumentType::class)->findOneBy([
                        'id' => DocumentType::Proof_Of_Project_In_Progress
                    ]);

                    $postDateType = $em->getRepository(PostDateType::class)->findOneBy([
                        'id' => PostDateType::Date_update_info_project_in_progress
                    ]);
                }elseif($choice === 'close'){
                    $docType = $em->getRepository(DocumentType::class)->findOneBy([
                        'id' => DocumentType::Proof_Close_Project
                    ]);

                    $postDateType = $em->getRepository(PostDateType::class)->findOneBy([
                        'id' => PostDateType::Date_close_project
                    ]);
                }

                //All document array key start with "image". So get the search key "image"
                $search = "image";
                $search_length = strlen($search);
                
                // For each key value in data form, check if have the key "image". If Yes, get data content
                foreach ($form->getData() as $key => $value) {
                
                    if (substr($key, 0, $search_length) == $search) {
                    

                    // For each image, INSERT data in Post_Document and PostDateHistoric
                        if(!is_null($form[$key]->getData())){
                            // Upload the image in our repertory
                            if($choice === 'update'){
                                $filename = $uploadService->uploadProofOfProject($form[$key]->getData(), $post, DocumentType::Proof_Of_Project_In_Progress);
                            }elseif($choice === 'close'){
                                $filename = $uploadService->uploadProofOfProject($form[$key]->getData(), $post, DocumentType::Proof_Close_Project);
                            }
                            
                            //INSERT New data with the filename
                            $postDocument = new PostDocument();
                            $postDocument   ->setFilename($filename)
                                            ->setPost($post)
                                            ->setOriginalFilename($form[$key]->getData()->getClientOriginalName())
                                            ->setMimeType($form[$key]->getData()->getMimeType() ?? 'application/octet-stream')
                                            ->setDepositeDate(new \DateTime('now'))
                                            ->setEmailContent($emailContent)
                                            ->setDocumentType($docType);

                            $em->persist($postDocument);
                            $em->flush();

                            // Add new data in PostDateHistoric
                            $postDateHistoric = new PostDateHistoric();
                            $postDateHistoric   ->setDate(new DateTime('now'))
                                                ->setPost($post)
                                                ->setUser($this->getUser())
                                                ->setPostDateType($postDateType)
                                                ->setPostDocument($postDocument);
                            $em->persist($postDateHistoric);
                            $em->flush();
                            }else{
                                break;
                            }
                    }
                }
                
                return $this->redirectToRoute("show_post", [
                    'uniquekey' => $uniquekey
                ]);
            }

        }else{
            $message = $this->translator->trans('message.post.notAuthor');
            $this->addFlash('echec', $message);
        }

        if($choice === 'update'){
            $title = $this->translator->trans('template.UpdateAdvancement.titleUpdate');
        }elseif($choice === 'close'){
            $title = $this->translator->trans('template.UpdateAdvancement.titleClose');
        }
        

        return $this->render('post/update_advancement.html.twig', [
            'userInfo' => $this->getUser(),
            'form' => $form->createView(),
            'title' =>$title
        ]);

    }



}