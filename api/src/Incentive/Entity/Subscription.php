<?php

namespace App\Incentive\Entity;

abstract class Subscription
{
    public const INCENTIVE_2023 = 'CoupPouceCEE2023';
    public const iNCENTIVE_MOBICOOP_2024 = 'CEEStandardMobicoop';

    private const ALLOWED_VERSION = [
        self::INCENTIVE_2023,
        self::iNCENTIVE_MOBICOOP_2024,
    ];

    private $incentiveProofTimestampToken;
    private $incentiveProofTimestampSigningTime;

    private $commitmentProofTimestampToken;
    private $commitmentProofTimestampSigningTime;

    private $honorCertificateProofTimestampToken;
    private $honorCertificateProofTimestampSigningTime;

    private $version;

    /**
     * Get the long distance EEC incentive proof timestamp token.
     */
    public function getIncentiveProofTimestampToken(): ?string
    {
        return $this->incentiveProofTimestampToken;
    }

    /**
     * Get the long distance EEC incentive proof timestamp signing time.
     */
    public function getIncentiveProofTimestampSigningTime(): ?\DateTime
    {
        return $this->incentiveProofTimestampSigningTime;
    }

    /**
     * Get the long distance EEC commitment proof timestamp token.
     */
    public function getCommitmentProofTimestampToken(): ?string
    {
        return $this->commitmentProofTimestampToken;
    }

    /**
     * Get the long distance EEC commitment proof timestamp signing time.
     */
    public function getCommitmentProofTimestampSigningTime(): ?\DateTime
    {
        return $this->commitmentProofTimestampSigningTime;
    }

    /**
     * Get the long distance EEC honor certificate proof timestamp token.
     */
    public function getHonorCertificateProofTimestampToken(): ?string
    {
        return $this->honorCertificateProofTimestampToken;
    }

    /**
     * Get the long distance EEC honor certificate proof timestamp signing time.
     */
    public function getHonorCertificateProofTimestampSigningTime(): ?\DateTime
    {
        return $this->honorCertificateProofTimestampSigningTime;
    }

    /**
     * Get the value of hasIncentiveToken.
     */
    public function hasIncentiveToken(): bool
    {
        return !is_null($this->getIncentiveProofTimestampToken());
    }

    /**
     * Get the value of hasCommitToken.
     */
    public function hasCommitToken(): bool
    {
        return !is_null($this->getCommitmentProofTimestampToken());
    }

    /**
     * Get the value of hasHonorCertificateToken.
     */
    public function hasHonorCertificateToken(): bool
    {
        return !is_null($this->getHonorCertificateProofTimestampToken());
    }

    /**
     * Get the subscription version.
     */
    public function getVersion(): ?string
    {
        return $this->version;
    }

    /**
     * Set the long distance EEC incentive proof timestamp token.
     *
     * @param string $incentiveProofTimestampToken the long distance EEC incentive proof timestamp token
     */
    public function setIncentiveProofTimestampToken(?string $incentiveProofTimestampToken): self
    {
        $this->incentiveProofTimestampToken = $incentiveProofTimestampToken;

        return $this;
    }

    /**
     * Set the long distance EEC incentive proof timestamp signing time.
     *
     * @param \DateTimeInterface $incentiveProofTimestampSigningTime the long distance EEC incentive proof timestamp signing time
     */
    public function setIncentiveProofTimestampSigningTime(\DateTimeInterface $incentiveProofTimestampSigningTime): self
    {
        $this->incentiveProofTimestampSigningTime = $incentiveProofTimestampSigningTime;

        return $this;
    }

    /**
     * Set the long distance EEC commitment proof timestamp token.
     *
     * @param string $commitmentProofTimestampToken the long distance EEC commitment proof timestamp token
     */
    public function setCommitmentProofTimestampToken(?string $commitmentProofTimestampToken): self
    {
        $this->commitmentProofTimestampToken = $commitmentProofTimestampToken;

        return $this;
    }

    /**
     * Set the long distance EEC commitment proof timestamp signing time.
     *
     * @param \DateTimeInterface $commitmentProofTimestampSigningTime the long distance EEC commitment proof timestamp signing time
     */
    public function setCommitmentProofTimestampSigningTime(?\DateTimeInterface $commitmentProofTimestampSigningTime): self
    {
        $this->commitmentProofTimestampSigningTime = $commitmentProofTimestampSigningTime;

        return $this;
    }

    /**
     * Set the long distance EEC honor certificate proof timestamp token.
     *
     * @param string $honorCertificateProofTimestampToken the long distance EEC honor certificate proof timestamp token
     */
    public function setHonorCertificateProofTimestampToken(?string $honorCertificateProofTimestampToken): self
    {
        $this->honorCertificateProofTimestampToken = $honorCertificateProofTimestampToken;

        return $this;
    }

    /**
     * Set the long distance EEC honor certificate proof timestamp signing time.
     *
     * @param \DateTimeInterface $honorCertificateProofTimestampSigningTime the long distance EEC honor certificate proof timestamp signing time
     */
    public function setHonorCertificateProofTimestampSigningTime(?\DateTimeInterface $honorCertificateProofTimestampSigningTime): self
    {
        $this->honorCertificateProofTimestampSigningTime = $honorCertificateProofTimestampSigningTime;

        return $this;
    }

    /**
     * Set the subscription version.
     *
     * @param string $version the subscription version
     */
    public function setVersion(string $version): self
    {
        if (!in_array($version, self::ALLOWED_VERSION)) {
            throw new \LogicException('The version you want to assign is not allowed');
        }

        $this->version = $version;

        return $this;
    }
}
