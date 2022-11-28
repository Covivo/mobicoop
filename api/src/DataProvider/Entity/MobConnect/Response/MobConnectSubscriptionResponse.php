<?php

namespace App\DataProvider\Entity\MobConnect\Response;

class MobConnectSubscriptionResponse
{
    /**
     * The Mob connect subscription ID.
     *
     * @var string
     */
    protected $_id;

    public function __construct(\stdClass $mobConnectResponse)
    {
        $this->setId($mobConnectResponse->subscriptionId);
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
