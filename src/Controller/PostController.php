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
        $transaction = $postInfo->getTransactions();

        $totalAmount = $transactionRepository->getTotalAmountbyPost($postInfo->getId());
        $TransactionThisPost = $transactionRepository->getTransactionbyPost($postInfo->getId());

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
                'transaction' => $transaction,
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

            $template='email/thankToCreatePost.html.twig';
            $subject ="Cam on ban da tao chu de moi";
            $mailer->sendMailCreateOrFinancePost($user,$createNew,$template,$subject);
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
     * @Route("finance/{id}", name="app_finance")
     */
    public function financePost(Post $post, Request $request)
    {
        $financeForm = $this->createForm(PaymentType::class);
        $financeForm->handleRequest($request);

        $user = $this->getUser();

        //dd($financeForm->getData());

        if ($financeForm->isSubmitted() && $financeForm->isValid()) {
            $amount = $financeForm->getData()['amount'];

            Stripe::setApiKey('sk_test_gxLCkDYIJRoJXx7Ovh4RqBTB00aHGuN3mt');
            $intent = PaymentIntent::create([
                'amount'   => $amount*100,
                'currency' => 'eur',
                'metadata' => ['integration_check' => 'accept_a_payment']
            ]);
        } else {
            return $this->redirect('show_post', ['uniqueKey' => $post->getUniquekey()]);
        }
        

        return $this->render('post/finance_post.html.twig',[
            'userInfo'      => $user,
            'clientSecret' => $intent->client_secret,
            'amount'        => $amount
        ]);
    }

    /**
    public function charge(Request $request){
        // Set your secret key. Remember to switch to your live secret key in production!
        // See your keys here: https://dashboard.stripe.com/account/apikeys
       // require_once('vendor/autoload.php');

        $stripe = new \Stripe\StripeClient('sk_test_gxLCkDYIJRoJXx7Ovh4RqBTB00aHGuN3mt');

        $user = $this->getUser();
        \Stripe\Stripe::setApiKey('sk_test_gxLCkDYIJRoJXx7Ovh4RqBTB00aHGuN3mt');
       // dd(\Stripe\PaymentIntent::all());
        // To create a PaymentIntent for confirmation, see our guide at: https://stripe.com/docs/payments/payment-intents/creating-payment-intents#creating-for-automatic



        $customer = \Stripe\Customer::create([
            "email" => $user->getEmail()
            ]
        );


        $intent = \Stripe\PaymentIntent::create([
            'amount' => 1599,
            'currency' => 'eur',
            // Verify your integration in this guide by including this parameter
            'metadata' => ['integration_check' => 'accept_a_payment'],
            'payment_method_types' => ['card'],
            // 'confirm' => true,
            'description'=>'khach tra tiên',

        ]);
        dd($intent);

        $this->addFlash('success','cam on ban da tra tien');
        return $this->render('post/finance_post.html.twig',[
            'userInfo' => $user,
            'payementIntent' => $intent
        ]);

    }
     **/
}