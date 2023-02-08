<?php

namespace App\DataProvider\Entity\MobConnect\Response;

class MobConnectSubscriptionResponse extends MobConnectResponse
{
    /**
     * The Mob connect subscription ID.
     *
     * @var string
     */
    protected $_id;

    public function __construct(array $mobConnectResponse)
    {
        parent::__construct($mobConnectResponse);

        if (!in_array($this->getCode(), self::ERROR_CODES) && !is_null($this->_content)) {
            $this->setId($this->_content->id);

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
