<?php

namespace App\Incentive\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Carpool\Entity\Ask;
use App\Carpool\Entity\Proposal;
use App\Incentive\Controller\Subscription\LdSubscriptionCommit;
use App\Incentive\Controller\Subscription\LdSubscriptionGet;
use App\Incentive\Controller\Subscription\LdSubscriptionUpdate;
use App\Incentive\Entity\Log\Log;
use App\Incentive\Entity\Log\LongDistanceSubscriptionLog;
use App\Incentive\Interfaces\SubscriptionDefinitionInterface;
use App\Incentive\Service\Definition\LdImproved;
use App\Incentive\Service\Definition\LdStandard;
use App\Service\AddressService;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="mobconnect__long_distance_subscription")
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
 *              "path"="/eec/ld-subscriptions/{id}",
 *              "controller": LdSubscriptionGet::class,
 *              "security"="is_granted('admin_eec',object)",
 *              "normalization_context"={"groups"={"readSubscription", "readAdminSubscription"}, "skip_null_values"=false}
 *          },
 *          "commit"={
 *              "method"="PUT",
 *              "path"="/eec/ld-subscriptions/{id}/commit",
 *              "controller"=LdSubscriptionCommit::class,
 *              "security"="is_granted('admin_eec',object)",
 *              "normalization_context"={"groups"={"readSubscription", "readAdminSubscription"}, "skip_null_values"=false}
 *          },
 *          "update"={
 *              "method"="PUT",
 *              "path"="/eec/ld-subscriptions/{id}/update",
 *              "controller"=LdSubscriptionUpdate::class,
 *              "security"="is_granted('admin_eec',object)",
 *              "normalization_context"={"groups"={"readSubscription", "readAdminSubscription"}, "skip_null_values"=false}
 *          }
 *      }
 * )
 */
class LongDistanceSubscription extends Subscription
{
    public const INITIAL_COMMITMENT_PROOF_PATH = '/api/public/upload/eec-incentives/initial-commitment-proof';
    public const HONOUR_CERTIFICATE_PATH = '/api/public/upload/eec-incentives/long-distance-subscription/honour-certificate/';

    public const COMMITMENT_PREFIX = 'Proposal_';

