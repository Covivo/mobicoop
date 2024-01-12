<?php

namespace App\DataProvider\Entity\MobConnect\Response;

use Symfony\Component\HttpFoundation\Response;

class MobConnectSubscriptionResponse extends MobConnectResponse implements \JsonSerializable
{
    /**
     * The Mob connect subscription ID.
     *
     * @var string
     */
    protected $_id;

    public function __construct(Response $mobConnectResponse, array $data = null)
    {
        parent::__construct($mobConnectResponse, $data);

        if (!in_array($this->getCode(), self::ERROR_CODES) && !is_null($this->_content)) {
            if (property_exists($this->_content, 'id' && !is_null($this->_content->id))) {
                $this->setId($this->_content->id);
            }

            if (property_exists($this->_content, 'timestamp') && !is_null($this->_content->timestamp)) {
                $this->setTimestamp($this->_content->timestamp);
            }
        }
    }

    /**
     * Get the value of _id.
     */
    public function getId(): string
    {
        return $this->_id;
    }

    public function jsonSerialize(): array
    {
        return [
            'code' => $this->getCode(),
            'content' => $this->getContent(),
        ];
    }

    /**
     * Set the value of _id.
     *
     * @param mixed $_id
     */
    private function setId(string $_id): self
    {
        $this->_id = $_id;

        return $this;
    }
}
