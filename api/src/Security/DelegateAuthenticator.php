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
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;

/**
 * Authenticator for login delegation.
 */
class DelegateAuthenticator extends AbstractAuthenticator
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
    public function supports(Request $request): bool
    {
        return count($this->getCredentials($request)) > 0;
    }

    public function authenticate(Request $request): Passport
    {
        $credentials = $this->getCredentials($request);

        if ($user = $this->em->getRepository(User::class)->findOneBy(['email' => $credentials['username']])) {
            // check if user has the right authorization
            if (!$this->authManager->isInnerAuthorized($user, 'login_delegate')) {
                throw new CustomUserMessageAuthenticationException('No login delegation');
            }
            $userDelegate = $this->em->getRepository(User::class)->findOneBy(['email' => $credentials['username_delegate']]);
            if (!$userDelegate) {
                throw new CustomUserMessageAuthenticationException('Unknown user');
            }

            return new Passport(new UserBadge($user->getEmail()), new PasswordCredentials($credentials['password']));
        }

        throw new CustomUserMessageAuthenticationException('Unknown user');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
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

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    private function getCredentials(Request $request): array
    {
        $decodeRequest = json_decode($request->getContent());
        if (isset($decodeRequest->username, $decodeRequest->username_delegate, $decodeRequest->password)) {
            return [
                'username' => $decodeRequest->username,
                'username_delegate' => $decodeRequest->username_delegate,
                'password' => $decodeRequest->password,
            ];
        }

        return [];
    }
}
