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
use App\DataProvider\Entity\GlConnectSsoProvider;

/**
 * SSO manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SsoManager
{
    private $ssoServices;
    private $ssoServicesActive;
    private $baseSiteUri;

    private const SUPPORTED_PROVIDERS = [
        "GLConnect" => GlConnectSsoProvider::class
    ];

    public function __construct(array $ssoServices, bool $ssoServicesActive, string $baseSiteUri)
    {
        $this->ssoServices = $ssoServices;
        $this->ssoServicesActive = $ssoServicesActive;
        $this->baseSiteUri = $baseSiteUri;
    }

    
    /**
     * Return instanciated SSoProvider if supported
     * @var string $serviceName Name of the SSO Service
     * @var array $serviceName  Service parameters given by sso.json
     */
    private function getSsoProvider(string $serviceName, array $params)
    {
        if ($this->ssoServicesActive && isset(self::SUPPORTED_PROVIDERS[$serviceName])) {
            $providerClass = self::SUPPORTED_PROVIDERS[$serviceName];
            return new $providerClass($this->baseSiteUri, $params['baseUri'], $params['clientId'], $params['clientSecret'], SsoConnection::RETURN_URL);
        }
        return null;
    }
    
    /**
     * Get all Sso connection services active on this instance
     *
     * @return SsoConnection[]
     */
    public function getSsoConnectionServices(): array
    {
        $ssoServices = [];
        foreach ($this->ssoServices as $serviceName => $ssoService) {
            $provider = $this->getSsoProvider($serviceName, $ssoService);
            if (!is_null($provider)) {
                $ssoConnection = new SsoConnection($serviceName);
                $ssoConnection->setUri($provider->getConnectFormUrl());
                $ssoConnection->setClientId($ssoService['clientId']);
                $ssoConnection->setService($ssoService['name']);
                $ssoServices[] = $ssoConnection;
            }
        }
        return $ssoServices;
    }

    public function getUserProfile()
    {
    }
}
