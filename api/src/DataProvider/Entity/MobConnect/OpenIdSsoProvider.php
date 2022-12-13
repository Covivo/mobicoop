<?php

namespace App\DataProvider\Entity\MobConnect;

use App\DataProvider\Entity\OpenIdSsoProvider as EntityOpenIdSsoProvider;
use App\DataProvider\Service\DataProvider;
use App\User\Entity\SsoUser;
use App\User\Entity\User;

class OpenIdSsoProvider extends EntityOpenIdSsoProvider
{
    public function __construct(
        string $serviceName,
        string $baseSiteUri,
        string $baseUri,
        string $clientId,
        string $clientSecret,
        string $redirectUrl,
        bool $autoCreateAccount,
        string $logOutRedirectUri = '',
        ?string $codeVerifier = null
    ) {
        parent::__construct($serviceName, $baseSiteUri, $baseUri, $clientId, $clientSecret, $redirectUrl, $autoCreateAccount, $logOutRedirectUri, $codeVerifier);
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

        $tokens = $this->getToken($code);

        $dataProvider = new DataProvider($this->baseUri, self::URLS[$this->serviceName][self::USERINFOS_URL]);
        $headers = [
            'Authorization' => 'Bearer '.$tokens['access_token'],
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

            $ssoUser->setAccessToken($tokens['access_token']);
            $ssoUser->setAccessTokenExpiresDuration($tokens['expires_in']);
            $ssoUser->setRefreshToken($tokens['refresh_token']);
            $ssoUser->setRefreshTokenExpiresDuration($tokens['refresh_expires_in']);

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

    protected function getToken($code)
    {
        $body = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'offline_access',
            'code_verifier' => $this->codeVerifier,
        ];

        $dataProvider = new DataProvider($this->baseUri, self::URLS[$this->serviceName][self::TOKEN_URL]);

        $response = $dataProvider->postCollection($body, null, null, DataProvider::BODY_TYPE_FORM_PARAMS, [$this->clientId, $this->clientSecret]);

        if (200 == $response->getCode()) {
            return json_decode($response->getValue(), true);
        }

        throw new \LogicException('Error get Token');
    }
}
