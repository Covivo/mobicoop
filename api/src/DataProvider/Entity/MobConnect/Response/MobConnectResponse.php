<?php

namespace App\DataProvider\Entity\MobConnect\Response;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

abstract class MobConnectResponse implements MobConnectResponseInterface
{
    public const ERROR_CODES = [400, 401, 403, 404, 409, 412, 415, 422, 500, 503];

    /**
     * @var null|int
     */
    protected $_code;

    protected $_content;

    /**
     * @var null|array
     */
    protected $_payload;

    /**
     * The Mob connect timestamp.
     *
     * @var null|string
     */
    protected $_timestamp;

    public function __construct(Response $mobConnectResponse, array $payload = null)
    {
        $this->_code = $mobConnectResponse->getStatusCode();
        $this->_content = is_null(json_decode($mobConnectResponse->getContent())) ? $mobConnectResponse->getContent() : json_decode($mobConnectResponse->getContent());

        $this->_payload = $payload;

        $this->_isResponseError();
    }

    /**
     * Get the value of code.
     */
    public function getCode(): ?int
    {
        return $this->_code;
    }

    /**
     * Get the value of content.
     */
    public function getContent()
    {
        return $this->_content;
    }

    public function getPayload(): ?array
    {
        return $this->_payload;
    }

    /**
     * Get the Mob connect timestamp.
     *
     * @return string
     */
    public function getTimestamp()
    {
        return $this->_timestamp;
    }

    /**
     * Set the Mob connect timestamp.
     *
     * @param string $_timestamp the Mob connect timestamp
     *
     * @return self
     */
    public function setTimestamp(string $_timestamp)
    {
        $this->_timestamp = $_timestamp;

        return $this;
    }

    /**
     * @throws HttpException
     */
    private function _isResponseError(): bool
    {
        if (in_array($this->getCode(), self::ERROR_CODES)) {
            throw new HttpException($this->getCode(), $this->getContent());
        }

        return false;
    }
}
