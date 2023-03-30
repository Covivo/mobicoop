<?php

namespace App\DataProvider\Entity\MobConnect;

use App\DataProvider\Entity\OpenIdSsoProvider as EntityOpenIdSsoProvider;
use App\DataProvider\Service\DataProvider;
use App\User\Entity\SsoUser;
use App\User\Entity\User;

/**
 * @author Olivier Fillol <olivier.fillol@mobicoop.org>
 */
class OpenIdSsoProvider extends EntityOpenIdSsoProvider
{
    /**
     * @var null|string
     */
    protected $_appClientID;

    /**
     * @var null|string
     */
    protected $_appClientSecret;

    public function __construct(
        string $serviceName,
        string $baseSiteUri,
        string $baseUri,
        string $clientId,
        string $clientSecret,
        string $redirectUrl,
        bool $autoCreateAccount,
        string $logOutRedirectUri = '',
        ?string $codeVerifier = null,
        ?string $appClientID = null,
        ?string $appClientSecret = null
    ) {
        parent::__construct($serviceName, $baseSiteUri, $baseUri, $clientId, $clientSecret, $redirectUrl, $autoCreateAccount, $logOutRedirectUri, $codeVerifier);

        $this->_appClientID = $appClientID;
        $this->_appClientSecret = $appClientSecret;
    }

    /**
     * {@inheritdoc}
     */
    public function getUserProfile(string $code): SsoUser
    {
        $tokens = $this->getToken($code);

        if (!is_null($tokens) && is_array($tokens) && isset($tokens['access_token'])) {
            $data = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $tokens['access_token'])[1]))), true);

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

        throw new \LogicException('Error getUserProfile');
    }

    public function getAppToken()
    {
        return $this->execute([
            'client_id' => $this->_appClientID,
            'client_secret' => $this->_appClientSecret,
            'grant_type' => 'client_credentials',
        ]);
    }

    public function getRefreshToken(string $refreshToken)
    {
        return $this->execute([
            'grant_type' => 'refresh_token',
            'scope' => 'offline_access',
            'refresh_token' => $refreshToken,
            'code_verifier' => $this->codeVerifier,
        ]);
    }

    protected function getToken($code)
    {
        return $this->execute([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'offline_access',
            'code_verifier' => $this->codeVerifier,
        ]);
    }

    private function execute(array $body)
    {
        $dataProvider = new DataProvider($this->baseUri, self::URLS[$this->serviceName][self::TOKEN_URL]);

        $response = $dataProvider->postCollection($body, null, null, DataProvider::BODY_TYPE_FORM_PARAMS, [$this->clientId, $this->clientSecret]);

        if (200 == $response->getCode()) {
            return json_decode($response->getValue(), true);
        }

        throw new \LogicException('Error get Token');
    }
}
