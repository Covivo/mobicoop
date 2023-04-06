<?php

namespace App\DataProvider\Entity\MobConnect\Response;

class MobConnectSubscriptionTimestampsResponse extends MobConnectResponse
{
    /**
     * @var string
     */
    private $commitmentProofTimestampToken;

    /**
     * @var \DateTime
     */
    private $commitmentProofTimestampSigningTime;

    /**
     * @var string
     */
    private $honorCertificateProofTimestampToken;

    /**
     * @var \DateTime
     */
    private $honorCertificateProofTimestampSigningTime;

    /**
     * @var string
     */
    private $incentiveProofTimestampToken;

    /**
     * @var \DateTime
     */
    private $incentiveProofTimestampSigningTime;

    public function __construct(array $mobConnectResponse)
    {
        parent::__construct($mobConnectResponse);

        $this->_buildObject();
    }

    /**
     * Get the value of commitmentProofTimestampToken.
     */
    public function getCommitmentProofTimestampToken(): ?string
    {
        return $this->commitmentProofTimestampToken;
    }

    /**
     * Set the value of commitmentProofTimestampToken.
     */
    public function setCommitmentProofTimestampToken(string $commitmentProofTimestampToken): self
    {
        $this->commitmentProofTimestampToken = $commitmentProofTimestampToken;

        return $this;
    }

    /**
     * Get the value of commitmentProofTimestampSigningTime.
     *
     * @return \DateTime
     */
    public function getCommitmentProofTimestampSigningTime(): ?\DateTime
    {
        return $this->commitmentProofTimestampSigningTime;
    }

    /**
     * Set the value of commitmentProofTimestampSigningTime.
     */
    public function setCommitmentProofTimestampSigningTime(\DateTime $commitmentProofTimestampSigningTime): self
    {
        $this->commitmentProofTimestampSigningTime = $commitmentProofTimestampSigningTime;

        return $this;
    }

    /**
     * Get the value of honorCertificateProofTimestampToken.
     *
     * @return string
     */
    public function getHonorCertificateProofTimestampToken(): ?string
    {
        return $this->honorCertificateProofTimestampToken;
    }

    /**
     * Set the value of honorCertificateProofTimestampToken.
     */
    public function setHonorCertificateProofTimestampToken(string $honorCertificateProofTimestampToken): self
    {
        $this->honorCertificateProofTimestampToken = $honorCertificateProofTimestampToken;

        return $this;
    }

    /**
     * Get the value of honorCertificateProofTimestampSigningTime.
     *
     * @return \DateTime
     */
    public function getHonorCertificateProofTimestampSigningTime(): ?\DateTime
    {
        return $this->honorCertificateProofTimestampSigningTime;
    }

    /**
     * Set the value of honorCertificateProofTimestampSigningTime.
     */
    public function setHonorCertificateProofTimestampSigningTime(\DateTime $honorCertificateProofTimestampSigningTime): self
    {
        $this->honorCertificateProofTimestampSigningTime = $honorCertificateProofTimestampSigningTime;

        return $this;
    }

    /**
     * Get the value of incentiveProofTimestampToken.
     *
     * @return string
     */
    public function getIncentiveProofTimestampToken(): ?string
    {
        return $this->incentiveProofTimestampToken;
    }

    /**
     * Set the value of incentiveProofTimestampToken.
     */
    public function setIncentiveProofTimestampToken(string $incentiveProofTimestampToken): self
    {
        $this->incentiveProofTimestampToken = $incentiveProofTimestampToken;

        return $this;
    }

    /**
     * Get the value of incentiveProofTimestampSigningTime.
     *
     * @return \DateTime
     */
    public function getIncentiveProofTimestampSigningTime(): ?\DateTime
    {
        return $this->incentiveProofTimestampSigningTime;
    }

    /**
     * Set the value of incentiveProofTimestampSigningTime.
     */
    public function setIncentiveProofTimestampSigningTime(\DateTime $incentiveProofTimestampSigningTime): self
    {
        $this->incentiveProofTimestampSigningTime = $incentiveProofTimestampSigningTime;

        return $this;
    }

    private function _buildObject()
    {
        if (!in_array($this->getCode(), self::ERROR_CODES) && !is_null($this->_content)) {
            // Trier le tableau par date
            $timestamps = $this->_sortTimestampsByDateASC();

            if (isset($timestamps[0])) {
                $this->setIncentiveProofTimestampToken($timestamps[0]->timestampToken);
                $this->setIncentiveProofTimestampSigningTime(new \DateTime($timestamps[0]->signingTime));
            }

            if (isset($timestamps[1])) {
                $this->setCommitmentProofTimestampToken($timestamps[1]->timestampToken);
                $this->setCommitmentProofTimestampSigningTime(new \DateTime($timestamps[1]->signingTime));
            }

            if (isset($timestamps[2])) {
                $this->setHonorCertificateProofTimestampToken($timestamps[2]->timestampToken);
                $this->setHonorCertificateProofTimestampSigningTime(new \DateTime($timestamps[2]->signingTime));
            }
        }
    }

    private function _sortTimestampsByDateASC(): array
    {
        /**
         * @var array
         */
        $timestamps = $this->getContent();

        if (!empty($timestamps)) {
            usort($timestamps, function ($a, $b) {
                return strcmp($a->signingTime, $b->signingTime);
            });
        }

        return $timestamps;
    }
}
