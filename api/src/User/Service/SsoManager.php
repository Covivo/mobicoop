<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace App\User\Service;

use App\User\Ressource\SsoConnection;
use LogicException;
use App\DataProvider\Entity\OpenIdSsoProvider;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * SSO manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SsoManager
{
    private $userManager;
    private $ssoServices;
    private $ssoServicesActive;
    private $ssoUseButtonIcon;

    private const SUPPORTED_PROVIDERS = [
        OpenIdSsoProvider::SSO_PROVIDER_GLCONNECT => OpenIdSsoProvider::class,
        OpenIdSsoProvider::SSO_PROVIDER_PASSMOBILITE => OpenIdSsoProvider::class
    ];

    public function __construct(UserManager $userManager, array $ssoServices, bool $ssoServicesActive, bool $ssoUseButtonIcon)
    {
        $this->userManager = $userManager;
        $this->ssoServices = $ssoServices;
        $this->ssoServicesActive = $ssoServicesActive;
        $this->ssoUseButtonIcon = $ssoUseButtonIcon;
    }

    
    /**
     * Return instanciated SSoProvider if supported
     * @var string $serviceName Name of the SSO Service
     * @param string $baseSiteUri   Url of the calling website
     */
    private function getSsoProvider(string $serviceName, string $baseSiteUri = "")
    {
        if (isset(self::SUPPORTED_PROVIDERS[$serviceName])) {
            $service = $this->ssoServices[$serviceName];
            $providerClass = self::SUPPORTED_PROVIDERS[$serviceName];
            return new $providerClass($serviceName, $baseSiteUri, $service['baseUri'], $service['clientId'], $service['clientSecret'], SsoConnection::RETURN_URL, $service['autoCreateAccount']);
        }
        return null;
    }
    
    /**
     * Get all Sso connection services active on this instance
     * @param string $baseSiteUri   Url of the calling website
     * @return SsoConnection[]
     */
    public function getSsoConnectionServices(string $baseSiteUri): array
    {
        $ssoServices = [];
        if ($this->ssoServicesActive) {
            foreach ($this->ssoServices as $serviceName => $ssoService) {
                $provider = $this->getSsoProvider($serviceName, $baseSiteUri);
                if (!is_null($provider)) {
                    $ssoConnection = new SsoConnection($serviceName);
                    $ssoConnection->setUri($provider->getConnectFormUrl());
                    $ssoConnection->setClientId($ssoService['clientId']);
                    $ssoConnection->setService($ssoService['name']);
                    $ssoConnection->setSsoProvider($serviceName);
                    $ssoConnection->setUseButtonIcon($this->ssoUseButtonIcon);
                    $ssoServices[] = $ssoConnection;
                }
            }
        }
        return $ssoServices;
    }

    /**
     * Get a User from an SSO connection (existing or new one)
     *
     * @param string $serviceName   Service name (key in sso.json)
     * @param string $code          Authentification code from SSO service
     * @param string $baseSiteUri   Url of the calling website
     * @return User
     */
    public function getUser(string $serviceName, string $code, string $baseSiteUri): User
    {
        $provider = $this->getSsoProvider($serviceName, $baseSiteUri);
        $provider->setCode($code);
        return $this->userManager->getUserFromSso($provider->getUserProfile($code));
    }

    /**
     * Get the logout routes of the Sso Services
     *
     * @return array
     */
    public function logoutSso(): array
    {
        if ($this->ssoServicesActive) {
            $logoutUrls = [];
            foreach ($this->ssoServices as $serviceName => $ssoService) {
                $provider = $this->getSsoProvider($serviceName);
                if (!is_null($provider)) {
                    $logoutUrls[$serviceName] = $provider->getLogoutUrls();
                }
            }
            return [$logoutUrls];
        }
        return [];
    }

    /**
     * Get the logout route of a User
     *
     * @param User $user
     * @return string|null
     */
    public function getSsoLogoutUrl(User $user): ?string
    {
        foreach ($this->logoutSso() as $logOutUrls) {
            foreach ($logOutUrls as $provider => $logOutUrl) {
                if ($provider == $user->getSsoProvider()) {
                    return $logOutUrl;
                }
            }
        }
        return null;
    }
}
