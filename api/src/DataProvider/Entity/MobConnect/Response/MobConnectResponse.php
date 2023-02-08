<?php

namespace App\DataProvider\Entity\MobConnect\Response;

abstract class MobConnectResponse
{
    public const ERROR_CODES = [400, 401, 403, 404, 412, 415, 422];

    /**
     * @var int
     */
    protected $_code;

    protected $_content;

    /**
     * The Mob connect timestamp.
     *
     * @var string
     */
    protected $_timestamp;

    public function __construct(array $mobConnectResponse)
    {
        $this->_code = $mobConnectResponse['code'];
        $this->_content = json_decode($mobConnectResponse['content']);
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
