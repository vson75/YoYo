<?php

namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\InvalidCsrfTokenException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Guard\Authenticator\AbstractFormLoginAuthenticator;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class LoginFormAuthenticator extends AbstractFormLoginAuthenticator
{
    private $userRepository;
    private $router;
    private $csrfTokenManager;
    private $userPasswordEncoder;

    use TargetPathTrait;

    public function __construct(UserRepository $userRepository,RouterInterface $router, CsrfTokenManagerInterface $csrfTokenManager, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userRepository = $userRepository;
        $this->router = $router;
        $this->csrfTokenManager = $csrfTokenManager;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    public function supports(Request $request)
    {
        // todo this method is called beginning every request
        // die('the support authentificator is here');
        return $request->attributes->get('_route') === 'app_login'
            && $request->isMethod('POST');
    }

    public function getCredentials(Request $request)
    {
        // todo
        //dump($request->request->all());die;
        $credentials = [
            'email' => $request->request->get('email'),
            'password' => $request->request->get('password'),
            'csrf_token' => $request->request->get('_csrf_token'),
        ];
        $request->getSession()->set(
            Security::LAST_USERNAME,
            $credentials['email']
        );
        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        // todo
       // dd($userProvider);
        $token = new CsrfToken('authenticate', $credentials['csrf_token']);
        if (!$this->csrfTokenManager->isTokenValid($token)) {
            throw new InvalidCsrfTokenException();
        }

        $userInfo = $this->userRepository->findOneBy(['email' => $credentials['email']
            ]);

        return $userInfo;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // todo
        $checkCredentials = $this->userPasswordEncoder->isPasswordValid($user, $credentials['password']);

        return $checkCredentials;
    }
/* comment this method because dont know how to use. It seem no impact on the authentification
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        // todo

        $data = [
            // you may want to customize or obfuscate the message first
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())

            // or to translate this message
            // $this->translator->trans($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);

    }
*/
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {

       //dd($this->getTargetPath($request->getSession(), $providerKey));

      // dd($this->getTargetPath($request->getSession(), $providerKey));
        if ($targetPath = $this->getTargetPath($request->getSession(), $providerKey)) {

            return new RedirectResponse($targetPath);
        }
        return new RedirectResponse($this->router->generate('app_homepage'));

    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        // todo
        $url = $this->getLoginUrl();

        return new RedirectResponse($url);
    }

    public function supportsRememberMe()
    {
        // todo
        return true;
    }

    protected function getLoginUrl()
    {
        // TODO: Implement getLoginUrl() method.
        return $this->router->generate('app_login');
    }
}
