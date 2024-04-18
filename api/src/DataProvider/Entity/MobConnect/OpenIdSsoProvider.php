<?php

namespace App\DataProvider\Entity\MobConnect;

use App\DataProvider\Entity\MobConnect\Converters\ResponseConverter;
use App\DataProvider\Entity\OpenIdSsoProvider as EntityOpenIdSsoProvider;
use App\DataProvider\Service\DataProvider;
use App\User\Entity\SsoUser;
use App\User\Entity\User;
use Symfony\Component\HttpFoundation\Response;

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

    public function getUserProfile(string $code): SsoUser
    {
        $response = $this->getToken($code);
        $content = json_decode($response->getContent());

        if (Response::HTTP_OK != $response->getStatusCode()) {
            throw new \LogicException('eec_user_sso_request_error');
        }

        if (
            !is_null($content)
            && property_exists($content, 'access_token')
        ) {
            $data = json_decode(base64_decode(str_replace('_', '/', str_replace('-', '+', explode('.', $content->access_token)[1]))), true);

            if (!array_key_exists('identity_provider', $data) || 'franceconnect-particulier' !== $data['identity_provider']) {
                throw new \LogicException('eec_user_not_france_connected');
            }

            $ssoUser = new SsoUser();
            $ssoUser->setSub((isset($data['sub'])) ? $data['sub'] : null);
            $ssoUser->setEmail((isset($data['email'])) ? $data['email'] : null);
            $ssoUser->setFirstname((isset($data['first_name'])) ? $data['first_name'] : ((isset($data['given_name'])) ? $data['given_name'] : null));
            $ssoUser->setLastname((isset($data['last_name'])) ? $data['last_name'] : ((isset($data['family_name'])) ? $data['family_name'] : null));
            $ssoUser->setProvider($this->serviceName);
            $ssoUser->setGender((isset($data['gender'])) ? $data['gender'] : User::GENDER_OTHER);
            $ssoUser->setBirthdate((isset($data['birthdate'])) ? $data['birthdate'] : null);
            $ssoUser->setAutoCreateAccount($this->autoCreateAccount);

            $ssoUser->setAccessToken($content->access_token);
            $ssoUser->setAccessTokenExpiresDuration($content->expires_in);
            $ssoUser->setRefreshToken($content->refresh_token);
            $ssoUser->setRefreshTokenExpiresDuration($content->refresh_expires_in);

            if (
                $this->autoCreateAccount
                && (is_null($ssoUser->getFirstname())
                || is_null($ssoUser->getLastname())
                || is_null($ssoUser->getEmail()))
            ) {
                throw new \LogicException('eec_user_sso_account_incomplete');
            }

            return $ssoUser;
        }

        throw new \LogicException('eec_user_sso_unknowned');
    }

    public function getAppToken(): Response
    {
        return $this->execute([
            'client_id' => $this->_appClientID,
            'client_secret' => $this->_appClientSecret,
            'grant_type' => 'client_credentials',
        ]);
    }

    public function getRefreshToken(string $refreshToken): Response
    {
        return $this->execute([
            'grant_type' => 'refresh_token',
            'scope' => 'offline_access',
            'refresh_token' => $refreshToken,
            'code_verifier' => $this->codeVerifier,
        ]);
    }

    protected function getToken($code): Response
    {
        return $this->execute([
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'scope' => 'offline_access',
            'code_verifier' => $this->codeVerifier,
        ]);
    }

    private function execute(array $body): Response
    {
        $dataProvider = new DataProvider($this->baseUri, self::URLS[$this->serviceName][self::TOKEN_URL]);

        return ResponseConverter::convertResponseToHttpFondationResponse($dataProvider->postCollection($body, null, null, DataProvider::BODY_TYPE_FORM_PARAMS, [$this->clientId, $this->clientSecret]));
    }
}
