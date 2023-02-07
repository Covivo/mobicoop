<?php

namespace App\DataProvider\Entity\MobConnect\Response;

abstract class MobConnectResponse
{
    protected const ERROR_CODES = [400, 401, 403, 404, 412, 415, 422];

    /**
     * @var int
     */
    protected $_code;

    protected $_content;

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
}
