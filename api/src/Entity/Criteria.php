<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : criteria (restriction for an offer / selection for a request).
 * 
 * @ORM\Entity
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 */
Class Criteria 
{
    CONST FREQUENCY_PUNCTUAL = 1;
    CONST FREQUENCY_REGULAR = 2;
    
    /**
     * @var int The id of this criteria.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var int The proposal frequency (1 = punctual; 2 = regular).
     * 
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $frequency;

    /**
     * @var int The number of available seats.
     * 
     * @Assert\NotBlank
     * @ORM\Column(type="integer")
     * @Groups({"read","write"})
     */
    private $seats;

    /**
     * @var \DateTimeInterface The starting date (= proposal date if punctual).
     * 
     * @Assert\NotBlank
     * @Assert\Date()
     * @ORM\Column(type="date")
     * @Groups({"read","write"})
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface|null The starting time.
     * 
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write"})
     */
    private $fromTime;

    /**
     * @var \DateTimeInterface|null The end date if regular proposal.
     * 
     * @Assert\Date()
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"read","write"})
     */
    private $toDate;

    /**
     * @var boolean|null The proposal is available on mondays (if regular).
     * 
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $monCheck;

    /**
     * @var boolean|null The proposal is available on tuesdays (if regular).
     * 
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $tueCheck;

    /**
     * @var boolean|null The proposal is available on wednesdays (if regular).
     * 
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $wedCheck;

    /**
     * @var boolean|null The proposal is available on thursdays (if regular).
     * 
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $thuCheck;

    /**
     * @var boolean|null The proposal is available on fridays (if regular).
     * 
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $friCheck;

    /**
     * @var boolean|null The proposal is available on saturdays (if regular).
     * 
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $satCheck;

    /**
     * @var boolean|null The proposal is available on sundays (if regular).
     * 
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $sunCheck;

    /**
     * @var \DateTimeInterface|null Mondays starting time (if regular).
     * 
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write"})
     */
    private $monTime;

    /**
     * @var \DateTimeInterface|null Tuesdays starting time (if regular).
     * 
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write"})
     */
    private $tueTime;

    /**
     * @var \DateTimeInterface|null Wednesdays starting time (if regular).
     * 
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write"})
     */
    private $wedTime;

    /**
     * @var \DateTimeInterface|null Thursdays starting time (if regular).
     * 
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write"})
     */
    private $thuTime;

    /**
     * @var \DateTimeInterface|null Fridays starting time (if regular).
     * 
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write"})
     */
    private $friTime;

    /**
     * @var \DateTimeInterface|null Saturdays starting time (if regular).
     * 
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write"})
     */
    private $satTime;

    /**
     * @var \DateTimeInterface|null Sunadays starting time (if regular).
     * 
     * @Assert\Time()
     * @ORM\Column(type="time", nullable=true)
     * @Groups({"read","write"})
     */
    private $sunTime;

    /**
     * @var int Accepted margin for starting time in seconds.
     * 
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read","write"})
     */
    private $marginTime;
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getSeats(): ?int
    {
        return $this->seats;
    }

    public function setSeats(int $seats): self
    {
        $this->seats = $seats;

        return $this;
    }

    public function getFromDate(): ?\DateTimeInterface
    {
        return $this->fromDate;
    }

    public function setFromDate(\DateTimeInterface $fromDate): self
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    public function getFromTime(): ?\DateTimeInterface
    {
        return $this->fromTime;
    }

    public function setFromTime(?\DateTimeInterface $fromTime): self
    {
        $this->fromTime = $fromTime;

        return $this;
    }

    public function getToDate(): ?\DateTimeInterface
    {
        return $this->toDate;
    }

    public function setToDate(?\DateTimeInterface $toDate): self
    {
        $this->toDate = $toDate;

        return $this;
    }

    public function getMonCheck(): ?bool
    {
        return $this->monCheck;
    }

    public function setMonCheck(?bool $monCheck): self
    {
        $this->monCheck = $monCheck;

        return $this;
    }

    public function getTueCheck(): ?bool
    {
        return $this->tueCheck;
    }

    public function setTueCheck(?bool $tueCheck): self
    {
        $this->tueCheck = $tueCheck;

        return $this;
    }

    public function getWedCheck(): ?bool
    {
        return $this->wedCheck;
    }

    public function setWedCheck(?bool $wedCheck): self
    {
        $this->wedCheck = $wedCheck;

        return $this;
    }

    public function getThuCheck(): ?bool
    {
        return $this->thuCheck;
    }

    public function setThuCheck(?bool $thuCheck): self
    {
        $this->thuCheck = $thuCheck;

        return $this;
    }

    public function getFriCheck(): ?bool
    {
        return $this->friCheck;
    }

    public function setFriCheck(?bool $friCheck): self
    {
        $this->friCheck = $friCheck;

        return $this;
    }

    public function getSatCheck(): ?bool
    {
        return $this->satCheck;
    }

    public function setSatCheck(?bool $satCheck): self
    {
        $this->satCheck = $satCheck;

        return $this;
    }

    public function getSunCheck(): ?bool
    {
        return $this->sunCheck;
    }

    public function setSunCheck(?bool $sunCheck): self
    {
        $this->sunCheck = $sunCheck;

        return $this;
    }

    public function getMonTime(): ?\DateTimeInterface
    {
        return $this->monTime;
    }

    public function setMonTime(?\DateTimeInterface $monTime): self
    {
        $this->monTime = $monTime;

        return $this;
    }

    public function getTueTime(): ?\DateTimeInterface
    {
        return $this->tueTime;
    }

    public function setTueTime(?\DateTimeInterface $tueTime): self
    {
        $this->tueTime = $tueTime;

        return $this;
    }

    public function getWedTime(): ?\DateTimeInterface
    {
        return $this->wedTime;
    }

    public function setWedTime(?\DateTimeInterface $wedTime): self
    {
        $this->wedTime = $wedTime;

        return $this;
    }

    public function getThuTime(): ?\DateTimeInterface
    {
        return $this->thuTime;
    }

    public function setThuTime(?\DateTimeInterface $thuTime): self
    {
        $this->thuTime = $thuTime;

        return $this;
    }

    public function getFriTime(): ?\DateTimeInterface
    {
        return $this->friTime;
    }

    public function setFriTime(?\DateTimeInterface $friTime): self
    {
        $this->friTime = $friTime;

        return $this;
    }

    public function getSatTime(): ?\DateTimeInterface
    {
        return $this->satTime;
    }

    public function setSatTime(?\DateTimeInterface $satTime): self
    {
        $this->satTime = $satTime;

        return $this;
    }

    public function getSunTime(): ?\DateTimeInterface
    {
        return $this->sunTime;
    }
    
    public function setSunTime(?\DateTimeInterface $sunTime): self
    {
        $this->sunTime = $sunTime;
        
        return $this;
    }

    public function getMarginTime(): ?int
    {
        return $this->marginTime;
    }

    public function setMarginTime(?int $marginTime): self
    {
        $this->marginTime = $marginTime;

        return $this;
    }
    
}