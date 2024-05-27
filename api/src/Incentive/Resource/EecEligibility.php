<?php

namespace App\Incentive\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * User eligibility for subscription to EEC aids.
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"eecEligibility"}, "enable_max_depth"=true},
 *      },
 *      collectionOperations={
 *          "get"={
 *              "method"="get",
 *              "path"="/my_eec_eligibility",
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
class EecEligibility
{
    public const DEFAULT_ID = '999999999999';
    public const LONG_DISTANCE_ELIGIBILITY_THRESHOLD = 0;
    public const SHORT_DISTANCE_ELIGIBILITY_THRESHOLD = 0;

    /**
     * @var int
     *
     * @Groups({"eecEligibility"})
     */
    private $longDistanceJourneysNumber = 0;

    /**
     * @var bool
     *
     * @Groups({"eecEligibility"})
     */
    private $longDistanceEligibility = false;

    /**
     * @var int
     *
     * @Groups({"eecEligibility"})
     */
    private $shortDistanceJourneysNumber = 0;

    /**
     * @var bool
     *
     * @Groups({"eecEligibility"})
     */
    private $shortDistanceEligibility = false;

    /**
     * @var User
     *
     * @Groups({"eecEligibility"})
     */
    private $user;

    /**
     * @var int The id of this CEE eligibility
     *
     * @ApiProperty(identifier=true)
     *
     * @Groups({"eecEligibility"})
     */
    private $id;

    /**
     * @var int
     *
     * @Groups({"eecEligibility"})
     */
    private $longDistanceDrivingLicenceNumberDoublon = 0;

    /**
     * @var int
     *
     * @Groups({"eecEligibility"})
     */
    private $shortDistanceDrivingLicenceNumberDoublon = 0;

    /**
     * @var int
     *
     * @Groups({"eecEligibility"})
     */
    private $longDistancePhoneDoublon = 0;

    /**
     * @var int
     *
     * @Groups({"eecEligibility"})
     */
    private $shortDistancePhoneDoublon = 0;

    /**
     * @var bool
     *
     * @Groups({"eecEligibility"})
     */
    private $fullAddress = false;

    public function __construct(User $user, $id = null)
    {
        $this->setUser($user);
        $this->id = $id ? $id : self::DEFAULT_ID;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the value of longDistance.
     */
    public function getLongDistanceJourneyNumber(): int
    {
        return $this->longDistanceJourneysNumber;
    }

    /**
     * Set the value of longDistance.
     */
    public function setLongDistanceJourneysNumber(int $longDistanceJourneysNumber): self
    {
        $this->longDistanceJourneysNumber = $longDistanceJourneysNumber;

        $this->setLongDistanceEligibility();

        return $this;
    }

    /**
     * Get the value of longDistanceEligibility.
     */
    public function getLongDistanceEligibility(): bool
    {
        return $this->longDistanceEligibility;
    }

    /**
     * Set the value of longDistanceEligibility.
     */
    public function setLongDistanceEligibility(): self
    {
        $this->longDistanceEligibility =
            $this->getLongDistanceJourneyNumber() <= self::LONG_DISTANCE_ELIGIBILITY_THRESHOLD
            && 0 === $this->getLongDistanceDrivingLicenceNumberDoublon()
            && 0 === $this->getLongDistancePhoneDoublon();

        return $this;
    }

    /**
     * Get the value of shortDistance.
     */
    public function getShortDistanceJourneysNumber(): int
    {
        return $this->shortDistanceJourneysNumber;
    }

    /**
     * Set the value of shortDistance.
     */
    public function setShortDistanceJourneysNumber(int $shortDistanceJourneysNumber): self
    {
        $this->shortDistanceJourneysNumber = $shortDistanceJourneysNumber;

        $this->setShortDistanceEligibility();

        return $this;
    }

    /**
     * Get the value of shortDistanceEligibility.
     */
    public function getShortDistanceEligibility(): bool
    {
        return $this->shortDistanceEligibility;
    }

    /**
     * Set the value of shortDistanceEligibility.
     */
    public function setShortDistanceEligibility(): self
    {
        $this->shortDistanceEligibility =
            $this->getShortDistanceJourneysNumber() <= self::SHORT_DISTANCE_ELIGIBILITY_THRESHOLD
            && 0 === $this->getShortDistanceDrivingLicenceNumberDoublon()
            && 0 === $this->getShortDistancePhoneDoublon();

        return $this;
    }

    /**
     * Get the value of user.
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * Set the value of user.
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get the value of longDistanceDrivingLicenceNumberDoublon.
     */
    public function getLongDistanceDrivingLicenceNumberDoublon(): int
    {
        return $this->longDistanceDrivingLicenceNumberDoublon;
    }

    /**
     * Set the value of longDistanceDrivingLicenceNumberDoublon.
     */
    public function setLongDistanceDrivingLicenceNumberDoublon(int $longDistanceDrivingLicenceNumberDoublon): self
    {
        $this->longDistanceDrivingLicenceNumberDoublon = $longDistanceDrivingLicenceNumberDoublon;
        $this->setLongDistanceEligibility();

        return $this;
    }

    /**
     * Get the value of shortDistanceDrivingLicenceNumberDoublon.
     */
    public function getShortDistanceDrivingLicenceNumberDoublon(): int
    {
        return $this->shortDistanceDrivingLicenceNumberDoublon;
    }

    /**
     * Set the value of shortDistanceDrivingLicenceNumberDoublon.
     */
    public function setShortDistanceDrivingLicenceNumberDoublon(int $shortDistanceDrivingLicenceNumberDoublon): self
    {
        $this->shortDistanceDrivingLicenceNumberDoublon = $shortDistanceDrivingLicenceNumberDoublon;
        $this->setShortDistanceEligibility();

        return $this;
    }

    /**
     * Get the value of longDistancePhoneDoublon.
     */
    public function getLongDistancePhoneDoublon(): int
    {
        return $this->longDistancePhoneDoublon;
    }

    /**
     * Set the value of longDistancePhoneDoublon.
     */
    public function setLongDistancePhoneDoublon(int $longDistancePhoneDoublon): self
    {
        $this->longDistancePhoneDoublon = $longDistancePhoneDoublon;
        $this->setLongDistanceEligibility();

        return $this;
    }

    /**
     * Get the value of shortDistancePhoneDoublon.
     */
    public function getShortDistancePhoneDoublon(): int
    {
        return $this->shortDistancePhoneDoublon;
    }

    /**
     * Set the value of shortDistancePhoneDoublon.
     */
    public function setShortDistancePhoneDoublon(int $shortDistancePhoneDoublon): self
    {
        $this->shortDistancePhoneDoublon = $shortDistancePhoneDoublon;
        $this->setShortDistanceEligibility();

        return $this;
    }

    public function hasFulladdress(): bool
    {
        return $this->fullAddress;
    }

    public function setFulladdress(bool $fullAddress): self
    {
        $this->fullAddress = $fullAddress;

        return $this;
    }
}
