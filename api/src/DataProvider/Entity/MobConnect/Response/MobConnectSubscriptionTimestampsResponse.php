<?php

namespace App\DataProvider\Entity\MobConnect\Response;

use Symfony\Component\HttpFoundation\Response;

class MobConnectSubscriptionTimestampsResponse extends MobConnectResponse
{
    public const TYPE_SUBSCRIPTION = 0;
    public const TYPE_COMMITMENT = 1;
    public const TYPE_HONOR_CERTIFICATE = 2;

    public const ALLOWED_TOKEN_TYPES = [
        self::TYPE_SUBSCRIPTION,
        self::TYPE_COMMITMENT,
        self::TYPE_HONOR_CERTIFICATE,
    ];

    /**
     * @var \stdClass[]
     */
    private $_tokens;

    public function __construct(Response $mobConnectResponse)
    {
        parent::__construct($mobConnectResponse);

        $this->_buildObject();
    }

    /**
     * Get the value of _tokens.
     */
    public function getTokens(): array
    {
        return $this->_tokens;
    }

    private function _buildObject()
    {
        if (!in_array($this->getCode(), self::ERROR_CODES) && !is_null($this->_content)) {
            $this->_tokens = $this->_content;
        }
    }
}
