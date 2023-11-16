<?php

namespace App\Incentive\Entity;

use Symfony\Component\CssSelector\Exception\InternalErrorException;

abstract class Subscription
{
    private const ACTIVE_YEAR_PATTERN = '/^202[3-7]{1}$/';

    protected $createdAt;

    protected $longDistanceJourneys;
    protected $shortDistanceJourneys;

    protected $commitmentProofJourney;
    protected $commitmentProofDate;

    protected $incentiveProofTimestampToken;
    protected $incentiveProofTimestampSigningTime;

    protected $commitmentProofTimestampToken;
    protected $commitmentProofTimestampSigningTime;

    protected $honorCertificateProofTimestampToken;
    protected $honorCertificateProofTimestampSigningTime;

    protected $version;
    protected $versionStatus;

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $date): self
    {
        $this->createdAt = $date;

        return $this;
    }

    /**
     * Return all journeys.
     */
    public function getJourneys()
    {
        switch (true) {
            case $this instanceof LongDistanceJourney: return $this->getLongDistanceJourneys();

            case $this instanceof ShortDistanceJourney: return $this->getShortDistanceJourneys();

            default: return [];
        }
    }

    public function getLongDistanceJourneys()
    {
        return $this->longDistanceJourneys;
    }

    public function getShortDistanceJourneys()
    {
        return $this->shortDistanceJourneys;
    }

    public function getCommitmentProofJourney()
    {
        return $this->commitmentProofJourney;
    }

    public function getCommitmentProofDate(): ?\DateTime
    {
        return $this->commitmentProofDate;
    }

    /**
     * TODO - Tester la fonction.
     */
    public function isCommitted(): bool
    {
        return !is_null($this->getCommitmentProofJourney());
    }

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
     * TODO - Tester la fonction.
     */
    public function setVersion(): self
    {
        if (is_null($this->getVersion()) || SubscriptionVersion::INCENTIVE_MOBICOOP_2024 !== $this->getVersion()) {
            $version = new SubscriptionVersion($this);

            $this->version = $version->getVersion();
            $this->setVersionStatus($version->getVersionStatus());
        }

        return $this;
    }

    public function getVersionStatus(): ?int
    {
        return $this->versionStatus;
    }

    public function setVersionStatus($versionStatus): self
    {
        $this->versionStatus = $versionStatus;

        return $this;
    }

    /**
     * TODO - Tester la fonction.
     */
    public function getSubscriptionYear(): string
    {
        return $this->getCreatedAt()->format('Y');
    }

    /**
     * TODO - Tester la fonction.
     */
    public function isSubscriptionYearGivenYear(string $year): bool
    {
        $this->checkYearPattern($year);

        return $year === $this->getSubscriptionYear();
    }

    /**
     * TODO - Tester la fonction.
     */
    public function getCommitmentYear(): ?string
    {
        return !is_null($this->getCommitmentproofDate())
            ? $this->getCommitmentProofDate()->format('Y')
            : null;
    }

    /**
     * TODO - Tester la fonction.
     */
    public function isCommittedYearGivenYear(string $year): bool
    {
        $this->checkYearPattern($year);

        return $year === $this->getCommitmentYear();
    }

    /**
     * TODO - Tester la fonction.
     */
    private function checkYearPattern(string $year): void
    {
        if (!preg_match(self::ACTIVE_YEAR_PATTERN, $year)) {
            throw new InternalErrorException('The year passed as parameter is not valid');
        }
    }
}
