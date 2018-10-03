<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use App\Controller\ProposalPost;

/**
 * Carpooling : proposal (offer from a driver / request from a passenger).
 * 
 * @ORM\Entity(repositoryClass="App\Repository\ProposalRepository")
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get",
 *          "post"={
 *              "method"="POST",
 *              "path"="/proposals",
 *              "controller"=ProposalPost::class,
 *          }
 *      },
 *      itemOperations={"get","put","delete"}
 * )
 */
Class Proposal 
{
    CONST PROPOSAL_TYPE_OFFER = 1;
    CONST PROPOSAL_TYPE_REQUEST = 2;
    CONST JOURNEY_TYPE_ONE_WAY = 1;
    CONST JOURNEY_TYPE_OUTWARD = 2;
    CONST JOURNEY_TYPE_RETURN = 3;
    
    /**
     * @var int The id of this proposal.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var int The proposal type (1 = offer (as a driver); 2 = request (as a passenger)).
     * 
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $proposalType;

    /**
     * @var int The journey type (1 = one way trip; 2 = outward of a round trip; 3 = return of a round trip)).
     * 
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $journeyType;

    /**
     * @var \DateTimeInterface Creation date of the proposal.
     * 
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @var int|null Real distance of the full journey in metres.
     * 
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $distanceReal;

    /**
     * @var int|null Flying distance of the full journey in metres.
     * 
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $distanceFly;

    /**
     * @var int|null Estimated duration of the full journey in seconds (based on real distance).
     * 
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $duration;

    /**
     * @var string|null Main cape of the journey (N/S/E/W)
     * 
     * @ORM\Column(type="string", length=3, nullable=true)
     * @Groups({"read"})
     */
    private $cape;

    /**
     * @var Proposal[]|null Linked proposal for an offer AND request proposal (= request linked for an offer proposal, offer linked for a request proposal).
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\Proposal", inversedBy="linkedProposals")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $proposalLinked;
    
    /**
     * @var Proposal[]|null (Reverse) Linked proposal for an offer AND request proposal (= request linked for an offer proposal, offer linked for a request proposal).
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Proposal", mappedBy="proposalLinked")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $linkedProposals;
    
    /**
     * @var Proposal[]|null Linked proposal for a round trip (return or outward journey).
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Proposal", inversedBy="linkedProposalJourneys")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $proposalLinkedJourney;
    
    /**
     * @var Proposal[]|null (Reverse) Linked proposal for a round trip (return or outward journey).
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Proposal", mappedBy="proposalLinkedJourney")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $linkedProposalJourneys;
    
    /**
     * @var Proposal[]|null Original proposal if calculated proposal.
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Proposal", inversedBy="originProposals")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $proposalOrigin;
    
    /**
     * @var Proposal[]|null (Reverse) Original proposal if calculated proposal.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Proposal", mappedBy="proposalOrigin")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $originProposals;

    /**
     * @var User|null User who submits the proposal.
     * 
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="proposals")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var Point[] The points of the proposal.
     * 
     * @Assert\NotBlank
     * @ORM\OneToMany(targetEntity="App\Entity\Point", mappedBy="proposal", cascade={"persist","remove"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $points;

    /**
     * @var TravelMode[]|null The travel modes accepted if the proposal is a request.
     * 
     * @ORM\ManyToMany(targetEntity="App\Entity\TravelMode", inversedBy="proposals")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $travelModes;

    /**
     * @var Matching[]|null The matching of the proposal (if proposal is an offer).
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Matching", mappedBy="proposalOffer")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $matchingOffers;

    /**
     * @var Matching[]|null The matching of the proposal (if proposal is a request).
     * 
     * @ORM\OneToMany(targetEntity="App\Entity\Matching", mappedBy="proposalRequest")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $matchingRequests;

    /**
     * @var Criteria The criteria applied to the proposal.
     * 
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="App\Entity\Criteria", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $criteria;

    public function __construct()
    {
        $this->points = new ArrayCollection();
        $this->travelModes = new ArrayCollection();
        $this->matchingOffers = new ArrayCollection();
        $this->matchingRequests = new ArrayCollection();
        $this->linkedProposals = new ArrayCollection();
        $this->linkedProposalJourneys = new ArrayCollection();
        $this->originProposals = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getProposalType(): ?int
    {
        return $this->proposalType;
    }

    public function setProposalType(int $proposalType): self
    {
        $this->proposalType = $proposalType;

        return $this;
    }

    public function getJourneyType(): ?int
    {
        return $this->journeyType;
    }

    public function setJourneyType(int $journeyType): self
    {
        $this->journeyType = $journeyType;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getDistanceReal(): ?int
    {
        return $this->distanceReal;
    }

    public function setDistanceReal(?int $distanceReal): self
    {
        $this->distanceReal = $distanceReal;

        return $this;
    }

    public function getDistanceFly(): ?int
    {
        return $this->distanceFly;
    }

    public function setDistanceFly(?int $distanceFly): self
    {
        $this->distanceFly = $distanceFly;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(?int $duration): self
    {
        $this->duration = $duration;

        return $this;
    }

    public function getCape(): ?string
    {
        return $this->cape;
    }
    
    public function setCape(string $cape): self
    {
        $this->cape = $cape;
        
        return $this;
    }
    
    public function getProposalLinked(): ?Proposal
    {
        return $this->proposalLinked;
    }
    
    public function setProposalLinked(?Proposal $proposalLinked): self
    {
        $this->proposalLinked = $proposalLinked;
        
        return $this;
    }
    
    /**
     * @return Collection|Proposal[]
     */
    public function getLinkedProposals(): Collection
    {
        return $this->linkedProposals;
    }
    
    public function addLinkedProposal(Proposal $linkedProposal): self
    {
        if (!$this->linkedProposals->contains($linkedProposal)) {
            $this->linkedProposals[] = $linkedProposal;
            $linkedProposal->setProposalLinked($this);
        }
        
        return $this;
    }
    
    public function removeLinkedProposal(Proposal $linkedProposal): self
    {
        if ($this->linkedProposals->contains($linkedProposal)) {
            $this->linkedProposals->removeElement($linkedProposal);
            // set the owning side to null (unless already changed)
            if ($linkedProposal->getProposalLinked() === $this) {
                $linkedProposal->setProposalLinked(null);
            }
        }
        
        return $this;
    }
    
    public function getProposalLinkedJourney(): ?Proposal
    {
        return $this->proposalLinkedJourney;
    }
    
    public function setProposalLinkedJourney(?Proposal $proposalLinkedJourney): self
    {
        $this->proposalLinkedJourney = $proposalLinkedJourney;
        
        return $this;
    }
    
    /**
     * @return Collection|Proposal[]
     */
    public function getLinkedProposalJourneys(): Collection
    {
        return $this->linkedProposalJourneys;
    }
    
    public function addLinkedProposalJourney(Proposal $linkedProposalJourney): self
    {
        if (!$this->linkedProposalJourneys->contains($linkedProposalJourney)) {
            $this->linkedProposalJourneys[] = $linkedProposalJourney;
            $linkedProposalJourney->setProposalLinkedJourney($this);
        }
        
        return $this;
    }
    
    public function removeLinkedProposalJourney(Proposal $linkedProposalJourney): self
    {
        if ($this->linkedProposalJourneys->contains($linkedProposalJourney)) {
            $this->linkedProposalJourneys->removeElement($linkedProposalJourney);
            // set the owning side to null (unless already changed)
            if ($linkedProposalJourney->getProposalLinkedJourney() === $this) {
                $linkedProposalJourney->setProposalLinkedJourney(null);
            }
        }
        
        return $this;
    }
    
    public function getProposalOrigin(): ?Proposal
    {
        return $this->proposalOrigin;
    }
    
    public function setProposalOrigin(?Proposal $proposalOrigin): self
    {
        $this->proposalOrigin = $proposalOrigin;
        
        return $this;
    }
    
    /**
     * @return Collection|Proposal[]
     */
    public function getOriginProposals(): Collection
    {
        return $this->originProposals;
    }
    
    public function addOriginProposal(Proposal $originProposal): self
    {
        if (!$this->originProposals->contains($originProposal)) {
            $this->originProposals[] = $originProposal;
            $originProposal->setProposalOrigin($this);
        }
        
        return $this;
    }
    
    public function removeOriginProposal(Proposal $originProposal): self
    {
        if ($this->originProposals->contains($originProposal)) {
            $this->originProposals->removeElement($originProposal);
            // set the owning side to null (unless already changed)
            if ($originProposal->getProposalOrigin() === $this) {
                $originProposal->setProposalOrigin(null);
            }
        }
        
        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return Collection|Point[]
     */
    public function getPoints(): Collection
    {
        return $this->points;
    }

    public function addPoint(Point $point): self
    {
        if (!$this->points->contains($point)) {
            $this->points[] = $point;
            $point->setProposal($this);
        }

        return $this;
    }

    public function removePoint(Point $point): self
    {
        if ($this->points->contains($point)) {
            $this->points->removeElement($point);
            // set the owning side to null (unless already changed)
            if ($point->getProposal() === $this) {
                $point->setProposal(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|TravelMode[]
     */
    public function getTravelModes(): Collection
    {
        return $this->travelModes;
    }

    public function addTravelMode(TravelMode $travelMode): self
    {
        if (!$this->travelModes->contains($travelMode)) {
            $this->travelModes[] = $travelMode;
        }

        return $this;
    }

    public function removeTravelMode(TravelMode $travelMode): self
    {
        if ($this->travelModes->contains($travelMode)) {
            $this->travelModes->removeElement($travelMode);
        }

        return $this;
    }

    /**
     * @return Collection|Matching[]
     */
    public function getMatchingOffers(): Collection
    {
        return $this->matchingOffers;
    }

    public function addMatchingOffer(Matching $matchingOffer): self
    {
        if (!$this->matchingOffers->contains($matchingOffer)) {
            $this->matchingOffers[] = $matchingOffer;
            $matchingOffer->setProposalOffer($this);
        }

        return $this;
    }

    public function removeMatchingOffer(Matching $matchingOffer): self
    {
        if ($this->matchingOffers->contains($matchingOffer)) {
            $this->matchingOffers->removeElement($matchingOffer);
            // set the owning side to null (unless already changed)
            if ($matchingOffer->getProposalOffer() === $this) {
                $matchingOffer->setProposalOffer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Matching[]
     */
    public function getMatchingRequests(): Collection
    {
        return $this->matchingRequests;
    }

    public function addMatchingRequest(Matching $matchingRequest): self
    {
        if (!$this->matchingRequests->contains($matchingRequest)) {
            $this->matchingRequests[] = $matchingRequest;
            $matchingRequest->setProposalRequest($this);
        }

        return $this;
    }

    public function removeMatchingRequest(Matching $matchingRequest): self
    {
        if ($this->matchingRequests->contains($matchingRequest)) {
            $this->matchingRequests->removeElement($matchingRequest);
            // set the owning side to null (unless already changed)
            if ($matchingRequest->getProposalRequest() === $this) {
                $matchingRequest->setProposalRequest(null);
            }
        }

        return $this;
    }

    public function getCriteria(): ?Criteria
    {
        return $this->criteria;
    }

    public function setCriteria(Criteria $criteria): self
    {
        $this->criteria = $criteria;

        return $this;
    }
    
    // DOCTRINE EVENTS
    
    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setCreatedDate(new \Datetime());
    }

}