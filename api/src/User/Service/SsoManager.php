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
 */

namespace App\User\Service;

use App\DataProvider\Entity\MobConnect\OpenIdSsoProvider as MobConnectOpenIdSsoProvider;
use App\DataProvider\Entity\OpenIdSsoProvider;
use App\User\Entity\SsoUser;
use App\User\Entity\User;
use App\User\Ressource\SsoConnection;
use Psr\Log\LoggerInterface;

/**
 * SSO manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SsoManager
{
    public const DEFAULT_RESPONSE_MODE = 'query';
    public const DEFAULT_RESPONSE_TYPE = 'code';
    private const SUPPORTED_PROVIDERS = [
        OpenIdSsoProvider::SSO_PROVIDER_GLCONNECT => OpenIdSsoProvider::class,
        OpenIdSsoProvider::SSO_PROVIDER_PASSMOBILITE => OpenIdSsoProvider::class,
        OpenIdSsoProvider::SSO_PROVIDER_MOBCONNECT => MobConnectOpenIdSsoProvider::class,
        OpenIdSsoProvider::SSO_PROVIDER_MOBCONNECTAUTH => MobConnectOpenIdSsoProvider::class,
        OpenIdSsoProvider::SSO_PROVIDER_MOBCONNECTBASIC => MobConnectOpenIdSsoProvider::class,
        OpenIdSsoProvider::SSO_PROVIDER_MOBIGO => OpenIdSsoProvider::class,
    ];

    private $userManager;
    private $ssoServices;
    private $ssoServicesActive;
    private $ssoUseButtonIcon;
    private $logger;

    public function __construct(UserManager $userManager, array $ssoServices, bool $ssoServicesActive, bool $ssoUseButtonIcon, LoggerInterface $logger)
    {
        $this->userManager = $userManager;
        $this->ssoServices = $ssoServices;
        $this->ssoServicesActive = $ssoServicesActive;
        $this->ssoUseButtonIcon = $ssoUseButtonIcon;
        $this->logger = $logger;
    }

    /**
     * Get all Sso connection services active on this instance.
     *
     * @param string      $baseSiteUri Url of the calling website
     * @param null|string $serviceId   Id of the SSO Service to filter on a specific one
     *
     * @return SsoConnection[]
     */
    public function getSsoConnectionServices(string $baseSiteUri, ?string $serviceId, ?string $redirectUri = null): array
    {
        $ssoServices = [];
        if ($this->ssoServicesActive) {
            foreach ($this->ssoServices as $serviceName => $ssoService) {
                $provider = null;
                if (is_null($serviceId) || $serviceId == $serviceName) {
                    $provider = $this->getSsoProvider($serviceName, $baseSiteUri, $redirectUri);
                }

                if (!is_null($provider)) {
                    $ssoConnection = new SsoConnection($serviceName);
                    $ssoConnection->setUri($provider->getConnectFormUrl());
                    $ssoConnection->setClientId($ssoService['clientId']);
                    $ssoConnection->setService($ssoService['name']);
                    $ssoConnection->setSsoProvider($serviceName);
                    $ssoConnection->setUseButtonIcon($this->ssoUseButtonIcon);
                    $ssoConnection->setExternalAccountDeletion($ssoService['externalAccountDeletion']);
                    $ssoServices[] = $ssoConnection;
                }
            }
        }

        return $ssoServices;
    }

    public function getSsoUserProfile(string $serviceName, string $code, string $baseSiteUri, ?string $redirectUri = null): SsoUser
    {
        $provider = $this->getSsoProvider($serviceName, $baseSiteUri, $redirectUri);
        $provider->setCode($code);

        return $provider->getUserProfile($code);
    }

    /**
     * Get a User from an SSO connection (existing or new one).
     *
     * @param string $serviceName Service name (key in sso.json)
     * @param string $code        Authentification code from SSO service
     * @param string $baseSiteUri Url of the calling website
     */
    public function getUser(string $serviceName, string $code, string $baseSiteUri): User
    {
        $ssoUser = $this->getSsoUserProfile($serviceName, $code, $baseSiteUri);

        return $this->userManager->getUserFromSso($ssoUser);
    }

    /**
     * Get the logout routes of the Sso Services.
     */
    public function logoutSso(): array
    {
        if ($this->ssoServicesActive) {
            $logoutUrls = [];
            foreach ($this->ssoServices as $serviceName => $ssoService) {
                $provider = $this->getSsoProvider($serviceName);
                if (!is_null($provider)) {
                    $logOutUrl = $provider->getLogoutUrl();
                    if (!is_null($logOutUrl)) {
                        $logoutUrls[$serviceName] = $logOutUrl;
                    }
                }
            }

            return count($logoutUrls) > 0 ? [$logoutUrls] : [];
        }

        return [];
    }

    /**
     * Get the logout route of a User.
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

    /**
     * Return instanciated SSoProvider if supported.
     *
     * @var string Name of the SSO Service
     *
     * @param string $baseSiteUri Url of the calling website
     */
    private function getSsoProvider(string $serviceName, string $baseSiteUri = '', ?string $redirectUri = null)
    {
        if (isset(self::SUPPORTED_PROVIDERS[$serviceName])) {
            $service = $this->ssoServices[$serviceName];
            $providerClass = self::SUPPORTED_PROVIDERS[$serviceName];
            $provider = new $providerClass(
                $serviceName,
                $baseSiteUri,
                $service['baseUri'],
                $service['clientId'],
                $service['clientSecret'],
                !is_null($redirectUri) && !empty(trim($redirectUri)) ? $redirectUri : (isset($service['returnUrl']) ? $service['returnUrl'] : SsoConnection::RETURN_URL),
                $service['autoCreateAccount'],
                $service['logOutRedirectUri'],
                $service['codeVerifier'],
                isset($service['response_mode']) ? $service['response_mode'] : self::DEFAULT_RESPONSE_MODE,
                isset($service['response_type']) ? $service['response_type'] : self::DEFAULT_RESPONSE_TYPE
            );
            $provider->setLogger($this->logger);

            return $provider;
        }

        return null;
    }
}
