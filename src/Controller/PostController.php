<?php


namespace App\Controller;
use App\Entity\{Post, Transaction};
use App\Form\{CommentFormType, PostFormType, PaymentType};
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
    public function homepage(EntityManagerInterface $em){

        $repository = $em->getRepository(Post::class);
        $post = $repository->findPostByNewest();
        $userInfo = $this->getUser();
       // dump($post);die;
        return $this->render('homepage.html.twig',[
                'post' => $post,
                'userInfo'=> $userInfo
            ]
        );
    }

    /**
     * @Route("post/{uniquekey}", name="show_post")
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

        $totalAmount = $transactionRepository->getTotalAmountbyPost($postInfo->getId());
        $TransactionThisPost = $transactionRepository->getTransactionbyPost($postInfo->getId());
       // dd($TransactionThisPost);

        $TransactionAnonymous = $transactionRepository->getAnonymousTransactionbyPost($postInfo->getId());
     //  dd($TransactionThisPost);

        $currentUserLooged = $this->security->getUser();

       // dd($currentUserLooged);
      // var_dump($comment);die;

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

        return $this->render('post/show_post.html.twig',[
                'postInfo' => $postInfo,
                'comment'=> $comment,
                'commentForm' => $form->createView(),
                'userInfo' => $currentUserLooged,
                'nb_participant' => $nb_participant,
                'totalAmount' => $totalAmount,
                'TransactionThisPost' => $TransactionThisPost,
                'TotalAnonymous' => $TransactionAnonymous,
                'financeForm' => $this->createForm(PaymentType::class)->createView()
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
                $newFilename = $uploadService->UploadPostImage($uploadedFile);
                $createNew->setImageFilename($newFilename);
            }

            $em->persist($createNew);
          //  dd($createNew);
            $em->flush();

            $this->addFlash('success', 'Cảm ơn bạn đã tạo chủ để này');

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
     * @Route("edit/post/{id}")
     * @IsGranted("ROLE_USER")
     */
    public function edit(EntityManagerInterface $em, Request $request, Post $post, UploadService $uploadService){
        $form = $this->createForm(PostFormType::class, $post);
        $form->handleRequest($request);
        $user = $this->getUser();

        if($form->isSubmitted() && $form->isValid()){
            $updatePost = $form->getData();
            $id_post = $updatePost->getId();

            $uploadedFile = $form['imageFile']->getData();

            if ($uploadedFile) {
                $newFilename = $uploadService->UploadPostImage($uploadedFile);
                $updatePost->setImageFilename($newFilename);
            }

            $em->persist($updatePost);
            $em->flush();
            $this->addFlash('success', 'Sửa đổi thành công');
            $repo = $em->getRepository(Post::class);
            $postUpdated = $repo->findOneBy([
                'id' => $id_post,
            ]);
            $key = $postUpdated->getUniquekey();

            return $this->redirectToRoute('show_post',[
                'uniquekey' => $key,
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


        return $this->render('post/funding_step_1.html.twig', [
            'userInfo' => $user,
            'financeForm' => $financeForm->createView(),
            'postInfo' => $postInfo
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
            $amount = $financeForm->getData()['amount'];

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
            'postInfo' => $postInfo
        ]);
    }


    /**
     * @Route("/add_transaction/{uniquekey}/{clientSecret}/{amount}/{anonyme}", methods="POST", name="app_add_transaction")
     */
    public function addTransactioninDB($uniquekey, $clientSecret, $amount, $anonyme, EntityManagerInterface $em, Mailer $mailer){

        $repo = $em->getRepository(Post::class);
        $post = $repo->findOneBy([
           'uniquekey' => $uniquekey
        ]);


        $transaction = new Transaction();

        $transaction->setUser($this->getUser())
                    ->setPost($post)
                    ->setAmount($amount)
                    ->setClientSecret($clientSecret)
                    ->setTransfertAt(new \DateTime('now'))
                    ->setAnonymousDonation($anonyme);

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

    }


}