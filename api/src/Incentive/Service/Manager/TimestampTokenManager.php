<?php

namespace App\Incentive\Service\Manager;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Proposal;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionTimestampsResponse;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\Incentive\Service\HonourCertificateService;
use App\Incentive\Service\LoggerService;
use Doctrine\ORM\EntityManagerInterface;

class TimestampTokenManager extends MobConnectManager
{
    public const TIMESTAMP_TOKEN_TYPE_INCENTIVE = 1;
    public const TIMESTAMP_TOKEN_TYPE_COMMITMENT = 2;
    public const TIMESTAMP_TOKEN_TYPE_HONOR_CERTIFICATE = 3;

    public const AVAILABLE_TIMESTAMP_TOKEN_TYPES = [
        self::TIMESTAMP_TOKEN_TYPE_INCENTIVE,
        self::TIMESTAMP_TOKEN_TYPE_COMMITMENT,
        self::TIMESTAMP_TOKEN_TYPE_HONOR_CERTIFICATE,
    ];

    private const DEFAULT_GETTER_PREFIX = 'get';
    private const DEFAULT_SETTER_PREFIX = 'set';
    private const DEFAULT_TOKEN_SUFFIX = 'ProofTimestampToken';
    private const DEFAULT_SIGNINTIME_SUFFIX = 'ProofTimestampSigningTime';
    private const DEFAULT_TAG = '{TAG}';

    private const DEFAULT_TIMESTAMP_TOKEN_GETTER = self::DEFAULT_GETTER_PREFIX.self::DEFAULT_TAG.self::DEFAULT_TOKEN_SUFFIX;
    private const DEFAULT_TIMESTAMP_TOKEN_SETTER = self::DEFAULT_SETTER_PREFIX.self::DEFAULT_TAG.self::DEFAULT_TOKEN_SUFFIX;
    private const DEFAULT_TIMESTAMP_SIGNINTIME_GETTER = self::DEFAULT_GETTER_PREFIX.self::DEFAULT_TAG.self::DEFAULT_SIGNINTIME_SUFFIX;
    private const DEFAULT_TIMESTAMP_SIGNINTIME_SETTER = self::DEFAULT_SETTER_PREFIX.self::DEFAULT_TAG.self::DEFAULT_SIGNINTIME_SUFFIX;

    /**
     * @var int
     */
    private $_currentLogType;

    /**
     * @var LongDistanceSubscription|ShortDistanceSubscription
     */
    private $_currentSubscription;

    /**
     * @var MobConnectSubscriptionTimestampsResponse
     */
    private $_currentTimestampTokensResponse;

    /**
     * @var int[]
     */
    private $_missingTimestampTokens = [];

