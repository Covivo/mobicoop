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

namespace App\DataProvider\Entity;

use App\DataProvider\Service\DataProvider;
use App\User\Entity\SsoUser;
use App\User\Entity\User;
use App\User\Interfaces\SsoProviderInterface;
use Psr\Log\LoggerInterface;

/**
 * OpenId SSO Provider.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class OpenIdSsoProvider implements SsoProviderInterface
{
    // Supported Providers names
    public const SSO_PROVIDER_GLCONNECT = 'GLConnect';
    public const SSO_PROVIDER_PASSMOBILITE = 'PassMobilite';
    public const SSO_PROVIDER_MOBCONNECT = 'mobConnect';
    public const SSO_PROVIDER_MOBCONNECT_TEST = 'mobConnect-test';

    public const AUTHORIZATION_URL = 'Authorization_Url';
    public const TOKEN_URL = 'Token_Url';
    public const USERINFOS_URL = 'UserInfos_Url';
    public const LOGOUT_URL = 'Logout_Url';

    public const URLS = [
        self::SSO_PROVIDER_GLCONNECT => [
            self::AUTHORIZATION_URL => 'idp/oidc/authorize/?client_id={CLIENT_ID}&scope=openid profile email&response_type=code&state={SERVICE_NAME}&redirect_uri={REDIRECT_URI}',
            self::TOKEN_URL => 'idp/oidc/token/',
            self::USERINFOS_URL => 'idp/oidc/user_info',
        ],
        self::SSO_PROVIDER_PASSMOBILITE => [
            self::AUTHORIZATION_URL => 'auth/realms/Passmobilite/protocol/openid-connect/auth/?client_id={CLIENT_ID}&scope=openid profile email&response_type=code&state={SERVICE_NAME}&redirect_uri={REDIRECT_URI}',
            self::TOKEN_URL => 'auth/realms/Passmobilite/protocol/openid-connect/token/',
            self::USERINFOS_URL => 'auth/realms/Passmobilite/protocol/openid-connect/userinfo',
            self::LOGOUT_URL => 'auth/realms/Passmobilite/protocol/openid-connect/logout?post_logout_redirect_uri={REDIRECT_URI}',
        ],
        self::SSO_PROVIDER_MOBCONNECT_TEST => [
            self::AUTHORIZATION_URL => '/auth/realms/mcm/protocol/openid-connect/auth?client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}&state={SERVICE_NAME}&response_mode=fragment&response_type=code&scope=openid&nonce=21a8befa-b65f-41c5-916b-29c9e8d70177',
            self::TOKEN_URL => 'auth/realms/Passmobilite/protocol/openid-connect/token/',
            self::USERINFOS_URL => 'auth/realms/Passmobilite/protocol/openid-connect/userinfo',
            self::LOGOUT_URL => 'auth/realms/Passmobilite/protocol/openid-connect/logout?post_logout_redirect_uri={REDIRECT_URI}',
        ],
        self::SSO_PROVIDER_MOBCONNECT => [
            self::AUTHORIZATION_URL => '/auth/realms/mcm/protocol/openid-connect/auth?client_id={CLIENT_ID}&redirect_uri={REDIRECT_URI}&state={SERVICE_NAME}&response_mode=fragment&response_type=code&scope=openid&nonce=21a8befa-b65f-41c5-916b-29c9e8d70177',
            self::TOKEN_URL => 'auth/realms/Passmobilite/protocol/openid-connect/token/',
            self::USERINFOS_URL => 'auth/realms/Passmobilite/protocol/openid-connect/userinfo',
            self::LOGOUT_URL => 'auth/realms/Passmobilite/protocol/openid-connect/logout?post_logout_redirect_uri={REDIRECT_URI}',
        ],
    ];

    private $serviceName;
    private $baseUri;
    private $clientId;
    private $clientSecret;
    private $redirectUrl;
    private $redirectUri;
    private $baseSiteUri;
    private $autoCreateAccount;
    private $logOutRedirectUri;

    private $code;
    private $logger;

    public function __construct(string $serviceName, string $baseSiteUri, string $baseUri, string $clientId, string $clientSecret, string $redirectUrl, bool $autoCreateAccount, string $logOutRedirectUri = '')
    {
        if (!isset(self::URLS[$serviceName])) {
            throw new \LogicException('Service unknown');
        }

        $this->serviceName = $serviceName;
        $this->baseUri = $baseUri;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUrl = $redirectUrl;
        $this->baseSiteUri = $baseSiteUri;
        $this->redirectUri = $this->baseSiteUri.'/'.$this->redirectUrl;
        $this->autoCreateAccount = $autoCreateAccount;
        $this->logOutRedirectUri = $logOutRedirectUri;
    }

    public function setCode(string $code)
    {
        $this->code = $code;
    }

    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectFormUrl(): string
    {
        return $this->baseUri.''.str_replace('{CLIENT_ID}', $this->clientId, str_replace(
            '{SERVICE_NAME}',
            $this->serviceName,
            str_replace('{REDIRECT_URI}', $this->redirectUri, self::URLS[$this->serviceName][self::AUTHORIZATION_URL])
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile(string $code): SsoUser
    {
        /** Mock data for dev purpose */
        // $ssoUser = new SsoUser();
        // $ssoUser->setSub('999');
        // $ssoUser->setEmail('tenshikuroi18@yopmail.com');
        // $ssoUser->setFirstname('Johnny');
        // $ssoUser->setLastname('Sso');
        // $ssoUser->setProvider('PassMobilite');
        // $ssoUser->setGender(User::GENDER_MALE);
        // $ssoUser->setBirthdate(null);
        // $ssoUser->setAutoCreateAccount($this->autoCreateAccount);

        // return $ssoUser;
        // end mock data

        $token = $this->getToken($code);

        $dataProvider = new DataProvider($this->baseUri, self::URLS[$this->serviceName][self::USERINFOS_URL]);
        $headers = [
            'Authorization' => 'Bearer '.$token,
        ];

        $response = $dataProvider->getCollection(null, $headers);

        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);
            $ssoUser = new SsoUser();
            $ssoUser->setSub((isset($data['sub'])) ? $data['sub'] : null);
            $ssoUser->setEmail((isset($data['email'])) ? $data['email'] : null);
            $ssoUser->setFirstname((isset($data['first_name'])) ? $data['first_name'] : ((isset($data['given_name'])) ? $data['given_name'] : null));
            $ssoUser->setLastname((isset($data['last_name'])) ? $data['last_name'] : ((isset($data['family_name'])) ? $data['family_name'] : null));
            $ssoUser->setProvider($this->serviceName);
            $ssoUser->setGender((isset($data['gender'])) ? $data['gender'] : User::GENDER_OTHER);
            $ssoUser->setBirthdate((isset($data['birthdate'])) ? $data['birthdate'] : null);
            $ssoUser->setAutoCreateAccount($this->autoCreateAccount);

            if (
                $this->autoCreateAccount
                && (is_null($ssoUser->getFirstname())
                || is_null($ssoUser->getLastname())
                || is_null($ssoUser->getEmail()))
            ) {
                throw new \LogicException('Not enough infos about the User');
            }

            return $ssoUser;
        }

        throw new \LogicException('Error get Token');
    }

    public function getLogoutUrl(): ?string
    {
        $url = null;
        if (isset(self::URLS[$this->serviceName][self::LOGOUT_URL])) {
            $url = $this->baseUri.''.self::URLS[$this->serviceName][self::LOGOUT_URL];
            $url = str_replace('{REDIRECT_URI}', $this->logOutRedirectUri, $url);
        }

        return $url;
    }

    public function getLogoutUrls(): ?string
    {
        return (isset(self::URLS[$this->serviceName][self::LOGOUT_URL])) ? $this->baseUri.''.self::URLS[$this->serviceName][self::LOGOUT_URL] : null;
    }

    private function getToken($code)
    {
        $body = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
        ];

        $dataProvider = new DataProvider($this->baseUri, self::URLS[$this->serviceName][self::TOKEN_URL]);

        $response = $dataProvider->postCollection($body, null, null, DataProvider::BODY_TYPE_FORM_PARAMS, [$this->clientId, $this->clientSecret]);

        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);

            return $data['access_token'];
        }

        throw new \LogicException('Error get Token');
    }
}
