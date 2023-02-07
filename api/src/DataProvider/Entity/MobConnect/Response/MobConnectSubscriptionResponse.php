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

    /**
     * The Mob connect timestamp.
     *
     * @var string
     */
    protected $_timestamp;

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
