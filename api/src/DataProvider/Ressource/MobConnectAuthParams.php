<?php

namespace App\DataProvider\Ressource;

class MobConnectAuthParams
{
    /**
     * @var string
     */
    private $_name;

    /**
     * @var string
     */
    private $_baseUri;

    /**
     * @var string
     */
    private $_clientId;

    /**
     * @var string
     */
    private $_clientSecret;

    /**
     * @var bool
     */
    private $_autoCreateAccount = true;

    /**
     * @var string
     */
    private $_logOutRedirectUri;

    public function __construct(array $params)
    {
        foreach ($params as $key => $value) {
            $param = '_'.$key;
            $this->{$param} = $value;
        }
    }

    /**
     * Get the value of _name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get the value of _baseUri.
     *
     * @return string
     */
    public function getBaseUri()
    {
        return $this->_baseUri;
    }

    /**
     * Get the value of _clientId.
     *
     * @return string
     */
    public function getClientId()
    {
        return $this->_clientId;
    }

    /**
     * Get the value of _clientSecret.
     *
     * @return string
     */
    public function getClientSecret()
    {
        return $this->_clientSecret;
    }

    /**
     * Get the value of _autoCreateAccount.
     *
     * @return bool
     */
    public function getAutoCreateAccount()
    {
        return $this->_autoCreateAccount;
    }

    /**
     * Get the value of _logOutRedirectUri.
     *
     * @return string
     */
    public function getLogOutRedirectUri()
    {
        return $this->_logOutRedirectUri;
    }
}
