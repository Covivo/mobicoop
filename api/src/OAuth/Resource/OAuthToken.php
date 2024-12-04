<?php

namespace App\OAuth\Resource;

class OAuthToken implements \JsonSerializable
{
    /**
     * @var string
     */
    protected $_token;

    /**
     * @var \DateTime
     */
    protected $_expirationDate;

    /**
     * @var int
     */
    protected $_expiresIn;

    /**
     * @var \DateTime
     */
    private $_createdAt;

    public function __construct(string $token, \DateTime $expirationDate, ?int $expiresIn = null)
    {
        $this->_token = $token;
        $this->_expirationDate = $expirationDate;

        $this->_createdAt = new \DateTime();

        if (!is_null($expiresIn)) {
            $this->_expiresIn = $expiresIn;
        }
    }

    public function getToken(): string
    {
        return $this->_token;
    }

    public function getExpirationDate(): \DateTime
    {
        return $this->_expirationDate;
    }

    public function hasExpired(): bool
    {
        $now = new \DateTime();

        return $now > $this->_expirationDate;
    }

    public function jsonSerialize(): array
    {
        return [
            'created_at' => $this->_createdAt->format('Y-m-d H:i:s'),
            'expires_in' => $this->_expiresIn,
            'expiration_date' => $this->_expirationDate->format('Y-m-d H:i:s'),
            'token' => $this->_token,
        ];
    }

    /**
     * @param int $expiresIn Delay expressed in seconds
     */
    public static function getExpirationDateFromExpiresIn(int $expiresIn): string
    {
        $now = new \DateTime();
        $now->add(new \DateInterval('PT'.$expiresIn.'S'));

        return $now->format('c');
    }
}
