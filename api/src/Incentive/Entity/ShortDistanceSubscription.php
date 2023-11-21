<?php

namespace App\Incentive\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\DataProvider\Entity\MobConnect\Response\MobConnectResponse;
use App\DataProvider\Entity\MobConnect\Response\MobConnectResponseInterface;
use App\DataProvider\Entity\MobConnect\Response\MobConnectSubscriptionResponse;
use App\Incentive\Controller\SdSubscriptionCommit;
use App\Incentive\Controller\SdSubscriptionUpdate;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\Log\ShortDistanceSubscriptionLog;
use App\Incentive\Service\Manager\SubscriptionManager;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="mobconnect__short_distance_subscription")
 *
 * @ORM\HasLifecycleCallbacks
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSubscription"}, "enable_max_depth"=true},
 *      },
 *      itemOperations={
 *          "get"={
 *              "method"="GET",
 *              "path"="/eec/sd-subscription/{id}",
 *              "normalization_context"={"groups"={"readSubscription"}, "skip_null_values"=false}
 *          },
 *          "commit"={
 *              "method"="PUT",
 *              "path"="/eec/sd-subscription/{id}/commit",
 *              "controller"=SdSubscriptionCommit::class,
 *              "security"="is_granted('admin_eec',object)",
 *              "normalization_context"={"groups"={"readSubscription"}, "skip_null_values"=false}
 *          },
 *          "update"={
 *              "method"="PUT",
 *              "path"="/eec/sd-subscription/{id}/update",
 *              "controller"=SdSubscriptionUpdate::class,
 *              "security"="is_granted('admin_eec',object)",
 *              "normalization_context"={"groups"={"readSubscription"}, "skip_null_values"=false}
 *          }
 *      }
 * )
 */
class ShortDistanceSubscription extends Subscription
{
    public const INITIAL_COMMITMENT_PROOF_PATH = '/api/public/upload/eec-incentives/initial-commitment-proof';
    public const HONOUR_CERTIFICATE_PATH = '/api/public/upload/eec-incentives/short-distance-subscription/honour-certificate/';

    public const VALIDITY_PERIOD = 3;               // Period expressed in months

    public const SUBSCRIPTION_TYPE = 'short';

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     *
     * @Groups({"readSubscription"})
     */
    protected $createdAt;

    /**
     * @var ArrayCollection The short distance log associated with the user
     *
     * @ORM\OneToMany(targetEntity="\App\Incentive\Entity\ShortDistanceJourney", mappedBy="subscription", cascade={"persist"}, orphanRemoval=true)
     *
     * @Groups({"readSubscription"})
     */
    protected $shortDistanceJourneys;

    /**
     * @var null|ShortDistanceJourney
     *
     * @ORM\OneToOne(targetEntity="\App\Incentive\Entity\ShortDistanceJourney", cascade={"persist"}, orphanRemoval=true)
     *
     * @ORM\JoinColumn(nullable=true)
     *
     * @Groups({"readSubscription"})
     */
    protected $commitmentProofJourney;

    /**
     * The long distance ECC commitment proof date.
     *
     * @var null|\DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true, options={"comment": "The long distance ECC commitment proof date"})
     *
     * @Groups({"readSubscription"})
     */
    protected $commitmentProofDate;

    /**
     * The long distance EEC incentive proof timestamp token.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true, options={"comment": "The long distance EEC incentive proof timestamp"})
     *
     * @Groups({"eec-timestamps"})
     */
    protected $incentiveProofTimestampToken;

    /**
     * The long distance EEC incentive proof timestamp signing time.
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true, options={"comment": "The long distance EEC incentive proof timestamp signing time"})
     *
     * @Groups({"eec-timestamps"})
     */
    protected $incentiveProofTimestampSigningTime;

    /**
     * The long distance EEC commitment proof timestamp token.
     *
     * @var null|string
     *
     * @ORM\Column(type="text", nullable=true, options={"comment": "The long distance ECC commitment proof timestamp"})
     *
     * @Groups({"eec-timestamps"})
     */
    protected $commitmentProofTimestampToken;

