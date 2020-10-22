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
use App\DataProvider\Service\DataProvider;
use App\User\Entity\SsoUser;

/**
 * Grand Lyon Connect SSO Provider
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class GlConnectSsoProvider implements SsoProviderInterface
{
    const SSO_PROVIDER = 'GLConnect';
    const AUTHORIZATION_URL = "idp/oidc/authorize/?client_id={CLIENT_ID}&scope=openid profile email&response_type=code&state=".self::SSO_PROVIDER."&redirect_uri={REDIRECT_URI}";
    const TOKEN_URL = "idp/oidc/token/";
    const USERINFOS_URL = "idp/oidc/user_info";

    private $baseUri;
    private $clientId;
    private $clientSecret;
    private $redirectUrl;
    private $redirectUri;
    private $private;
    private $baseSiteUri;
    
    private $code;

    public function __construct(string $baseSiteUri, string $baseUri, string $clientId, string $clientSecret, string $redirectUrl)
    {
        $this->baseUri = $baseUri;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
        $this->baseSiteUri = $baseSiteUri;
        $this->redirectUri = $this->baseSiteUri."/".$this->redirectUrl;
    }

    public function setCode(string $code)
    {
        $this->code = $code;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getConnectFormUrl(): string
    {
        return $this->baseUri."".str_replace(
            "{CLIENT_ID}",
            $this->clientId,
            str_replace("{REDIRECT_URI}", $this->redirectUri, self::AUTHORIZATION_URL)
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile(string $code): SsoUser
    {
        $token = $this->getToken($code);

        $dataProvider = new DataProvider($this->baseUri, self::USERINFOS_URL);
        $headers = [
            "Authorization" => "Bearer ".$token
        ];
        
        $response = $dataProvider->getCollection(null, $headers);
        
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
            $ssoUser = new SsoUser();
            $ssoUser->setSub($data['sub']);
            $ssoUser->setEmail($data['email']);
            $ssoUser->setFirstname($data['first_name']);
            $ssoUser->setLastname($data['last_name']);
            $ssoUser->setProvider(self::SSO_PROVIDER);
            $ssoUser->setGender($data['gender']);
            $ssoUser->setBirthdate($data['birthdate']);
            return $ssoUser;
        } else {
            throw new \LogicException("Error get Token");
        }
    }

    private function getToken($code)
    {
        $body = [
            "grant_type" => "authorization_code",
            "code" => $code,
            "redirect_uri" => $this->redirectUri
        ];

        $dataProvider = new DataProvider($this->baseUri, self::TOKEN_URL);

        $response = $dataProvider->postCollection($body, null, null, DataProvider::BODY_TYPE_FORM_PARAMS, [$this->clientId,$this->clientSecret]);
        
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
            return $data["access_token"];
        } else {
            throw new \LogicException("Error get Token");
        }
    }
}
