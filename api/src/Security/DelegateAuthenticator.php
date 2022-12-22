<?php

namespace App\Security;

use App\Auth\Service\AuthManager;
use App\User\Entity\User;
use App\User\Event\LoginDelegateEvent;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Authenticator for login delegation.
 */
class DelegateAuthenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $jwtTokenManagerInterface;
    private $refreshTokenManager;
    private $params;
    private $userManager;
    private $authManager;
    private $eventDispatcher;

    public function __construct(EntityManagerInterface $em, JWTTokenManagerInterface $jwtTokenManagerInterface, RefreshTokenManagerInterface $refreshTokenManager, ParameterBagInterface $params, UserManager $userManager, AuthManager $authManager, EventDispatcherInterface $eventDispatcher)
    {
        $this->em = $em;
        $this->jwtTokenManagerInterface = $jwtTokenManagerInterface;
        $this->refreshTokenManager = $refreshTokenManager;
        $this->params = $params;
        $this->userManager = $userManager;
        $this->authManager = $authManager;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request)
    {
        return true;
    }

    /**
     * Called on every request. Return whatever credentials you want to
     * be passed to getUser() as $credentials.
     */
    public function getCredentials(Request $request)
    {
        $decodeRequest = json_decode($request->getContent());
        if (!isset($decodeRequest->username) || !isset($decodeRequest->username_delegate) || !isset($decodeRequest->password)) {
            return false;
        }
        $credentials['username'] = $decodeRequest->username;
        $credentials['username_delegate'] = $decodeRequest->username_delegate;
        $credentials['password'] = $decodeRequest->password;

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (null === $credentials) {
            // Code 401 "Unauthorized"
            return null;
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $credentials['username']]);
        if (!$user) {
            return null;
        }
        // check if user has the right authorization
        if (!$this->authManager->isInnerAuthorized($user, 'login_delegate')) {
            return null;
        }
        $userDelegate = $this->em->getRepository(User::class)->findOneBy(['email' => $credentials['username_delegate']]);
        if (!$userDelegate) {
            return null;
        }

        // if a User is returned, checkCredentials() is called
        return $user;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->userManager->isValidPassword($user, $credentials['password']);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $decodeRequest = json_decode($request->getContent());
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $decodeRequest->username]);
        $userDelegate = $this->em->getRepository(User::class)->findOneBy(['email' => $decodeRequest->username_delegate]);

        // time for valid refresh token, define in gesdinet_jwt_refresh_token, careful to let this value in seconds
        $addTime = 'PT'.$this->params->get('gesdinet_jwt_refresh_token.ttl').'S';

        $now = new \DateTime('now');
        $now->add(new \DateInterval($addTime));
        $refreshToken = $this->refreshTokenManager->create();
        $refreshToken->setUsername($userDelegate->getRefresh());
        $refreshToken->setRefreshToken();
        $refreshToken->setValid($now);

        $this->refreshTokenManager->save($refreshToken);
        // send login delegation event
        $event = new LoginDelegateEvent($user, $userDelegate);
        $this->eventDispatcher->dispatch($event, LoginDelegateEvent::NAME);

        return new JsonResponse([
            'token' => $this->jwtTokenManagerInterface->create($userDelegate),
            'refreshToken' => $refreshToken->getRefreshToken(),
        ]);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Called when authentication is needed, but it's not sent.
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            'message' => 'Authentication Required',
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
