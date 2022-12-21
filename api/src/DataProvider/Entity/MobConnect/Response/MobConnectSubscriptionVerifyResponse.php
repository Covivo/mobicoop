<?php

namespace App\DataProvider\Entity\MobConnect\Response;

class MobConnectSubscriptionVerifyResponse extends MobConnectSubscriptionResponse
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
    private $_comment;

    public function __construct(\stdClass $mobConnectResponse)
    {
        parent::__construct($mobConnectResponse);

        $this->setStatus($mobConnectResponse->status);

        if (isset($mobConnectResponse->motif_de_rejet)) {
            $this->setRejectReason($mobConnectResponse->motif_de_rejet);
        }

        if (isset($mobConnectResponse->commentaire)) {
            $this->setComment($mobConnectResponse->commentaire);
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
        return $this->_comment;
    }

    /**
     * Set the value of _comment.
     */
    public function setComment(string $_comment): self
    {
        $this->_comment = $_comment;

        return $this;
    }
}