    /**
     * The long distance EEC commitment proof timestamp signing time.
     *
     * @var null|\DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true, options={"comment": "The long distance EEC commitment proof timestamp signing time"})
     *
     * @Groups({"eec-timestamps"})
     */
    protected $commitmentProofTimestampSigningTime;

    /**
     * The long distance EEC honor certificate proof timestamp token.
     *
     * @var string
     *
     * @ORM\Column(type="text", nullable=true, options={"comment": "The long distance EEC honor certificate proof timestamp"})
     *
     * @Groups({"eec-timestamps"})
     */
    protected $honorCertificateProofTimestampToken;

    /**
     * The long distance EEC honor certificate proof timestamp signing time.
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true, options={"comment": "The long distance EEC honor certificate proof timestamp signing time"})
     *
     * @Groups({"eec-timestamps"})
     */
    protected $honorCertificateProofTimestampSigningTime;

    /**
     * The subscription version.
     *
     * @var string
     *
     * @ORM\Column(
     *      type="string",
     *      length=50,
     *      nullable=true,
     *      options={
     *          "comment": "The subscription version. Could be CoupPouceCEE2023 or CEEStandardMobicoop"
     *      }
     * )
     *
     * @Groups({"readSubscription"})
     */
    protected $version;

    /**
     * The subscription version status.
     *
     * @var int
     *
     * @ORM\Column(
     *      type="smallint",
     *      nullable=true,
     *      options={
     *          "comment": "The subscription version status."
     *      }
     * )
     *
     * @Groups({"readSubscription"})
     */
    protected $versionStatus;

    /**
     * @var int The user subscription ID
     *
     * @ORM\Column(name="id", type="integer")
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Groups({"readSubscription", "eec-timestamps"})
     */
    private $id;

    /**
     * @var User The user
     *
     * @ORM\OneToOne(targetEntity="\App\User\Entity\User", inversedBy="shortDistanceSubscription")
     *
     * @ORM\JoinColumn(onDelete="SET NULL", unique=true)
     */
    private $user;

    /**
     * @var string the ID of the mobConnect subscription
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"readSubscription"})
     */
    private $subscriptionId;

    /**
     * @var null|string the subscription status
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     *
     * @Groups({"readSubscription"})
     */
    private $status;

