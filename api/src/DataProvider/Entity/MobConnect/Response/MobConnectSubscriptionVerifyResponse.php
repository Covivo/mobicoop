<?php

namespace App\DataProvider\Entity\MobConnect\Response;

use Symfony\Component\HttpFoundation\Response;

class MobConnectSubscriptionVerifyResponse extends MobConnectResponse
{
    public const SUCCESS_STATUS = 200;

    /**
     * @var string
     */
    private $_status;

    /**
     * @var string
     */
    private $_rejectReason;

    /**
     * @var string
     */
    private $_comments;

    public function __construct(Response $mobConnectResponse)
    {
        parent::__construct($mobConnectResponse);

        if (!in_array($this->getCode(), self::ERROR_CODES) && !is_null($this->_content)) {
            $this->setStatus($this->_content->status);
        }
    }

    /**
     * Get the value of _status.
     */
    public function getStatus(): ?string
    {
        return $this->_status;
    }

    /**
     * Set the value of _status.
     */
    public function setStatus(?string $_status): self
    {
        $this->_status = $_status;

        return $this;
    }

    /**
     * Get the value of _rejectReason.
     */
    public function getRejectReason(): ?string
    {
        return $this->_rejectReason;
    }

    /**
     * Set the value of _rejectReason.
     */
    public function setRejectReason(?string $_rejectReason): self
    {
        $this->_rejectReason = $_rejectReason;

        return $this;
    }

    /**
     * Get the value of _comment.
     */
    public function getComment(): ?string
    {
        return $this->_comments;
    }

    /**
     * Set the value of _comment.
     */
    public function setComment(?string $_comments): self
    {
        $this->_comments = $_comments;

        return $this;
    }
}