    public function __construct(
        EntityManagerInterface $em,
        LoggerService $loggerService,
        HonourCertificateService $honourCertificateService,
        string $carpoolProofPrefix,
        array $mobConnectParams,
        array $ssoServices
    ) {
        parent::__construct($em, $loggerService, $honourCertificateService, $carpoolProofPrefix, $mobConnectParams, $ssoServices);
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     *
     * @return LongDistanceSubscription|ShortDistanceSubscription
     */
    public function setSubscriptionTimestampToken($subscription, int $tokenType)
    {
        if (is_null($subscription)) {
            return;
        }

        $this->_setCurrentSubscription($subscription);

        // We define the current token type
        if ($this->_isTokenTypeAllowed($tokenType)) {
            switch ($tokenType) {
                case self::TIMESTAMP_TOKEN_TYPE_INCENTIVE:
                    $logType = Log::TYPE_TIMESTAMP_SUBSCRIPTION;

                    break;

                case self::TIMESTAMP_TOKEN_TYPE_COMMITMENT:
                    $logType = Log::TYPE_TIMESTAMP_COMMITMENT;

                    break;

                case self::TIMESTAMP_TOKEN_TYPE_HONOR_CERTIFICATE:
                    $logType = Log::TYPE_TIMESTAMP_ATTESTATION;

                    break;
            }

            $this->_setCurrentLogType($logType);
        }

        // We get the tokens
        $this->_setCurrentTimestampTokensResponse();

        $this->_setSubscriptionTimestampToken($tokenType);

        $this->_resetAll();

        return $this->_currentSubscription;
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     *
     * @return LongDistanceSubscription|ShortDistanceSubscription
     */
    public function setMissingSubscriptionTimestampTokens($subscription, int $logType)
    {
        $this->_setCurrentSubscription($subscription);
        $this->_setCurrentLogType($logType);

        foreach (self::AVAILABLE_TIMESTAMP_TOKEN_TYPES as $tokenType) {
            if ($this->_isMissingTimestampToken($tokenType)) {
                $this->_addMissingTimestampToken($tokenType);
            }
        }

        if (!empty($this->_missingTimestampTokens)) {
            $this->_setSubscriptionMissingTimestampTokens();

            $this->_setMissingCommitmentJourney();
        }

        $this->_resetAll();

        $this->_em->flush();

        return $this->_currentSubscription;
    }

    private function _isMissingTimestampToken(int $tokenType): bool
    {
        switch ($tokenType) {
            case self::TIMESTAMP_TOKEN_TYPE_INCENTIVE: return is_null($this->_currentSubscription->getIncentiveProofTimestampToken());

            case self::TIMESTAMP_TOKEN_TYPE_COMMITMENT: return is_null($this->_currentSubscription->getCommitmentProofTimestampToken());

            case self::TIMESTAMP_TOKEN_TYPE_HONOR_CERTIFICATE: return is_null($this->_currentSubscription->getHonorCertificateProofTimestampToken());
        }

        return false;
    }

    private function _isTokenTypeAllowed(string $tokenType): bool
    {
        return in_array($tokenType, self::AVAILABLE_TIMESTAMP_TOKEN_TYPES);
    }

    private function _resetMissingTimestampTokens(): self
    {
        $this->_missingTimestampTokens = [];

        return $this;
    }

    private function _addMissingTimestampToken(int $tokenType): self
    {
        array_push($this->_missingTimestampTokens, $tokenType);

        return $this;
    }

    private function _removeMissingTimestampToken(int $tokenType): self
    {
        $searchedKey = array_search($tokenType, $this->_missingTimestampTokens);

        if ($searchedKey) {
            unset($this->_missingTimestampTokens[$searchedKey]);
        }

        $this->_missingTimestampTokens = array_values($this->_missingTimestampTokens);

        return $this;
    }

    private function _setSubscriptionMissingTimestampTokens(): self
    {
        $this->_setCurrentTimestampTokensResponse();

        foreach ($this->_missingTimestampTokens as $tokenType) {
            $this->_setSubscriptionTimestampToken($tokenType);
        }

        return $this;
    }

    private function _setSubscriptionTimestampToken(int $tokenType): self
    {
        switch ($tokenType) {
            case self::TIMESTAMP_TOKEN_TYPE_INCENTIVE:
                $substituteValue = 'Incentive';

                break;

            case self::TIMESTAMP_TOKEN_TYPE_COMMITMENT:
                $substituteValue = 'Commitment';

                break;

            case self::TIMESTAMP_TOKEN_TYPE_HONOR_CERTIFICATE:
                $substituteValue = 'HonorCertificate';

                break;
        }

        if (!is_null($this->_currentLogType)) {
            $this->_currentSubscription->addLog($this->_currentTimestampTokensResponse, $this->_currentLogType);
        }

        if (!is_null($this->_currentTimestampTokensResponse->getIncentiveProofTimestampToken())) {
            $timestampTokenGetter = str_replace(self::DEFAULT_TAG, $substituteValue, self::DEFAULT_TIMESTAMP_TOKEN_GETTER);
            $timestampTokenSetter = str_replace(self::DEFAULT_TAG, $substituteValue, self::DEFAULT_TIMESTAMP_TOKEN_SETTER);
            $timestampSigninTimeGetter = str_replace(self::DEFAULT_TAG, $substituteValue, self::DEFAULT_TIMESTAMP_SIGNINTIME_GETTER);
            $timestampSigninTimeSetter = str_replace(self::DEFAULT_TAG, $substituteValue, self::DEFAULT_TIMESTAMP_SIGNINTIME_SETTER);

            if (!is_null($this->_currentTimestampTokensResponse->{$timestampTokenGetter}())) {
                $this->_loggerService->log('We define the token of type '.$substituteValue);
                $this->_currentSubscription->{$timestampTokenSetter}($this->_currentTimestampTokensResponse->{$timestampTokenGetter}());
            }
            if (!is_null($this->_currentTimestampTokensResponse->{$timestampSigninTimeGetter}())) {
                $this->_loggerService->log('We define the signinTime of type '.$substituteValue);
                $this->_currentSubscription->{$timestampSigninTimeSetter}($this->_currentTimestampTokensResponse->{$timestampSigninTimeGetter}());
            }
        }

        return $this;
    }

    private function _setCurrentLogType(int $logType): self
    {
        if (in_array($logType, Log::ALLOWED_TYPES)) {
            $this->_currentLogType = $logType;
        }

        return $this;
    }

    private function _resetCurrentLogType(): self
    {
        $this->_currentLogType = null;

        return $this;
    }

    private function _resetCurrentSubscription(): self
    {
        $this->_currentSubscription = null;

        return $this;
    }

    /**
     * @param LongDistanceSubscription|ShortDistanceSubscription $subscription
     */
    private function _setCurrentSubscription($subscription): self
    {
        $this->_currentSubscription = $subscription;

        if (!is_null($this->_currentSubscription)) {
            $this->setDriver($this->_currentSubscription->getUser());
        }

        return $this;
    }

    private function _resetAll(): self
    {
        $this->_resetCurrentLogType();
        $this->_resetMissingTimestampTokens();

        return $this;
    }

    private function _setCurrentTimestampTokensResponse(): self
    {
        $this->_currentTimestampTokensResponse = $this->getDriverSubscriptionTimestamps($this->_currentSubscription->getSubscriptionId());

        return $this;
    }

    private function _setMissingCommitmentJourney(): self
    {
        if (is_null($this->_currentSubscription->getCommitmentProofJourney()) && !is_null($this->_currentTimestampTokensResponse->getJourneyId())) {
            $this->_loggerService->log('The commitment journey is missing; we will try to recover it from the moB data');
            $journeyId = $this->_currentTimestampTokensResponse->getJourneyId();

            $commitmentJourney = null;
            $id = null;

            if (preg_match('/^'.LongDistanceSubscription::COMMITMENT_PREFIX.'/', $journeyId)) {
                $id = intval(substr($journeyId, strlen(LongDistanceSubscription::COMMITMENT_PREFIX.'_')));

                switch (LongDistanceSubscription::COMMITMENT_PREFIX) {
                    case 'Proposal':
                        $commitmentJourney = $this->_em->getRepository(Proposal::class)->find($id);
                }
            } else {
                $id = intval(substr($journeyId, strlen($this->_carpoolProofPrefix)));
                $commitmentJourney = $this->_em->getRepository(CarpoolProof::class)->find($id);
            }

            if (!is_null($commitmentJourney)) {
                $this->_currentSubscription->setCommitmentProofJourney($commitmentJourney);
            } else {
                $this->_loggerService->log('The commitment journey corresponding to '.$journeyId.' was not found');
            }
        }

        return $this;
    }
}
