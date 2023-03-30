<?php

namespace App\DataProvider\Entity\MobConnect\Response;

class MobConnectSubscriptionTimestampsResponse extends MobConnectResponse
{
    /**
     * @var string
     */
    private $commitmentProofTimestamp;

    /**
     * @var string
     */
    private $honorCertificateProofTimestamp;

    /**
     * @var string
     */
    private $incentiveProofTimestamp;

    public function __construct(array $mobConnectResponse)
    {
        parent::__construct($mobConnectResponse);

        if (!in_array($this->getCode(), self::ERROR_CODES) && !is_null($this->_content)) {
            $this->setCommitmentProofTimestamp(bin2hex('fake'));
            $this->setHonorCertificateProofTimestamp(bin2hex('fake'));
            $this->setIncentiveProofTimestamp(bin2hex('fake'));
        }
    }

    /**
     * Get the value of commitmentProofTimestamp.
     */
    public function getCommitmentProofTimestamp(): ?string
    {
        return $this->commitmentProofTimestamp;
    }

    /**
     * Set the value of commitmentProofTimestamp.
     */
    public function setCommitmentProofTimestamp(string $commitmentProofTimestamp): self
    {
        $this->commitmentProofTimestamp = $commitmentProofTimestamp;

        return $this;
    }

    /**
     * Get the value of honorCertificateProofTimestamp.
     */
    public function getHonorCertificateProofTimestamp(): ?string
    {
        return $this->honorCertificateProofTimestamp;
    }

    /**
     * Set the value of honorCertificateProofTimestamp.
     */
    public function setHonorCertificateProofTimestamp(string $honorCertificateProofTimestamp): self
    {
        $this->honorCertificateProofTimestamp = $honorCertificateProofTimestamp;

        return $this;
    }

    /**
     * Get the value of incentiveProofTimestamp.
     */
    public function getIncentiveProofTimestamp(): ?string
    {
        return $this->incentiveProofTimestamp;
    }

    /**
     * Set the value of incentiveProofTimestamp.
     */
    public function setIncentiveProofTimestamp(string $incentiveProofTimestamp): self
    {
        $this->incentiveProofTimestamp = $incentiveProofTimestamp;

        return $this;
    }
}