    public const SUBSCRIPTION_TYPE = 'long';

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true, options={"default"="CURRENT_TIMESTAMP"})
     *
     * @Groups({"readSubscription"})
     */
    protected $createdAt;

    /**
     * @var ArrayCollection The long distance log associated with the user
     *
     * @ORM\OneToMany(targetEntity="\App\Incentive\Entity\LongDistanceJourney", mappedBy="subscription", cascade={"persist"}, orphanRemoval=true)
     */
    protected $longDistanceJourneys;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"default": 3})
     */
    protected $maximumJourneysNumber;

    /**
     * @var int
     *
     * @ORM\Column(type="smallint", options={"default": 3})
     */
    protected $validityPeriodDuration;

    /**
     * @var LongDistanceJourney
     *
     * @ORM\OneToOne(targetEntity="\App\Incentive\Entity\LongDistanceJourney", cascade={"persist", "remove"}, orphanRemoval=true)
     *
     * @ORM\JoinColumn(nullable=true)
     *
     * @Groups({"readSubscription"})
     */
    protected $commitmentProofJourney;

    /**
     * The long distance ECC commitment proof date.
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(type="datetime", nullable=true, options={"comment": "The long distance ECC commitment proof date"})
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
     * @var string
     *
     * @ORM\Column(type="text", nullable=true, options={"comment": "The long distance ECC commitment proof timestamp"})
     *
     * @Groups({"eec-timestamps"})
     */
    protected $commitmentProofTimestampToken;

    /**
     * The long distance EEC commitment proof timestamp signing time.
     *
     * @var \DateTimeInterface
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
     * @Groups({"readSubscription","eec-timestamps"})
     */
    private $id;

    /**
     * @var User The user
     *
     * @ORM\OneToOne(targetEntity="\App\User\Entity\User", inversedBy="longDistanceSubscription")
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
     * @var string the subscription status
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     *
     * @Groups({"readSubscription"})
     */
    private $status;

    /**
     * @var \DateTimeInterface
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
     * @ORM\OneToMany(targetEntity=LongDistanceSubscriptionLog::class, mappedBy="subscription", cascade={"persist"})
     */
    private $logs;

    public function __construct(
        User $user,
        string $subscriptionId,
        SubscriptionDefinitionInterface $subscriptionDefinition
    ) {
        $this->longDistanceJourneys = new ArrayCollection();
        $this->logs = new ArrayCollection();

        $this->setUser($user);
        $this->setSubscriptionId($subscriptionId);

        $this->setGivenName($user->getGivenName());
        $this->setFamilyName($user->getFamilyName());
        $this->setDrivingLicenceNumber($user->getDrivingLicenceNumber());
        $this->setStreetAddress();
        $this->setAddressLocality();
        $this->setPostalCode();
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

    public function addLongDistanceJourney(LongDistanceJourney $longDistanceJourney): self
    {
        $this->longDistanceJourneys[] = $longDistanceJourney;
        $longDistanceJourney->setSubscription($this);

        if ($this->getMaximumJourneysNumber() === $this->getJourneysNumber()) {
            $this->setBonusStatus(self::BONUS_STATUS_PENDING);
        }

        return $this;
    }

    public function removeJourney(?LongDistanceJourney $journey): self
    {
        if (!is_null($journey)) {
            $journey->setInitialProposal(null);
            $journey->setCarpoolItem(null);
            $journey->setCarpoolPayment(null);

            $this->longDistanceJourneys->removeElement($journey);
        }

        return $this;
    }

    public function getJourneysNumber(): int
    {
        return is_array($this->getJourneys()) ? count($this->getJourneys()) : count($this->getJourneys()->toArray());
    }

    /**
     * Returns EEC compliant journeys.
     */
    public function getCompliantJourneys(): array
    {
        return is_array($this->getJourneys())
            ? array_values(array_filter($this->getJourneys(), function (LongDistanceJourney $journey) {
                return $journey->isEECCompliantForDisplay();
            }))
            : array_values(array_filter($this->getJourneys()->toArray(), function (LongDistanceJourney $journey) {
                return $journey->isEECCompliantForDisplay();
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
     * @param string $status the status of the journey
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
     * Get bonus Status of the subscription.
     */
    public function getBonusStatus(): int
    {
        return $this->bonusStatus;
    }

    /**
     * Set bonus Status of the subscription.
     *
     * @param int $bonusStatus bonus Status of the subscription
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
            $log = new LongDistanceSubscriptionLog($this, $exception->getStatusCode(), $exception->getMessage(), $payload, $logType);
            $this->logs[] = $log;
        }

        return $this;
    }

    /**
     * Get the autogenerated initial commintment proof.
     */
    public function getCommitmentProofJourney(): ?LongDistanceJourney
    {
        return $this->commitmentProofJourney;
    }

    public function getCommitmentProofJourneyFromInitialProposal(Proposal $initialProposal): ?LongDistanceJourney
    {
        $filteredJourneys = array_values(array_filter($this->getJourneys()->toArray(), function (LongDistanceJourney $journey) use ($initialProposal) {
            return
                !is_null($journey->getInitialProposal())
                && $journey->getInitialProposal()->getId() === $initialProposal->getId();
        }));

        return empty($filteredJourneys) ? null : $filteredJourneys[0];
    }

    public function isCommitmentJourney(LongDistanceJourney $journey): bool
    {
        return
            !is_null($this->getCommitmentProofJourney())
            && $this->getCommitmentProofJourney()->getId() === $journey->getId();
    }

    /**
     * Set the autogenerated initial commitment proof.
     *
     * @param LongDistanceJourney $commitmentProofJourney the autogenerated initial commitment proof
     */
    public function setCommitmentProofJourney(?LongDistanceJourney $commitmentProofJourney): self
    {
        if (!is_null($commitmentProofJourney)) {
            if (is_array($this->getJourneys())) {
                $filteredJourneys = array_filter($this->getJourneys(), function ($journey) use ($commitmentProofJourney) {
                    return $journey->getId() === $commitmentProofJourney->getId();
                });

                if (empty($filteredJourneys)) {
                    $this->addLongDistanceJourney($commitmentProofJourney);
                }
            }

            if (
                !is_array($this->getJourneys())
                && !$this->getJourneys()->contains($commitmentProofJourney)
            ) {
                $this->addLongDistanceJourney($commitmentProofJourney);
            }
        } else {
            $this->getCommitmentProofJourney()->setCarpoolItem(null);
            $this->getCommitmentProofJourney()->setCarpoolPayment(null);
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
     * Get indicates if the 1st carpooling has been published.
     */
    public function getFirstCarpoolPublished(): ?bool
    {
        return
            is_null($this->getCommitmentProofJourney())
            ? null
            : !is_null($this->getCommitmentProofJourney()) && $this->hasCommitToken();
    }

    /**
     * Get indicates if the 1st carpooling has been published.
     */
    public function isFirstCarpoolPublished(): ?bool
    {
        return $this->getFirstCarpoolPublished();
    }

    /**
     * Returns if the 1st carpooling is observed.
     * We determine if there is at least one carpooling request made and that it has already taken place:
     * - If there is none we will not return anything,
     * - If there was any, we will determine if there is at least one proof of carpooling present and that the latter is awaiting validation by the RPC:
     *   - If yes we return true,
     *   - If not we return false.
     */
    public function getCarpoolRegistered(): ?bool
    {
        if (
            is_null($this->getCommitmentProofJourney())                                 // The subscription has not yet been validated
            || (                                                                        // The subscription has been validated but there is no carpoolProof
                !is_null($this->getCommitmentProofJourney())
                && is_null($this->getCommitmentProofJourney()->getInitialProposal())
                && is_null($this->getCommitmentProofJourney()->getInitialProposal()->getMatchingOffers())
                && empty($this->getCommitmentProofJourney()->getInitialProposal()->getMatchingOffers())
            )
        ) {
            return null;
        }

        $asks = [];
        $carpoolProofs = [];

        foreach ($this->getCommitmentProofJourney()->getInitialProposal()->getMatchingOffers() as $key => $matching) {
            $passenger = !is_null($matching->getProposalRequest()) && !is_null($matching->getProposalRequest()->getUser())
                ? $matching->getProposalRequest()->getUser() : null;

            if (is_null($passenger)) {
                continue;
            }

            foreach ($matching->getAsks() as $key => $ask) {
                if (
                    (
                        Ask::STATUS_ACCEPTED_AS_DRIVER === $ask->getStatus()
                        || Ask::STATUS_ACCEPTED_AS_PASSENGER === $ask->getStatus()
                    ) && (
                        !is_null($ask->getCriteria())
                        && $ask->getCriteria()->getFromDate() < new \DateTime('now')
                    )
                ) {
                    array_push($asks, $ask);

                    foreach ($ask->getCarpoolProofs() as $key => $carpoolProof) {
                        if ($carpoolProof->isInProgressEecCompliant()) {
                            array_push($carpoolProofs, $carpoolProof);
                        }
                    }
                }
            }
        }

        if (empty($asks)) {
            return null;
        }

        return !empty($carpoolProofs);
    }

    public function isCommitmentJourneyPayedAndValidated(): bool
    {
        return
            !is_null($this->getCommitmentProofJourney())
            && !is_null($this->getCommitmentProofJourney()->getCarpoolItem())
            && !is_null($this->getCommitmentProofJourney()->getCarpoolItem()->getCarpoolProof())
            && $this->getCommitmentProofJourney()->getCarpoolItem()->isEECompliant()
            && $this->getCommitmentProofJourney()->getCarpoolItem()->getCarpoolProof()->isEECCompliant();
    }

    public static function getAvailableDefinitions(): array
    {
        return [
            LdImproved::class,
            LdStandard::class,
        ];
    }

    public function getType(): string
    {
        return self::SUBSCRIPTION_TYPE;
    }
}
