<?php

namespace App\DataProvider\Entity\MobConnect\AuthenticationProvider;

use App\DataProvider\Entity\MobConnect\OpenIdSsoProvider;
use App\Incentive\Entity\MobConnectAuth;
use App\Incentive\Interfaces\EecProviderInterface;
use App\Incentive\Service\MobConnectMessages;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UserAuthenticationProvider extends AuthenticationProvider
{
    /**
     * @var MobConnectAuth
     */
    private $_mobConnectAuth;

    /**
     * @var User
     */
    private $_user;

    public function __construct(EecProviderInterface $provider)
    {
        $this->_provider = $provider;
    }

    /**
     * @throws HttpException
     */
    public function getToken(?User $user): string
    {
        $this->_user = $user;

        $this->_mobConnectAuth = $this->_user->getMobConnectAuth();

        if (is_null($this->_mobConnectAuth)) {
            throw new HttpException(Response::HTTP_CONFLICT, MobConnectMessages::USER_AUTHENTICATION_MISSING);
        }

        if ($this->_mobConnectAuth->hasAuthenticationExpired()) {
            throw new HttpException(Response::HTTP_CONFLICT, MobConnectMessages::USER_AUTHENTICATION_EXPIRED);
        }

        if ($this->_mobConnectAuth->hasAccessTokenExpired()) {
            return $this->_refreshToken();
        }

        return $this->_mobConnectAuth->getAccessToken();
    }

    /**
     * @throws HttpException
     */
    private function _refreshToken(): string
    {
        $provider = new OpenIdSsoProvider(
            $this->_provider->getName(),
            '',
            $this->_provider->getAuthenticationUri(),
            $this->_provider->getClientId(),
            $this->_provider->getClientSecret(),
            '',
            $this->_provider->getAutoCreateAccount(),
            '',
            null
        );

        $this->response = $provider->getRefreshToken($this->_mobConnectAuth->getRefreshToken());

        if (Response::HTTP_OK != $this->response->getStatusCode()) {
            throw new HttpException($this->response->getStatusCode(), $this->response->getContent());
        }

        $this->_mobConnectAuth->updateTokens(json_decode($this->response->getContent(), JSON_OBJECT_AS_ARRAY));

        return $this->_mobConnectAuth->getAccessToken();
    }
}
