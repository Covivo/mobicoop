<?php

namespace App\Incentive\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Carpool\Entity\CarpoolProof;
use App\Incentive\Entity\LongDistanceSubscription;
use App\Incentive\Entity\ShortDistanceSubscription;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A User Cee subscriptions.
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSubscription"}, "enable_max_depth"=true},
 *      },
 *      collectionOperations={
 *          "get"={
 *              "path"="/my_cee_subscriptions",
 *              "normalization_context"={"groups"={"readSubscription"}, "skip_null_values"=false},
 *              "swagger_context" = {
 *                  "tags"={"Subscription"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Subscription"},
 *                  "summary"="Not implemented"
 *              }
 *          }
 *      }
 * )
 *
 * @author Olivier Fillol <olivier.fillol@mobicoop.org>
 */
class CeeSubscriptions
{
    public const DEFAULT_ID = '999999999999';
    public const LONG_DISTANCE_MINIMUM_IN_METERS = 80000;
    public const LONG_DISTANCE_MINIMUM_PRICE_BY_KM = 0.06;

    /**
     * @var int The id of this CEE subscription
     *
     * @ApiProperty(identifier=true)
     *
     * @Groups({"readSubscription"})
     */
    private $id;

    /**
     * @var null|ShortDistanceSubscription Short distance subscription
     *
     * @Groups({"readSubscription"})
     */
    private $shortDistanceSubscription;

    /**
     * @var null|LongDistanceSubscription Long distance subscriptions
     *
     * @Groups({"readSubscription"})
     */
    private $longDistanceSubscription;

    /**
     * @var int Nb pending class C proofs
     *
     * @Groups({"readSubscription"})
     */
    private $nbPendingProofs = 0;

    /**
     * @var int Nb validated class C proofs
     *
     * @Groups({"readSubscription"})
     */
    private $nbValidatedProofs = 0;

    /**
     * @var int Nb rejected class C proofs
     *
     * @Groups({"readSubscription"})
     */
    private $nbRejectedProofs = 0;

    /**
     * @var bool Specifies whether authentication is valid
     *
     * @Groups({"readSubscription"})
     */
    private $authenticationValidity = true;

    /**
     * @var User
     */
    private $_user;

    public function __construct(User $user)
    {
        $this->_user = $user;

        $this->_build();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getNbPendingProofs(): ?int
    {
        return $this->nbPendingProofs;
    }

    public function setNbPendingProofs(int $nbPendingProofs): self
    {
        $this->nbPendingProofs = $nbPendingProofs;

        return $this;
    }

    public function getNbValidatedProofs(): ?int
    {
        return $this->nbValidatedProofs;
    }

    public function setNbValidatedProofs(int $nbValidatedProofs): self
    {
        $this->nbValidatedProofs = $nbValidatedProofs;

        return $this;
    }

    public function getNbRejectedProofs(): ?int
    {
        return $this->nbRejectedProofs;
    }

    public function setNbRejectedProofs(int $nbRejectedProofs): self
    {
        $this->nbRejectedProofs = $nbRejectedProofs;

        return $this;
    }

    /**
     * Get short distance subscription.
     */
    public function getShortDistanceSubscription(): ?ShortDistanceSubscription
    {
        return $this->shortDistanceSubscription;
    }

    /**
     * Set short distance subscription.
     *
     * @param ShortDistanceSubscription $shortDistanceSubscription Short distance subscription
     */
    public function setShortDistanceSubscription(?ShortDistanceSubscription $shortDistanceSubscription): self
    {
        $this->shortDistanceSubscription = $shortDistanceSubscription;

        return $this;
    }

    /**
     * Get long distance subscriptions.
     */
    public function getLongDistanceSubscription(): ?LongDistanceSubscription
    {
        return $this->longDistanceSubscription;
    }

    /**
     * Set long distance subscriptions.
     *
     * @param LongDistanceSubscription $longDistanceSubscription Long distance subscriptions
     */
    public function setLongDistanceSubscription(?LongDistanceSubscription $longDistanceSubscription): self
    {
        $this->longDistanceSubscription = $longDistanceSubscription;

        return $this;
    }

    public function getAuthenticationValidity(): bool
    {
        return $this->authenticationValidity;
    }

    public function setAuthenticationValidity(): self
    {
        $this->authenticationValidity = $this->_user
            && $this->_user->getMobConnectAuth()
            && $this->_user->getMobConnectAuth()->getValidity();

        return $this;
    }

    private function _computeShortDistance()
    {
        foreach ($this->_getEecEligibleProofsShortDistance() as $proof) {
            switch ($proof->getStatus()) {
                case CarpoolProof::STATUS_PENDING:
                case CarpoolProof::STATUS_SENT:$this->setNbPendingProofs($this->getNbPendingProofs() + 1);

                    break;

                case CarpoolProof::STATUS_ERROR:
                case CarpoolProof::STATUS_ACQUISITION_ERROR:
                case CarpoolProof::STATUS_NORMALIZATION_ERROR:
                case CarpoolProof::STATUS_FRAUD_ERROR:$this->setNbRejectedProofs($this->getNbRejectedProofs() + 1);

                    break;

                case CarpoolProof::STATUS_VALIDATED:$this->setNbValidatedProofs($this->getNbValidatedProofs() + 1);

                    break;
            }
        }
    }

    /**
     * Keep only the eligible proofs (for short distance only).
     */
    private function _getEecEligibleProofsShortDistance(): array
    {
        $eecEligibleProofs = [];

        foreach ($this->_user->getCarpoolProofsAsDriver() as $proof) {
            if (
                !is_null($proof->getAsk())
                && !is_null($proof->getAsk()->getMatching())
                && $proof->getAsk()->getMatching()->getCommonDistance() >= self::LONG_DISTANCE_MINIMUM_IN_METERS
            ) {
                continue;
            }

            if (CarpoolProof::TYPE_HIGH !== $proof->getType() && CarpoolProof::TYPE_UNDETERMINED_DYNAMIC !== $proof->getType()) {
                continue;
            }

            $eecEligibleProofs[] = $proof;
        }

        return $eecEligibleProofs;
    }

    private function _build()
    {
        $this->id = $this->_user->getId() ? $this->_user->getId() : self::DEFAULT_ID;

        $this->setShortDistanceSubscription($this->_user->getShortDistanceSubscription());
        $this->setLongDistanceSubscription($this->_user->getLongDistanceSubscription());

        $this->_computeShortDistance();

        $this->setAuthenticationValidity();
    }
}
