<?php

namespace App\Security;

use App\User\Entity\User;
use App\User\Service\SsoManager;
use Doctrine\ORM\EntityManagerInterface;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class SsoAuthenticator extends AbstractGuardAuthenticator
{
    private $em;
    private $jwtTokenManagerInterface;
    private $refreshTokenManager;
    private $params;
    private $ssoManager;

    public function __construct(EntityManagerInterface $em, JWTTokenManagerInterface $jwtTokenManagerInterface, RefreshTokenManagerInterface $refreshTokenManager, ParameterBagInterface $params, SsoManager $ssoManager)
    {
        $this->em = $em;
        $this->jwtTokenManagerInterface = $jwtTokenManagerInterface;
        $this->refreshTokenManager = $refreshTokenManager;
        $this->params = $params;
        $this->ssoManager = $ssoManager;
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
        if (
            isset($decodeRequest->ssoId) && !empty($decodeRequest->ssoId)
            && isset($decodeRequest->ssoProvider) && !empty($decodeRequest->ssoProvider)
            && isset($decodeRequest->baseSiteUri) && !empty($decodeRequest->baseSiteUri)
        ) {
            $credentials['ssoId'] = $decodeRequest->ssoId;
            $credentials['ssoProvider'] = $decodeRequest->ssoProvider;
            $credentials['baseSiteUri'] = $decodeRequest->baseSiteUri;
        } else {
            return false;
        }

        return $credentials;
    }

    /**
     * Searching User by ssoId and ssoProvider.
     *
     * @param array $credentials
     *
     * @return null|User
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if (null === $credentials) {
            // The token header was empty, authentication fails with HTTP Status
            // Code 401 "Unauthorized"
            return null;
        }

        return $this->ssoManager->getUser($credentials['ssoProvider'], $credentials['ssoId'], $credentials['baseSiteUri']);
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        // Check credentials - e.g. make sure the password is valid.
        // In case of an API token, no credential check is needed.

        // Return `true` to cause authentication success
        return true;
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $user = $token->getUser();

        // Time for valid refresh token, define in gesdinet_jwt_refresh_token, careful to let this value in secondes
        $addTime = 'PT'.$this->params->get('gesdinet_jwt_refresh_token.ttl').'S';

        $now = new \DateTime('now');
        $now->add(new \DateInterval($addTime));

        $refreshToken = $this->refreshTokenManager->create();
        $refreshToken->setUsername($user->getRefresh());
        $refreshToken->setRefreshToken();
        $refreshToken->setValid($now);

        $this->refreshTokenManager->save($refreshToken);

        // on success, let the request continue
        return new JsonResponse([
            'token' => $this->jwtTokenManagerInterface->create($token->getUser()),
            'refreshToken' => $refreshToken->getRefreshToken(),
            'logoutUrl' => (!is_null($user->getSsoProvider())) ? $this->ssoManager->getSsoLogoutUrl($user) : null,
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
