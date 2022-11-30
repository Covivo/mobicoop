<?php

namespace App\DataProvider\Entity\MobConnect;

use App\DataProvider\Entity\MobConnect\Response\MobConnectAuthResponse;
use App\DataProvider\Ressource\MobConnectAuthParams;
use App\User\Entity\User;

/**
 * MobConnect authentication provider.
 *
 * @author Olivier FILLOL <olivier.fillol@mobicoop.org>
 */
class MobConnectAuthProvider extends MobConnectProvider
{
    private const ROUTE_CODE = '/auth/realms/mcm/protocol/openid-connect/auth';
    private const ROUTE_TOKEN = '/auth/realms/mcm/protocol/openid-connect/token';

    private const GRANT_TYPE_CODE = 'authorization_code';
    private const GRANT_TYPE_REFRESH = 'refresh_token';
    private const HEADER_CONTENT_TYPE = 'application/x-www-form-urlencoded';
    private const HEADER_ACCEPT = '*/*';
    private const PARAM_SCOPE = 'offline_access';

    /**
     * @var MobConnectAuthParams
     */
    private $_authParams;

    public function __construct(MobConnectAuthParams $authParams, User $user)
    {
        $this->_authParams = $authParams;
        $this->_user = $user;

        $this->_apiUri = $this->_authParams->getBaseUri();
    }

    public function getJWTToken(string $authorizationCode, ?string $refreshToken = null): MobConnectAuthResponse
    {
        $headers = [
            'Content-Type' => self::HEADER_CONTENT_TYPE,
            'Accept' => self::HEADER_ACCEPT,
        ];

        $params = [
            'scope' => self::PARAM_SCOPE,
        ];

        $body = [
            'client_id' => $this->_authParams->getClientId(),
            'grant_type' => is_null($refreshToken) ? self::GRANT_TYPE_CODE : self::GRANT_TYPE_REFRESH,
        ];

        if (is_null($refreshToken)) {
            $body['code'] = $authorizationCode;
        } else {
            $body['refresh_token'] = $refreshToken;
        }

        $this->_createDataProvider(self::ROUTE_TOKEN);

        return new MobConnectAuthResponse($this->_getResponse($this->_dataProvider->postCollection($body, $headers, $params, $this->_dataProvider::BODY_TYPE_FORM_PARAMS)));
    }
}
