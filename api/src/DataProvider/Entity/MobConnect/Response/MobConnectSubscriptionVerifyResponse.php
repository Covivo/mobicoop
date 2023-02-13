<?php

namespace App\DataProvider\Entity\MobConnect\Response;

class MobConnectSubscriptionVerifyResponse extends MobConnectResponse
{
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

    public function __construct(array $mobConnectResponse)
    {
        parent::__construct($mobConnectResponse);

        if (!in_array($this->getCode(), self::ERROR_CODES) && !is_null($this->_content)) {
            $this->setStatus($this->_content->status);

            if (isset($this->_content->rejectionReason)) {
                $this->setRejectReason($this->_content->motif_de_rejet);
            }

            if (isset($this->_content->comments)) {
                $this->setComment($this->_content->comments);
            }
        }
    }

    /**
     * Get the value of _status.
     */
    public function getStatus(): string
    {
        return $this->_status;
    }

    /**
     * Set the value of _status.
     */
    public function setStatus(string $_status): self
    {
        $this->_status = $_status;

        return $this;
    }

    /**
     * Get the value of _rejectReason.
     *
     * @return string
     */
    public function getRejectReason(): ?string
    {
        return $this->_rejectReason;
    }

    /**
     * Set the value of _rejectReason.
     */
    public function setRejectReason(string $_rejectReason): self
    {
        $this->_rejectReason = $_rejectReason;

        return $this;
    }

    /**
     * Get the value of _comment.
     *
     * @return string
     */
    public function getComment(): ?string
    {
        return $this->_comments;
    }

    /**
     * Set the value of _comment.
     */
    public function setComment(string $_comments): self
    {
        $this->_comments = $_comments;

        return $this;
    }
}
