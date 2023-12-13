<?php

namespace App\Incentive\Entity;

/**
 * TODO - Tester la classe.
 */
class SubscriptionVersion
{
    public const EEC_VERSION_STANDARD = 0;
    public const EEC_VERSION_IMPROVED = 1;

    public const EEC_VERSION_STANDARD_DEADLINE = '2024-01-01';

    public const ALLOWED_VERSIONS = [
        self::EEC_VERSION_STANDARD,
        self::EEC_VERSION_IMPROVED,
    ];

    /**
     * @var LongDistanceSubscription|ShortDistanceSubscription
     */
    private $_currentSubscription;

    /**
     * @var LongDistanceJourney|ShortDistanceJourney
     */
    private $_currentCommitmentJourney;

    /**
     * @var \DateTimeInterface
     */
    private $_deadline;

    /**
     * @var \DateTimeInterface
     */
    private $_publicationDeadline;

    /**
     * @var \DateTimeInterface
     */
    private $_today;

    /**
     * @var string
     */
    private $_version;

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    public function __construct($subscription)
    {
        $this->_deadline = new \DateTime(self::EEC_VERSION_STANDARD_DEADLINE);
        $this->_publicationDeadline = clone $this->_deadline;
        $this->_publicationDeadline->add(new \DateInterval('P1M'));
        $this->_today = new \DateTime('now');

        $this->_currentSubscription = $subscription;
        $this->_currentCommitmentJourney = $this->_currentSubscription->getCommitmentProofJourney();

        if (self::EEC_VERSION_IMPROVED !== $this->_currentSubscription->getVersion()) {
            $this->build();
        }
    }

    public function build(): void
    {
        if (                                                // 2. Subscription in 2024
            $this->isSubscriptionAfterIncentiveDeadline()
        ) {
            $this->_version = self::EEC_VERSION_IMPROVED;
        }

        if ($this->isSubscriptionBeforeIncentiveDeadline()) {
            $this->proccessFor2023();
        }
    }

    public function getCurrentSubscription()
    {
        return $this->_currentSubscription;
    }

    public function getCurrentCommitmentJourney()
    {
        return $this->_currentCommitmentJourney;
    }

    public function getVersion(): ?string
    {
        return $this->_version;
    }

    public function isDateComing(\DateTimeInterface $date): bool
    {
        return $this->_today < $date;
    }

    public function isDateBeforeDeadline(\DateTimeInterface $date): bool
    {
        return $date < $this->_deadline;
    }

    public function isDateAfterDeadline(\DateTimeInterface $date): bool
    {
        return !$this->isDateBeforeDeadline($date);
    }

    public function isDateBeforePublishedDeadline(\DateTimeInterface $date): bool
    {
        return $date < $this->_publicationDeadline;
    }

    public function isDateAfterPublishedDeadline(\DateTimeInterface $date): bool
    {
        return !$this->isDateBeforePublishedDeadline($date);
    }

    public function isDeadlinePassed(): bool
    {
        return $this->isDateAfterDeadline($this->_today);
    }

    public function isSubscriptionBeforeIncentiveDeadline(): bool
    {
        return !$this->isSubscriptionAfterIncentiveDeadline();
    }

    public function isSubscriptionAfterIncentiveDeadline(): bool
    {
        return $this->_deadline->format('Y') <= $this->_currentSubscription->getSubscriptionYear();
    }

    public function isCommitmentBeforeIncentiveDeadline(): bool
    {
        return !$this->isCommitmentAfterIncentiveDeadline();
    }

    public function isCommitmentAfterIncentiveDeadline(): bool
    {
        return !is_null($this->_currentCommitmentJourney)
            ? $this->_deadline->format('Y') <= $this->_currentCommitmentJourney->getCreatedAt()->format('Y')
            : false;
    }

