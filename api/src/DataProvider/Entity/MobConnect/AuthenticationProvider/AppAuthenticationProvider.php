<?php

namespace App\DataProvider\Entity\MobConnect\AuthenticationProvider;

use App\DataProvider\Entity\MobConnect\OpenIdSsoProvider;
use App\Incentive\Interfaces\EecProviderInterface;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class AppAuthenticationProvider extends AuthenticationProvider
{
    public function __construct(EecProviderInterface $provider)
    {
        $this->_provider = $provider;
    }

    /**
     * @return bool|string
     */
    public function getToken(?User $user)
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
            $this->_provider->getCodeVerifier(),
            $this->_provider->getAppId(),
            $this->_provider->getAppSecret()
        );

        $this->response = $provider->getAppToken();

        if (Response::HTTP_OK != $this->response->getStatusCode()) {
            throw new HttpException($this->response->getStatusCode(), $this->response->getContent());
        }

        return json_decode($this->response->getContent())->access_token;
    }
}
