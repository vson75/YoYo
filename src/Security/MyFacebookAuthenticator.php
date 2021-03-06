<?php
namespace App\Security;

use App\Entity\User; // your user entity
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Security\Authenticator\SocialAuthenticator;
use KnpU\OAuth2ClientBundle\Client\Provider\FacebookClient;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\FacebookUser;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;


class MyFacebookAuthenticator extends SocialAuthenticator
{
        private $clientRegistry;
        private $em;
        private $router;
        private $uploadsPath;

        use TargetPathTrait;

        public function __construct(ClientRegistry $clientRegistry, EntityManagerInterface $em, RouterInterface $router, string $uploadsPath)
    {
        $this->clientRegistry = $clientRegistry;
        $this->em = $em;
        $this->router = $router;
        $this->uploadsPath = $uploadsPath;
    }

    public function supports(Request $request)
    {
    // continue ONLY if the current ROUTE matches the check ROUTE
    return $request->attributes->get('_route') === 'connect_facebook_check';
    }

        public function getCredentials(Request $request)
        {
        // this method is only called if supports() returns true

        // For Symfony lower than 3.4 the supports method need to be called manually here:
        // if (!$this->supports($request)) {
        //     return null;
        // }

        return $this->fetchAccessToken($this->getFacebookClient());
        }

        public function getUser($credentials, UserProviderInterface $userProvider)
        {
        /** @var FacebookUser $facebookUser */
        $facebookUser = $this->getFacebookClient()
        ->fetchUserFromToken($credentials);

        $email = $facebookUser->getEmail();

       // dd($facebookUser->getId());
        // 1) have they logged in with Facebook before? Easy!
        $existingUser = $this->em->getRepository(User::class)
        ->findOneBy(['facebookId' => $facebookUser->getId()]);
        if ($existingUser) {
        return $existingUser;
        }else{
            // 3) Maybe you just want to "register" them by creating
            // a User object
            //dd($facebookUser->getId());
            $user = new User();
            $user->setFirstname($facebookUser->getFirstName())
                ->setLastname($facebookUser->getLastName());
            $user->setEmail($facebookUser->getEmail());
            $user->setFacebookId($facebookUser->getId());



            $this->em->persist($user);
            $this->em->flush();

            // get picture facebook content and create directory in user/icon
            // example: https://symfony.com/doc/current/components/filesystem.html

            $facebookID = $user->getFacebookId();
            $image = $facebookUser->getPictureUrl();
            $image = file_get_contents($image);

            $fileSystem = new Filesystem();
            $fileSystem->dumpFile($this->uploadsPath.'/user/icon/'.$user->getId().'/icon_facebook.png',$image);

            //update user created with his facebook image

            $repo = $this->em->getRepository(User::class);
            $user = $repo->findOneBy([
                'facebookId' => $facebookID
            ]);
            $user->setIcon('icon_facebook.png');
            $this->em->persist($user);
            $this->em->flush();

            return $user;
        }


        }

    /**
    * @return FacebookClient
    */
    private function getFacebookClient()
    {
    return $this->clientRegistry->getClient('facebook_main');
    // "facebook_main" is the key used in config/packages/knpu_oauth2_client.yaml
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
    // change "app_homepage" to some route in your app
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {

            return new RedirectResponse($targetPath);
        }
        return new RedirectResponse($this->router->generate('app_homepage'));

    // or, on success, let the request continue to be handled by the controller
    //return null;
      //  dd($targetUrl);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
    $message = strtr($exception->getMessageKey(), $exception->getMessageData());

    return new Response($message, Response::HTTP_FORBIDDEN);
    }

    /**
    * Called when authentication is needed, but it's not sent.
    * This redirects to the 'login'.
    */
    public function start(Request $request, AuthenticationException $authException = null)
    {
    return new RedirectResponse(
    '/connect/', // might be the site, where users choose their oauth provider
    Response::HTTP_TEMPORARY_REDIRECT
    );
    }

    // ...
    }