    private function proccessFor2023(): void
    {
        if (                                                // 1.4. No journey published to date
            is_null($this->_currentCommitmentJourney)
        ) {
            if ($this->isDateBeforeDeadline($this->_today)) {
                $this->_version = self::EEC_VERSION_STANDARD;
            }

            if ($this->isDateAfterDeadline($this->_today)) {
                $this->_version = self::EEC_VERSION_IMPROVED;
            }
        }

        if (!is_null($this->_currentCommitmentJourney)) {
            if (                                            // 1.1.|1.2. First journey published in 2023
                $this->isDateBeforeDeadline($this->_currentCommitmentJourney->getCreatedAt())
            ) {
                if (                                            // 1.1.&1.2. Ld journey
                    $this->_currentCommitmentJourney instanceof LongDistanceJourney
                ) {
                    if (                                        // 1.1. First journey validated in 2023
                        $this->_currentCommitmentJourney->isEECCompliant()
                        && !is_null($this->_currentCommitmentJourney->getCarpoolPayment())
                        && !is_null($this->_currentCommitmentJourney->getCarpoolPayment()->getTransactionDate())
                        && $this->isDateBeforeDeadline($this->_currentCommitmentJourney->getCarpoolPayment()->getTransactionDate())
                        && !is_null($this->_currentCommitmentJourney->getCarpoolItem())
                        && !is_null($this->_currentCommitmentJourney->getCarpoolItem()->getCarpoolProof())
                        && !is_null($this->_currentCommitmentJourney->getCarpoolItem()->getCarpoolProof()->getUpdatedDate())
                        && $this->isDateBeforeDeadline($this->_currentCommitmentJourney->getCarpoolItem()->getCarpoolProof()->getUpdatedDate())
                    ) {
                        $this->_version = self::EEC_VERSION_STANDARD;
                    }

                    if (                                        // 1.2. First journey validation in progress
                        !$this->_currentCommitmentJourney->isEECCompliant()
                        && !is_null($this->_currentCommitmentJourney->getInitialProposal())
                        && !is_null($this->_currentCommitmentJourney->getInitialProposal()->getCriteria())
                    ) {
                        if (                                    // 1.2.1. Ld first journey realised before the published deadline
                            $this->isDateBeforePublishedDeadline($this->_currentCommitmentJourney->getInitialProposal()->getCriteria()->getFromDate())
                        ) {
                            $this->_version = self::EEC_VERSION_STANDARD;
                        }

                        if (                                    // 1.2.1. Ld first journey realised after the published deadline
                            $this->isDateAfterPublishedDeadline($this->_currentCommitmentJourney->getInitialProposal()->getCriteria()->getFromDate())
                        ) {
                            $this->_version = self::EEC_VERSION_IMPROVED;
                        }
                    }
                }

                if (                                            // 1.1.&1.2 Sd journey
                    $this->_currentCommitmentJourney instanceof ShortDistanceJourney
                ) {
                    if (
                        $this->_currentCommitmentJourney->isEECCompliant()
                        && !is_null($this->_currentCommitmentJourney->getCarpoolProof())
                        && !is_null($this->_currentCommitmentJourney->getCarpoolProof()->getUpdatedDate())
                        && $this->isDateBeforeDeadline($this->_currentCommitmentJourney->getCarpoolProof()->getUpdatedDate())
                    ) {
                        $this->_version = self::EEC_VERSION_STANDARD;
                    }

                    if (                                        // 1.2.1&1.2.2 First journey validation in progress
                        !$this->_currentCommitmentJourney->isEECCompliant()
                    ) {
                        if (                                    // 1.2.1. First journey validation before published deadline
                            $this->isDateAfterPublishedDeadline($this->_today)
                        ) {
                            $this->_version = self::EEC_VERSION_STANDARD;
                        }

                        if (                                    // 1.2.2. First journey validation after published deadline
                            $this->isDateAfterPublishedDeadline($this->_today)
                        ) {
                            $this->_version = self::EEC_VERSION_IMPROVED;
                        }
                    }
                }
            }

            if (                                            // 1.3. First journey published in 2024
                $this->isDateAfterDeadline($this->_currentCommitmentJourney->getCreatedAt())
            ) {
                $this->_version = self::EEC_VERSION_IMPROVED;
            }
        }

        if (                                                // 1.3. First journey published in 2024
            $this->isSubscriptionBeforeIncentiveDeadline()
            && !is_null($this->_currentCommitmentJourney)
            && !$this->isDateBeforeDeadline($this->_currentCommitmentJourney->getCreatedAt())
        ) {
            $this->_version = self::EEC_VERSION_IMPROVED;
        }
    }
}
