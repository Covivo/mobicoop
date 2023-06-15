<?php

namespace App\DataProvider\Entity\MobConnect\Response;

abstract class MobConnectResponse implements MobConnectResponseInterface
{
    public const ERROR_CODES = [400, 401, 403, 404, 409, 412, 415, 422, 500];

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

    public function __construct(array $mobConnectResponse, array $payload = null)
    {
        if (isset($mobConnectResponse['code'])) {
            $this->_code = $mobConnectResponse['code'];
        }
        if (isset($mobConnectResponse['content'])) {
            $this->_content = is_null(json_decode($mobConnectResponse['content'])) ? $mobConnectResponse['content'] : json_decode($mobConnectResponse['content']);
        }
        $this->_payload = $payload;
    }

    public static function isResponseErrorResponse(MobConnectResponseInterface $response)
    {
        return in_array($response->getCode(), MobConnectResponse::ERROR_CODES);
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
