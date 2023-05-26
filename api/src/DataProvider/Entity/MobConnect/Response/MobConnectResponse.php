<?php

namespace App\DataProvider\Entity\MobConnect\Response;

abstract class MobConnectResponse
{
    public const ERROR_CODES = [400, 401, 403, 404, 409, 412, 415, 422, 500];

    /**
     * @var int
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
     * @var string
     */
    protected $_timestamp;

    public function __construct(array $mobConnectResponse, array $payload = null)
    {
        $this->_code = $mobConnectResponse['code'];
        $this->_content = is_null(json_decode($mobConnectResponse['content'])) ? $mobConnectResponse['content'] : json_decode($mobConnectResponse['content']);
        $this->_payload = $payload;
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
}
