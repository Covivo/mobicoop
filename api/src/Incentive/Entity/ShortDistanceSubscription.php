<?php

namespace App\Incentive\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Controller\Subscription\SdSubscriptionCommit;
use App\Incentive\Controller\Subscription\SdSubscriptionGet;
use App\Incentive\Controller\Subscription\SdSubscriptionUpdate;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\Log\ShortDistanceSubscriptionLog;
use App\Incentive\Interfaces\SubscriptionDefinitionInterface;
use App\Incentive\Service\Definition\SdImproved;
use App\Incentive\Service\Definition\SdStandard;
use App\Incentive\Validator\CarpoolProofValidator;
use App\Incentive\Validator\SubscriptionValidator;
use App\Service\AddressService;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpKernel\Exception\HttpException;
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
 *          "normalization_context"={
 *              "groups"={"readSubscription","readAdminSubscription"},
 *              "enable_max_depth"=true
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "method"="GET",
 *              "path"="/eec/sd-subscriptions/{id}",
 *              "controller": SdSubscriptionGet::class,
 *              "security"="is_granted('admin_eec',object)",
 *              "normalization_context"={"groups"={"readSubscription", "readAdminSubscription"}, "skip_null_values"=false}
 *          },
 *          "commit"={
 *              "method"="PUT",
 *              "path"="/eec/sd-subscriptions/{id}/commit",
 *              "controller"=SdSubscriptionCommit::class,
 *              "security"="is_granted('admin_eec',object)",
 *              "normalization_context"={"groups"={"readSubscription", "readAdminSubscription"}, "skip_null_values"=false}
 *          },
 *          "update"={
 *              "method"="PUT",
 *              "path"="/eec/sd-subscriptions/{id}/update",
 *              "controller"=SdSubscriptionUpdate::class,
 *              "security"="is_granted('admin_eec',object)",
 *              "normalization_context"={"groups"={"readSubscription", "readAdminSubscription"}, "skip_null_values"=false}
 *          }
 *      }
 * )
 */
class ShortDistanceSubscription extends Subscription
{
    public const INITIAL_COMMITMENT_PROOF_PATH = '/api/public/upload/eec-incentives/initial-commitment-proof';
    public const HONOUR_CERTIFICATE_PATH = '/api/public/upload/eec-incentives/short-distance-subscription/honour-certificate/';

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
     */
    protected $shortDistanceJourneys;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"default": 10})
     */
    protected $maximumJourneysNumber;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"default": 3})
     */
    protected $validityPeriodDuration;

    /**
     * @var null|ShortDistanceJourney
     *
     * @ORM\OneToOne(targetEntity="\App\Incentive\Entity\ShortDistanceJourney", cascade={"persist", "remove"}, orphanRemoval=true)
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
     * @var int
     *
     * @ORM\Column(type="smallint")
     *
     * @Groups({"readSubscription"})
     */
    protected $version;

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
     * @var null|string the full street address of the user
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
    private $bonusStatus = self::BONUS_STATUS_PENDING;

    /**
     * The moBconnet HTTP request log.
     *
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity=ShortDistanceSubscriptionLog::class, mappedBy="subscription", cascade={"persist"})
     */
    private $logs;

    public function __construct(
        User $user,
        string $subscriptionId,
        SubscriptionDefinitionInterface $subscriptionDefinition
    ) {
        $this->shortDistanceJourneys = new ArrayCollection();
        $this->logs = new ArrayCollection();

        $this->setUser($user);
        $this->setSubscriptionId($subscriptionId);

        $this->setGivenName($user->getGivenName());
        $this->setFamilyName($user->getFamilyName());
        $this->setDrivingLicenceNumber($user->getDrivingLicenceNumber());
        $this->setStreetAddress();
        $this->setPostalCode();
        $this->setAddressLocality();
        $this->setTelephone($user->getTelephone());
        $this->setEmail($user->getEmail());

        $this->setVersion($subscriptionDefinition->getVersion());
        $this->setMaximumJourneysNumber($subscriptionDefinition->getMaximumJourneysNumber());
        $this->setValidityPeriodDuration($subscriptionDefinition->getValidityPeriodDuration());
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
            $journey->setCarpoolProof(null);
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
        $addressService = new AddressService($this->getUser()->getHomeAddress());

        $this->streetAddress = $addressService->getAddressWithStreetNumber();

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
        if (!SubscriptionValidator::hasBeenVerified($this)) {
            $this->setStreetAddress();
            $this->setPostalCode();
            $this->setAddressLocality();
        }

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
        $this->verificationDate = $verificationDate;

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
            && $this->getExpirationDate() < $now->sub(new \DateInterval('P'.$this->getValidityPeriodDuration().'M'));
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

    public function addLog(HttpException $exception, int $logType, array $payload = []): self
    {
        if (in_array($logType, Log::ALLOWED_TYPES)) {
            $log = new ShortDistanceSubscriptionLog($this, $exception->getStatusCode(), $exception->getMessage(), $payload, $logType);
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

    public function getCommitmentProofJourneyFromCarpoolProof(CarpoolProof $carpoolProof): ?ShortDistanceJourney
    {
        $filteredJourneys = array_values(array_filter($this->getJourneys()->toArray(), function (ShortDistanceJourney $journey) use ($carpoolProof) {
            return
                !is_null($journey->getCarpoolProof())
                && $journey->getCarpoolProof()->getId() === $carpoolProof->getId();
        }));

        return empty($filteredJourneys) ? null : $filteredJourneys[0];
    }

    public function isCommitmentJourney(?ShortDistanceJourney $journey = null): bool
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
        if (!is_null($this->getCommitmentProofJourney())) {
            $this->getCommitmentProofJourney()->setCarpoolProof(null);
            $this->removeJourney($this->getCommitmentProofJourney());
        }

        if (
            !is_null($commitmentProofJourney)
            && !$this->getJourneys()->contains($commitmentProofJourney)
        ) {
            $this->addShortDistanceJourney($commitmentProofJourney);
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

    public function reset(): self
    {
        if (!is_null($this->getCommitmentProofJourney())) {
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
        $this->setBonusStatus(self::BONUS_STATUS_PENDING);

        return $this;
    }

    /**
     * Returns if the 1st carpooling is observed.
     */
    public function getCarpoolRegistered(): ?bool
    {
        return !is_null($this->getCommitmentProofJourney())
            ? !is_null($this->getCommitmentProofJourney()->getCarpoolProof()) : null;
    }

    /**
     * Get the value of proofValidated.
     */
    public function isCommitmentJourneyValidated(): bool
    {
        return
            !is_null($this->getCommitmentProofJourney())
            && !is_null($this->getCommitmentProofJourney()->getCarpoolProof())
            && CarpoolProofValidator::isEecCompliant($this->getCommitmentProofJourney()->getCarpoolProof());
    }

    public static function getAvailableDefinitions(): array
    {
        return [
            SdImproved::class,
            SdStandard::class,
        ];
    }

    public function getType(): string
    {
        return self::SUBSCRIPTION_TYPE;
    }
}
