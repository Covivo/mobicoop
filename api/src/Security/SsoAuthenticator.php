<?php

namespace App\Security;

use App\User\Service\SsoManager;
use Gesdinet\JWTRefreshTokenBundle\Model\RefreshTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class SsoAuthenticator extends AbstractAuthenticator
{
    private $jwtTokenManagerInterface;
    private $refreshTokenManager;
    private $params;
    private $ssoManager;

    public function __construct(JWTTokenManagerInterface $jwtTokenManagerInterface, RefreshTokenManagerInterface $refreshTokenManager, ParameterBagInterface $params, SsoManager $ssoManager)
    {
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
    public function supports(Request $request): bool
    {
        return count($this->getCredentials($request)) > 0;
    }

    public function authenticate(Request $request): Passport
    {
        $credentials = $this->getCredentials($request);

        if ($user = $this->ssoManager->getUser($credentials['ssoProvider'], $credentials['ssoId'], $credentials['baseSiteUri'])) {
            return new SelfValidatingPassport(new UserBadge($user->getUsername()), []);
        }

        throw new CustomUserMessageAuthenticationException('Wrong email or password token');
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey): ?Response
    {
        $user = $token->getUser();

        //Time for valid refresh token, define in gesdinet_jwt_refresh_token, careful to let this value in secondes
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
        if (
            isset($decodeRequest->ssoId) && !empty($decodeRequest->ssoId)
            && isset($decodeRequest->ssoProvider) && !empty($decodeRequest->ssoProvider)
            && isset($decodeRequest->baseSiteUri) && !empty($decodeRequest->baseSiteUri)
        ) {
            return [
                'ssoId' => $decodeRequest->ssoId,
                'ssoProvider' => $decodeRequest->ssoProvider,
                'baseSiteUri' => $decodeRequest->baseSiteUri,
            ];
        }

        return [];
    }
}