    /**
     * @var null|\DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"readSubscription"})
     */
    private $verificationDate;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"readSubscription"})
     */
    private $expirationDate;

    /**
     * @var string the mobConnect rejection reason
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rejectReason;

    /**
     * @var string a mobConnect comment for the verify operation
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @var string the first name of the user
     *
     * @ORM\Column(type="string", length=255)
     */
    private $givenName;

    /**
     * @var string the family name of the user
     *
     * @ORM\Column(type="string", length=255)
     */
    private $familyName;

    /**
     * @var string the driving licence number of the user
     *
     * @ORM\Column(type="string", length=15)
     *
     * @Groups({"readSubscription"})
     */
    private $drivingLicenceNumber;

    /**
     * @var string the full street address of the user
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $streetAddress;

    /**
     * @var string the address postal code of the user
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $postalCode;

    /**
     * @var string the address locality of the user
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $addressLocality;

    /**
     * @var string the telephone number of the user
     *
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Groups({"readSubscription"})
     */
    private $telephone;

    /**
     * @var string the email of the user
     *
     * @ORM\Column(type="string", length=255, unique=true)
     *
     * @Groups({"readSubscription"})
     */
    private $email;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     *
     * @Groups({"readSubscription"})
     */
    private $updatedAt;

    /**
     * The autogenerated honour certificate.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true, options={"comment": "The autogenerated honour certificate"})
     */
    private $honourCertificate;

    /**
     * Bonus Status of the subscription.
     *
     * @var int
     *
     * @ORM\Column(type="smallint", options={"default": 0, "comment":"Bonus Status of the EEC form"})
     *
     * @Groups({"readSubscription"})
     */
    private $bonusStatus = SubscriptionManager::BONUS_STATUS_PENDING;

    /**
     * @var bool
     *
     * @Groups({"readSubscription"})
     */
    private $hasIncentiveToken = false;

    /**
     * @var bool
     *
     * @Groups({"readSubscription"})
     */
    private $hasCommitToken = false;

    /**
     * @var bool
     *
     * @Groups({"readSubscription"})
     */
    private $hasHonorCertificateToken = false;

    /**
     * The moBconnet HTTP request log.
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity=ShortDistanceSubscriptionLog::class, mappedBy="subscription", cascade={"persist"})
     */
    private $logs;

    public function __construct(User $user, MobConnectSubscriptionResponse $mobConnectSubscriptionResponse)
    {
        $this->shortDistanceJourneys = new ArrayCollection();
        $this->logs = new ArrayCollection();

        $this->setUser($user);
        $this->setSubscriptionId($mobConnectSubscriptionResponse->getId());

        $this->setGivenName($user->getGivenName());
        $this->setFamilyName($user->getFamilyName());
        $this->setDrivingLicenceNumber($user->getDrivingLicenceNumber());
        $this->setStreetAddress();
        $this->setPostalCode();
        $this->setAddressLocality();
        $this->setTelephone($user->getTelephone());
        $this->setEmail($user->getEmail());
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime('now');
    }

    /**
     * @ORM\PrePersist
     *
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now');
    }

    /**
     * Get the cee ID.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the user.
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user The user
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function addShortDistanceJourney(ShortDistanceJourney $shortDistanceJourney): self
    {
        $this->shortDistanceJourneys[] = $shortDistanceJourney;
        $shortDistanceJourney->setSubscription($this);

        return $this;
    }

    public function removeJourney(?ShortDistanceJourney $journey): self
    {
        if (!is_null($journey)) {
            $this->shortDistanceJourneys->removeElement($journey);
        }

        return $this;
    }

    /**
     * Returns EEC compliant journeys.
     */
    public function getCompliantJourneys(): array
    {
        return is_array($this->getJourneys())
            ? array_values(array_filter($this->getJourneys(), function (ShortDistanceJourney $journey) {
                return $journey->isEECCompliant();
            }))
            : array_values(array_filter($this->getJourneys()->toArray(), function (ShortDistanceJourney $journey) {
                return $journey->isEECCompliant();
            }));
    }

    /**
     * Get the ID of the mobConnect subscription.
     */
    public function getSubscriptionId(): string
    {
        return $this->subscriptionId;
    }

    /**
     * Set the ID of the mobConnect subscription.
     *
     * @param string $subscriptionId the ID of the mobConnect subscription
     */
    public function setSubscriptionId(string $subscriptionId): self
    {
        $this->subscriptionId = $subscriptionId;

        return $this;
    }

    /**
     * Get the status of the journey.
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * Set the status of the journey.
     *
     * @param null|string $status the status of the journey
     */
    public function setStatus(?string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get the first name of the user.
     */
    public function getGivenName(): string
    {
        return $this->givenName;
    }

    /**
     * Set the first name of the user.
     */
    public function setGivenName(string $givenName): self
    {
        $this->givenName = $givenName;

        return $this;
    }

    /**
     * Get the family name of the user.
     */
    public function getFamilyName(): string
    {
        return $this->familyName;
    }

    /**
     * Set the family name of the user.
     */
    public function setFamilyName(string $familyName): self
    {
        $this->familyName = $familyName;

        return $this;
    }

    /**
     * Get the driving licence number of the user.
     */
    public function getDrivingLicenceNumber(): string
    {
        return $this->drivingLicenceNumber;
    }

    /**
     * Set the driving licence number of the user.
     */
    public function setDrivingLicenceNumber(string $drivingLicenceNumber): self
    {
        $this->drivingLicenceNumber = $drivingLicenceNumber;

        return $this;
    }

    /**
     * Get the full street address of the user.
     */
    public function getStreetAddress(): ?string
    {
        return $this->streetAddress;
    }

    /**
     * Set the full street address of the user.
     */
    public function setStreetAddress(): self
    {
        if (!is_null($this->getUser() && !is_null($this->getUser()->getHomeAddress()))) {
            $homeAddress = $this->getUser()->getHomeAddress();

            $this->streetAddress = $homeAddress->getHouseNumber().', '.$homeAddress->getStreetAddress();
        }

        return $this;
    }

    /**
     * Get the address postal code of the user.
     */
    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    /**
     * Set the address postal code of the user.
     */
    public function setPostalCode(): self
    {
        if (!is_null($this->getUser() && !is_null($this->getUser()->getHomeAddress()))) {
            $homeAddress = $this->getUser()->getHomeAddress();

            $this->postalCode = $homeAddress->getPostalCode();
        }

        return $this;
    }

    /**
     * Get the address locality of the user.
     */
    public function getAddressLocality(): ?string
    {
        return $this->addressLocality;
    }

    /**
     * Set the address locality of the user.
     */
    public function setAddressLocality(): self
    {
        if (!is_null($this->getUser() && !is_null($this->getUser()->getHomeAddress()))) {
            $homeAddress = $this->getUser()->getHomeAddress();

            $this->addressLocality = $homeAddress->getAddressLocality();
        }

        return $this;
    }

    public function updateAddress(): self
    {
        $this->setStreetAddress();
        $this->setPostalCode();
        $this->setAddressLocality();

        return $this;
    }

    /**
     * Get the telephone number of the user.
     */
    public function getTelephone(): string
    {
        return $this->telephone;
    }

    /**
     * Set the telephone number of the user.
     */
    public function setTelephone(string $telephone): self
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Get the email of the user.
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set the email of the user.
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of updatedAt.
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * Get the autogenerated honour certificate.
     */
    public function getHonourCertificate(): ?string
    {
        return self::HONOUR_CERTIFICATE_PATH.$this->honourCertificate;
    }

    /**
     * Set the autogenerated honour certificate.
     *
     * @param string $honourCertificate the autogenerated honour certificate
     */
    public function setHonourCertificate(?string $honourCertificate): self
    {
        $this->honourCertificate = $honourCertificate;

        return $this;
    }

    /**
     * Get the mobConnect rejection reason.
     */
    public function getRejectReason(): ?string
    {
        return $this->rejectReason;
    }

    /**
     * Set the mobConnect rejection reason.
     *
     * @param string $rejectReason the mobConnect rejection reason
     */
    public function setRejectReason(?string $rejectReason): self
    {
        $this->rejectReason = $rejectReason;

        return $this;
    }

    /**
     * Get a mobConnect comment for the verify operation.
     */
    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * Set a mobConnect comment for the verify operation.
     *
     * @param string $comment a mobConnect comment for the verify operation
     */
    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get the value of verificationDate.
     *
     * @return \DateTimeInterface
     */
    public function getVerificationDate(): ?\DateTime
    {
        return $this->verificationDate;
    }

    /**
     * Set the value of verificationDate.
     */
    public function setVerificationDate(?\DateTimeInterface $verificationDate = null): self
    {
        $this->verificationDate = !is_null($verificationDate) ? $verificationDate : new \DateTime('now');

        return $this;
    }

    /**
     * Get the value of expirationDate.
     *
     * @return \DateTimeInterface
     */
    public function getExpirationDate(): ?\DateTime
    {
        return $this->expirationDate;
    }

    /**
     * Set the value of expirationDate.
     */
    public function setExpirationDate(?\DateTimeInterface $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * Returns if the subscription has expired.
     */
    public function hasExpired(): bool
    {
        $now = new \DateTime('now');

        return
            !empty($this->getJourneys())
            && !is_null($this->getExpirationDate())
            && $this->getExpirationDate() < $now->sub(new \DateInterval('P'.self::VALIDITY_PERIOD.'M'));
    }

    /**
     * Get bonus Status of the journey.
     */
    public function getBonusStatus(): int
    {
        return $this->bonusStatus;
    }

    /**
     * Set bonus Status of the journey.
     *
     * @param int $bonusStatus bonus Status of the journey
     */
    public function setBonusStatus(int $bonusStatus): self
    {
        $this->bonusStatus = $bonusStatus;

        return $this;
    }

    /**
     * Set the value of commitmentProofDate.
     */
    public function setCommitmentProofDate(?\DateTimeInterface $commitmentProofDate): self
    {
        $this->commitmentProofDate = $commitmentProofDate;

        return $this;
    }

    /**
     * Get the moBconnet HTTP request log.
     */
    public function getLogs(): ArrayCollection
    {
        return $this->logs;
    }

    public function addLog(MobConnectResponseInterface $response, int $logType): self
    {
        if (
            in_array($logType, Log::ALLOWED_TYPES)
            && MobConnectResponse::isResponseErrorResponse($response)
        ) {
            $log = new ShortDistanceSubscriptionLog($this, $response->getCode(), $response->getContent(), $response->getPayload(), $logType);
            $this->logs[] = $log;
        }

        return $this;
    }

    /**
     * Get the value of commitmentProofJourney.
     */
    public function getCommitmentProofJourney(): ?ShortDistanceJourney
    {
        return $this->commitmentProofJourney;
    }

    public function isCommitmentJourney(ShortDistanceJourney $journey): bool
    {
        return
            !is_null($this->getCommitmentProofJourney())
            && $this->getCommitmentProofJourney()->getId() === $journey->getId();
    }

    /**
     * Set the value of commitmentProofJourney.
     */
    public function setCommitmentProofJourney(?ShortDistanceJourney $commitmentProofJourney): self
    {
        if (!is_null($commitmentProofJourney)) {
            if (is_array($this->getJourneys())) {
                $filteredJourneys = array_filter($this->getJourneys(), function ($journey) use ($commitmentProofJourney) {
                    return $journey->getId() === $commitmentProofJourney->getId();
                });

                if (!empty($filteredJourneys)) {
                    $this->addShortDistanceJourney($commitmentProofJourney);
                }
            }

            if (
                !is_array($this->getJourneys())
                && !$this->getJourneys()->contains($commitmentProofJourney)
            ) {
                $this->addShortDistanceJourney($commitmentProofJourney);
            }
        } else {
            $this->removeJourney($this->getCommitmentProofJourney());
        }

        $this->commitmentProofJourney = $commitmentProofJourney;

        return $this;
    }

    public function isAddressValid(): bool
    {
        return
            !is_null($this->getStreetAddress())
            && !is_null($this->getPostalCode())
            && !is_null($this->getAddressLocality());
    }

    /**
     * Returns if the conditions are required for the subscription to be verified:
     * - Can not be 'VERIFIEE'
     * - Can not be expired
     * - A full address must have been provided
     * - The associated Journeys can not be empty
     * - The commitment journey must have been defined
     * - The carpool proof associated with the commitment journey must have been defined
     * - The carpool proof associated with the commitment journey must be EEC compliant
     * - The banking identity must be validated
     * - The different timestamp tokens must be present.
     */
    public function isReadyToVerify(): bool
    {
        return
            SubscriptionManager::STATUS_VALIDATED != $this->getStatus()
            && !$this->hasExpired()
            && $this->isAddressValid()
            && !is_null($this->getUser())
            && $this->getUser()->hasBankingIdentityValidated()
            && !$this->getJourneys()->isEmpty()
            && !is_null($this->getCommitmentProofJourney())
            && !is_null($this->getCommitmentProofJourney()->getCarpoolProof())
            && $this->getCommitmentProofJourney()->getCarpoolProof()->isEecCompliant()
            && !is_null($this->getIncentiveProofTimestampToken())
            && !is_null($this->getIncentiveProofTimestampSigningTime())
            && !is_null($this->getCommitmentProofTimestampToken())
            && !is_null($this->getCommitmentProofTimestampSigningTime())
            && !is_null($this->getHonorCertificateProofTimestampToken())
            && !is_null($this->getHonorCertificateProofTimestampSigningTime());
    }

    public function reset(): self
    {
        $commitmentProofJourney = $this->getCommitmentProofJourney();

        if (!is_null($commitmentProofJourney)) {
            $this->setCommitmentProofJourney(null);
        }

        $this->setCommitmentProofDate(null);
        $this->setCommitmentProofTimestampToken(null);
        $this->setExpirationDate(null);                                 // Todo: Vérifer que cela est possible
        $this->setCommitmentProofTimestampSigningTime(null);
        $this->setHonourCertificate(null);
        $this->setHonorCertificateProofTimestampToken(null);
        $this->setHonorCertificateProofTimestampSigningTime(null);
        $this->setStatus(null);
        $this->setVerificationDate(null);

        return $this;
    }
}
