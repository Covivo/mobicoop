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

    /**
     * The Mob connect timestamp.
     *
     * @var string
     */
    protected $_timestamp;

    public function __construct(\stdClass $mobConnectResponse)
    {
        $this->setId($mobConnectResponse->id);

        // TODO: Check the property name
        if (property_exists($mobConnectResponse, 'timestamp') && !is_null($mobConnectResponse->timestamp)) {
            $this->setTimestamp($mobConnectResponse->timestamp);
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
