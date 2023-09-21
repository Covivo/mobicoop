<?php

namespace App\DataProvider\Entity\MobConnect\Response;

use App\Incentive\Entity\Subscription\SpecificFields;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class MobConnectSubscriptionTimestampsResponse extends MobConnectResponse
{
    public const TYPE_SUBSCRIPTION = 0;
    public const TYPE_COMMITMENT = 1;
    public const TYPE_HONOR_CERTIFICATE = 2;

    public const ALLOWED_TOKEN_TYPES = [
        self::TYPE_SUBSCRIPTION,
        self::TYPE_COMMITMENT,
        self::TYPE_HONOR_CERTIFICATE,
    ];

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

    /**
     * @var null|string
     */
    private $journeyId;

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
     * Get the value of commitmentProofTimestampSigningTime.
     */
    public function getCommitmentProofTimestampSigningTime(): ?\DateTime
    {
        return $this->commitmentProofTimestampSigningTime;
    }

    /**
     * Get the value of honorCertificateProofTimestampToken.
     */
    public function getHonorCertificateProofTimestampToken(): ?string
    {
        return $this->honorCertificateProofTimestampToken;
    }

    /**
     * Get the value of honorCertificateProofTimestampSigningTime.
     */
    public function getHonorCertificateProofTimestampSigningTime(): ?\DateTime
    {
        return $this->honorCertificateProofTimestampSigningTime;
    }

    /**
     * Get the value of incentiveProofTimestampToken.
     */
    public function getIncentiveProofTimestampToken(): ?string
    {
        return $this->incentiveProofTimestampToken;
    }

    /**
     * Get the value of incentiveProofTimestampSigningTime.
     */
    public function getIncentiveProofTimestampSigningTime(): ?\DateTime
    {
        return $this->incentiveProofTimestampSigningTime;
    }

    /**
     * Get the value of journeyId.
     */
    public function getJourneyId(): ?string
    {
        return $this->journeyId;
    }

    /**
     * Set the value of journeyId.
     */
    public function setJourneyId(?string $journeyId): self
    {
        $this->journeyId = $journeyId;

        return $this;
    }

    /**
     * Set the value of commitmentProofTimestampToken.
     */
    private function _setCommitmentProofTimestampToken(string $commitmentProofTimestampToken): self
    {
        $this->commitmentProofTimestampToken = $commitmentProofTimestampToken;

        return $this;
    }

    /**
     * Set the value of commitmentProofTimestampSigningTime.
     */
    private function _setCommitmentProofTimestampSigningTime(\DateTime $commitmentProofTimestampSigningTime): self
    {
        $this->commitmentProofTimestampSigningTime = $commitmentProofTimestampSigningTime;

        return $this;
    }

    /**
     * Set the value of honorCertificateProofTimestampToken.
     */
    private function _setHonorCertificateProofTimestampToken(string $honorCertificateProofTimestampToken): self
    {
        $this->honorCertificateProofTimestampToken = $honorCertificateProofTimestampToken;

        return $this;
    }

    /**
     * Set the value of honorCertificateProofTimestampSigningTime.
     */
    private function _setHonorCertificateProofTimestampSigningTime(\DateTime $honorCertificateProofTimestampSigningTime): self
    {
        $this->honorCertificateProofTimestampSigningTime = $honorCertificateProofTimestampSigningTime;

        return $this;
    }

    /**
     * Set the value of incentiveProofTimestampToken.
     */
    private function _setIncentiveProofTimestampToken(string $incentiveProofTimestampToken): self
    {
        $this->incentiveProofTimestampToken = $incentiveProofTimestampToken;

        return $this;
    }

    /**
     * Set the value of incentiveProofTimestampSigningTime.
     */
    private function _setIncentiveProofTimestampSigningTime(\DateTime $incentiveProofTimestampSigningTime): self
    {
        $this->incentiveProofTimestampSigningTime = $incentiveProofTimestampSigningTime;

        return $this;
    }

    private function _buildObject()
    {
        if (!in_array($this->getCode(), self::ERROR_CODES) && !is_null($this->_content)) {
            $subscriptionTimestamps = $this->getTimestampTokensGroup(self::TYPE_SUBSCRIPTION);
            $commitmentTimestamps = $this->getTimestampTokensGroup(self::TYPE_COMMITMENT);
            $honorCertificateTimestamps = $this->getTimestampTokensGroup(self::TYPE_HONOR_CERTIFICATE);

            if (!empty($subscriptionTimestamps)) {
                $subscriptionTimestamp = end($subscriptionTimestamps);

                $this->_setIncentiveProofTimestampToken($subscriptionTimestamp->timestampToken);
                $this->_setIncentiveProofTimestampSigningTime(new \DateTime($subscriptionTimestamp->signingTime));
            }

            if (!empty($commitmentTimestamps)) {
                $commitmentTimestamp = end($commitmentTimestamps);

                $this->_setCommitmentProofTimestampToken($commitmentTimestamp->timestampToken);
                $this->_setCommitmentProofTimestampSigningTime(new \DateTime($commitmentTimestamp->signingTime));
            }

            if (!empty($honorCertificateTimestamps)) {
                $honorCertificateTimestamp = end($honorCertificateTimestamps);

                $this->_setHonorCertificateProofTimestampToken($honorCertificateTimestamp->timestampToken);
                $this->_setHonorCertificateProofTimestampSigningTime(new \DateTime($honorCertificateTimestamp->signingTime));
            }
        }
    }

    /**
     * Returns an array of timestamp tokens matching the requested type. Allowed types are defined by the ALLOWED_TOKEN_TYPES constant.
     */
    private function getTimestampTokensGroup(int $token_type): array
    {
        if (!in_array($token_type, self::ALLOWED_TOKEN_TYPES)) {
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'The token type parameter is not allowed');
        }

        return is_array($this->getContent()) && !empty($this->getContent())
            ? array_values(array_filter($this->getContent(), function ($timestamp) use ($token_type) {
                $specificFields = !is_null($timestamp->subscription) && !is_null($timestamp->subscription->specificFields)
                    ? $timestamp->subscription->specificFields
                    : null;

                switch ($token_type) {
                    case self::TYPE_SUBSCRIPTION:
                        return
                            !is_null($specificFields)
                            && !property_exists($specificFields, SpecificFields::JOURNEY_ID)
                            && !property_exists($specificFields, SpecificFields::JOURNEY_START_DATE)
                            && !property_exists($specificFields, SpecificFields::HONOR_CERTIFICATE);

                    case self::TYPE_COMMITMENT:
                        return
                            !is_null($specificFields)
                            && property_exists($specificFields, SpecificFields::JOURNEY_ID)
                            && property_exists($specificFields, SpecificFields::JOURNEY_START_DATE)
                            && !property_exists($specificFields, SpecificFields::HONOR_CERTIFICATE);

                    case self::TYPE_HONOR_CERTIFICATE:
                        return
                            !is_null($specificFields)
                            && property_exists($specificFields, SpecificFields::JOURNEY_ID)
                            && property_exists($specificFields, SpecificFields::JOURNEY_START_DATE)
                            && property_exists($specificFields, SpecificFields::HONOR_CERTIFICATE);

                    default:
                        throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, 'The use case was not planned');
                }
            }))
            : [];
    }
}
