<?php

namespace App\Incentive\Entity;

class Token
{
    /**
     * @var string
     */
    private $_timestampToken;

    /**
     * @var \DateTime
     */
    private $_signingTime;

    public function __construct(string $mobConnectToken, string $mobConnectSigninTime)
    {
        $this->_timestampToken = $mobConnectToken;
        $this->_signingTime = new \DateTime($mobConnectSigninTime);
    }

    /**
     * Get the value of _timestampToken.
     */
    public function getTimestampToken(): string
    {
        return $this->_timestampToken;
    }

    /**
     * Get the value of _signingTime.
     */
    public function getSigningTime(): \DateTime
    {
        return $this->_signingTime;
    }
}
