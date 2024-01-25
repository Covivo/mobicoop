<?php

namespace App\DataProvider\Entity\MobConnect\AuthenticationProvider;

use App\DataProvider\Entity\MobConnect\OpenIdSsoProvider;
use App\Incentive\Entity\MobConnectAuth;
use App\Incentive\Interfaces\EecProviderInterface;
use App\Incentive\Service\MobConnectMessages;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;

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
     * @return bool|string
     */
    public function getToken(?User $user)
    {
        $this->_user = $user;

        $this->_mobConnectAuth = $this->_user->getMobConnectAuth();

        if (is_null($this->_mobConnectAuth)) {
            throw new \LogicException(MobConnectMessages::USER_AUTHENTICATION_MISSING);
        }

        if ($this->_mobConnectAuth->hasAuthenticationExpired()) {
            throw new \LogicException(MobConnectMessages::USER_AUTHENTICATION_EXPIRED);
        }

        if ($this->_mobConnectAuth->hasAccessTokenExpired()) {
            return $this->_refreshToken();
        }

        return $this->_mobConnectAuth->getAccessToken();
    }

    /**
     * @return bool|string
     */
    private function _refreshToken()
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
            return false;
        }

        $this->_mobConnectAuth->updateTokens(json_decode($this->response->getContent(), JSON_OBJECT_AS_ARRAY));

        return $this->_mobConnectAuth->getAccessToken();
    }
}
