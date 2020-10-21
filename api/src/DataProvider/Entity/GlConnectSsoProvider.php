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

namespace App\DataProvider\Entity;

use App\User\Entity\User;
use App\User\Interfaces\SsoProviderInterface;
use App\User\Ressource\SsoConnection;

/**
 * Grand Lyon Connect SSO Provider
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class GlConnectSsoProvider implements SsoProviderInterface
{
    const AUTHORIZATION_URL = "idp/oidc/authorize/?client_id={CLIENT_ID}&scope=openid profile email&response_type=code&redirect_uri={REDIRECT_URI}";

    private $baseUri;
    private $clientId;
    private $clientSecret;
    private $redirectUrl;
    private $private;
    private $baseSiteUri;

    public function __construct(string $baseSiteUri, string $baseUri, string $clientId, string $clientSecret, string $redirectUrl)
    {
        $this->baseUri = $baseUri;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
        $this->baseSiteUri = $baseSiteUri;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectFormUrl(): string
    {
        return $this->baseUri."".str_replace(
            "{CLIENT_ID}",
            $this->clientId,
            str_replace("{REDIRECT_URI}", $this->baseSiteUri.$this->redirectUrl, self::AUTHORIZATION_URL)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUserFromSso(): User
    {
        $user = new User();
        
        return $user;
    }
}
