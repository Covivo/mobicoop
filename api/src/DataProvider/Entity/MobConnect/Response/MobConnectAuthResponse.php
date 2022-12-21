<?php

namespace App\DataProvider\Entity\MobConnect\Response;

/**
 * MobConnect authentication Response.
 *
 * @author Olivier Fillol <olivier.fillol@mobicoop.org>
 */
class MobConnectAuthResponse
{
    /**
     * @var string
     */
    private $_accessToken;

    /**
     * @var string
     */
    private $_refreshToken;

    public function __construct(\stdClass $rawResponse)
    {
        $this->_accessToken = $rawResponse->access_token;
        $this->_refreshToken = $rawResponse->refresh_token;
    }

    /**
     * Get the value of accessToken.
     */
    public function getAccessToken(): string
    {
        return $this->_accessToken;
    }

    /**
     * Get the value of refreshToken.
     */
    public function getRefreshToken(): string
    {
        return $this->_refreshToken;
    }
}
