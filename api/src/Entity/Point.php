<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : travel point for a journey.
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
Class Point 
{
    /**
     * @var int The id of this point.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var int Position number of the point in the whole journey.
     * 
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $position;

    /**
     * @var boolean The point is the last point of the whole journey.
     * 
     * @Assert\NotBlank
     * @ORM\Column(type="boolean")
     * @Groups({"read","write"})
     */
    private $lastPoint;

    /**
     * @var int|null Real distance to next point in metres.
     * 
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $distanceNextReal;

    /**
     * @var int|null Flying distance to next point in metres.
     * 
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $distanceNextFly;

    /**
     * @var int|null Duration to the next point in seconds (based on real distance).
     * 
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $durationNext;

    /**
     * @var Proposal The proposal that created the point.
     * 
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Entity\Proposal", inversedBy="points")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $proposal;

    /**
     * @var Address The address of the point.
     * 
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Entity\Address", inversedBy="points", cascade={"persist","remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $address;

    /**
     * @var TravelMode|null The travel mode used from the point to the next point.
     *  
     * @ORM\ManyToOne(targetEntity="App\Entity\TravelMode")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $travelMode;

    /**
     * @var Matching[]|null The offer matchings where this point is used as a starting point.
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Matching", mappedBy="pointOfferFrom")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $matchingOffersFrom;

    /**
     * @var Matching[]|null The offer matchings where this point is used as an ending point.
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Matching", mappedBy="pointOfferTo")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $matchingOffersTo;

    /**
     * @var Matching[]|null The request matchings where this point is used as a starting point.
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Matching", mappedBy="pointRequestFrom")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $matchingRequestsFrom;

    public function __construct()
    {
        $this->matchingOffersFrom = new ArrayCollection();
        $this->matchingOffersTo = new ArrayCollection();
        $this->matchingRequestsFrom = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getLastPoint(): ?bool
    {
        return $this->lastPoint;
    }

    public function setLastPoint(bool $lastPoint): self
    {
        $this->lastPoint = $lastPoint;

        return $this;
    }

    public function getDistanceNextReal(): ?int
    {
        return $this->distanceNextReal;
    }

    public function setDistanceNextReal(?int $distanceNextReal): self
    {
        $this->distanceNextReal = $distanceNextReal;

        return $this;
    }

    public function getDistanceNextFly(): ?int
    {
        return $this->distanceNextFly;
    }

    public function setDistanceNextFly(?int $distanceNextFly): self
    {
        $this->distanceNextFly = $distanceNextFly;

        return $this;
    }

    public function getDurationNext(): ?int
    {
        return $this->durationNext;
    }

    public function setDurationNext(?int $durationNext): self
    {
        $this->durationNext = $durationNext;

        return $this;
    }

    public function getProposal(): ?Proposal
    {
        return $this->proposal;
    }

    public function setProposal(?Proposal $proposal): self
    {
        $this->proposal = $proposal;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getTravelMode(): ?TravelMode
    {
        return $this->travelMode;
    }

    public function setTravelMode(?TravelMode $travelMode): self
    {
        $this->travelMode = $travelMode;

        return $this;
    }

    /**
     * @return Collection|Matching[]
     */
    public function getMatchingOffersFrom(): Collection
    {
        return $this->matchingOffersFrom;
    }

    public function addMatchingOfferFrom(Matching $matchingOfferFrom): self
    {
        if (!$this->matchingOffersFrom->contains($matchingOfferFrom)) {
            $this->matchingOffersFrom[] = $matchingOfferFrom;
            $matchingOfferFrom->setPointOfferFrom($this);
        }

        return $this;
    }

    public function removeMatchingOfferFrom(Matching $matchingOfferFrom): self
    {
        if ($this->matchingOffersFrom->contains($matchingOfferFrom)) {
            $this->matchingOffersFrom->removeElement($matchingOfferFrom);
            // set the owning side to null (unless already changed)
            if ($matchingOfferFrom->getPointOfferFrom() === $this) {
                $matchingOfferFrom->setPointOfferFrom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Matching[]
     */
    public function getMatchingOffersTo(): Collection
    {
        return $this->matchingOffersTo;
    }

    public function addMatchingOffersTo(Matching $matchingOffersTo): self
    {
        if (!$this->matchingOffersTo->contains($matchingOffersTo)) {
            $this->matchingOffersTo[] = $matchingOffersTo;
            $matchingOffersTo->setPointOfferTo($this);
        }

        return $this;
    }

    public function removeMatchingOffersTo(Matching $matchingOffersTo): self
    {
        if ($this->matchingOffersTo->contains($matchingOffersTo)) {
            $this->matchingOffersTo->removeElement($matchingOffersTo);
            // set the owning side to null (unless already changed)
            if ($matchingOffersTo->getPointOfferTo() === $this) {
                $matchingOffersTo->setPointOfferTo(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Matching[]
     */
    public function getMatchingRequestsFrom(): Collection
    {
        return $this->matchingRequestsFrom;
    }

    public function addMatchingRequestsFrom(Matching $matchingRequestsFrom): self
    {
        if (!$this->matchingRequestsFrom->contains($matchingRequestsFrom)) {
            $this->matchingRequestsFrom[] = $matchingRequestsFrom;
            $matchingRequestsFrom->setPointRequestFrom($this);
        }

        return $this;
    }

    public function removeMatchingRequestsFrom(Matching $matchingRequestsFrom): self
    {
        if ($this->matchingRequestsFrom->contains($matchingRequestsFrom)) {
            $this->matchingRequestsFrom->removeElement($matchingRequestsFrom);
            // set the owning side to null (unless already changed)
            if ($matchingRequestsFrom->getPointRequestFrom() === $this) {
                $matchingRequestsFrom->setPointRequestFrom(null);
            }
        }

        return $this;
    }

}