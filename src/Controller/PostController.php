<?php


namespace App\Controller;


use App\Entity\{AdminParameter,
    DocumentType,
    Favorite,
    Post,
    PostStatus,
    PostTranslation,
    RequestOrganisationDocument,
    RequestOrganisationInfo,
    Transaction,
    User,
    WebsiteLanguage};
use App\Form\{CommentFormType, ExtendPostType, PostFormType, PaymentType, TranslationPostType};
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

        $q = $request->query->get('isFavorite');
      //var_dump($q);
        //show post by newest with the status = Collectinf or filter favorite
        if(is_null($userInfo)){
            $post = $repository->findPostByNewest();
            //show post by newest with status = Finish collect OR Transfering Fund
            $postFinishCollect = $repository->findPostFinishCollect();
        }else{
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


        return $this->render('homepage.html.twig',[
                'post' => $post,
                'postFinishCollect' => $postFinishCollect,
                'status' => $statusArray,
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

        $managementFees = $em->getRepository(AdminParameter::class)->findLastestId();


        return $this->render('post/funding_step_1.html.twig', [
            'userInfo' => $user,
            'financeForm' => $financeForm->createView(),
            'postInfo' => $postInfo,
            'ManagementFees' => $managementFees->getvariableFees(),
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
            'stripe_pk_key' => $stripe_pk_key
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
     * @Route("/transfert_fund/post/{uniquekey}", name="app_transfert_fund")
     */
    public function transfertFundPost(EntityManagerInterface $em, $uniquekey){
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

            $message = $this->translator->trans('message.post.StartedTransfertFund');
            $this->addFlash("success", $message);
            return $this->redirectToRoute('show_post',['uniquekey'=>$uniquekey]);
        }else{
            $message = $this->translator->trans('message.post.notAuthor');
            $this->addFlash('echec', $message);
            return $this->redirectToRoute('app_homepage');
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


}