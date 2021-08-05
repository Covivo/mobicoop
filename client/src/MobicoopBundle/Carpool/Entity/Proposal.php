<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Entity;

use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\IndividualStop;
use App\Carpool\Entity\Matching;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Mobicoop\Bundle\MobicoopBundle\Event\Entity\Event;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Travel\Entity\TravelMode;
use Mobicoop\Bundle\MobicoopBundle\Gamification\Entity\GamificationEntity;

/**
 * Carpooling : proposal (offer from a driver / request from a passenger).
 */
class Proposal extends GamificationEntity implements ResourceInterface, \JsonSerializable
{
    const TYPE_ONE_WAY = 1;
    const TYPE_OUTWARD = 2;
    const TYPE_RETURN = 3;
    
    const TYPE = [
            "one_way"=>self::TYPE_ONE_WAY,
            "return"=>self::TYPE_OUTWARD
    ];

    // proposal validity in years if no end date provided
    const PROPOSAL_VALIDITY = 10;
    
    /**
     * @var int The id of this proposal.
     * @Groups({"post","put"})
     */
    private $id;
    
    /**
     * @var string|null The iri of this proposal.
     * @Groups({"post","put"})
     */
    private $iri;

    /**
     * @var string|null Linked proposal for a round trip (return or outward journey).
     * /!\ for now we must pass the IRI !!!
     * @Groups({"post","put"})
     */
    private $proposalLinked;
    
    /**
     * @var User|null User for whom the proposal is submitted (in general the user itself, except when it is a "posting for").
     * @Groups({"post","put"})
     */
    private $user;

    /**
     * @var User|null User that create the proposal for another user.
     * @Groups({"post","put"})
     */
    private $userDelegate;

    /**
    * @var int Proposal type (one way / outward / return).
    * @Groups({"post","put"})
    */
    private $type;
    
    /**
     * @var string|null A comment about the proposal.
     * @Groups({"post","put"})
     */
    private $comment;

    /**
     * @var boolean Private proposal.
     * A private proposal can't be the found in the result of a search.
     * @Groups({"post","put"})
     */
    private $private;

    /**
     * @var boolean Paused proposal.
     * A paused proposal can't be the found in the result of a search, and can be unpaused at any moment.
     * @Groups({"post","put"})
     */
    private $paused;

    /**
     * @var Waypoint[] The waypoints of the proposal.
     * @Groups({"get","post","put"})
     *
     * @Assert\NotBlank
     */
    private $waypoints;
    
    /**
     * @var TravelMode[]|null The travel modes accepted if the proposal is a request.
     */
    private $travelModes;

    /**
     * @var ArrayCollection|null The communities related to the proposal.
     *
     * @Groups({"post","put"})
     */
    private $communities;

    /**
     * @var Matching[]|null The matchings of the proposal (if proposal is a request).
     */
    private $matchingOffers;

    /**
     * @var Matching[]|null The matchings of the proposal (if proposal is an offer).
     */
    private $matchingRequests;

    /**
     * @var Criteria The criteria applied to the proposal.
     * @Groups({"post","put"})
     *
     * @Assert\NotBlank
     */
    private $criteria;
    
    /**
     * @var IndividualStop[] The individual stops of the proposal.
     */
    private $individualStops;
    
    /**
     * @var Proposal|null The proposal we know that already matched by this new proposal
     * @Groups({"post","put"})
     */
    private $matchingProposal;

    /**
     * @var mixed|null The matching of the linked proposal (used for regular return trips).
     * @Groups({"post","put"})
     */
    private $matchingLinked;

    /**
     * @var mixed|null The ask of the linked proposal (used for regular return trips).
     * @Groups({"post","put"})
     */
    private $askLinked;

    /**
     * @var boolean Create a formal ask after posting the proposal.
     * @Groups({"post","put"})
     */
    private $formalAsk;

    /**
     * @var array The matching results of a proposal in a user-friendly format.
     */
    private $results;

    /**
     * @var Event related for the proposal
     * @Groups({"post","put"})
     */
    private $event;

    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/proposals/".$id);
        }
        $this->waypoints = new ArrayCollection();
        $this->travelModes = new ArrayCollection();
        $this->communities = new ArrayCollection();
        $this->matchingOffers = new ArrayCollection();
        $this->matchingRequests = new ArrayCollection();
        $this->individualStops = new ArrayCollection();
    }

    public function __clone()
    {
        // when we clone a Proposal we keep only the basic properties, we re-initialize all the collections
        $this->waypoints = new ArrayCollection();
        $this->travelModes = new ArrayCollection();
        $this->communities = new ArrayCollection();
        $this->matchingOffers = new ArrayCollection();
        $this->matchingRequests = new ArrayCollection();
        $this->individualStops = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id)
    {
        $this->id = $id;
    }
    
    public function getIri()
    {
        return $this->iri;
    }
    
    public function setIri($iri)
    {
        $this->iri = $iri;
    }
    
    public function getType(): ?int
    {
        return $this->type;
    }
    
    public function setType(int $type): self
    {
        $this->type = $type;
        
        return $this;
    }
    
    public function getComment(): ?string
    {
        return $this->comment;
    }
    
    public function setComment(?string $comment): self
    {
        $this->comment = $comment;
        
        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(?bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    public function isPaused(): bool
    {
        return $this->paused;
    }

    public function setPaused(?bool $paused): self
    {
        $this->paused = $paused;

        return $this;
    }
    
    public function getProposalLinked()
    {
        return $this->proposalLinked;
    }
    
    public function setProposalLinked($proposalLinked): ?self
    {
        $this->proposalLinked = $proposalLinked;
        // set (or unset) the owning side of the relation if necessary
        // $newProposalLinked = $proposalLinked === null ? null : $this;
        // if ($newProposalLinked !== $proposalLinked->getProposalLinked()) {
        //     $proposalLinked->setProposalLinked($newProposalLinked);
        // }
        
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

    public function getUserDelegate(): ?User
    {
        return $this->userDelegate;
    }
    
    public function setUserDelegate(?User $userDelegate): self
    {
        $this->userDelegate = $userDelegate;
        
        return $this;
    }
    
    /**
     * @return Collection|Waypoint[]
     */
    public function getWaypoints(): Collection
    {
        return $this->waypoints;
    }
    
    public function addWaypoint(Waypoint $waypoint): self
    {
        if (!$this->waypoints->contains($waypoint)) {
            $this->waypoints[] = $waypoint;
            $waypoint->setProposal($this);
        }
        
        return $this;
    }
    
    public function removeWaypoint(Waypoint $waypoint): self
    {
        if ($this->waypoints->contains($waypoint)) {
            $this->waypoints->removeElement($waypoint);
            // set the owning side to null (unless already changed)
            if ($waypoint->getProposal() === $this) {
                $waypoint->setProposal(null);
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

    public function getCommunities()
    {
        return $this->communities->getValues();
    }

    // we can add a community as a full community object or with its IRI
    public function addCommunity($community): self
    {
        if (!$this->communities->contains($community)) {
            $this->communities[] = $community;
        }
        return $this;
    }

    public function removeCommunity($community): self
    {
        if ($this->communities->contains($community)) {
            $this->communities->removeElement($community);
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
    
    public function getCriteria(): ?Criteria
    {
        return $this->criteria;
    }
    
    public function setCriteria(Criteria $criteria): self
    {
        $this->criteria = $criteria;
        
        return $this;
    }
    
    /**
     * @return Collection|IndividualStop[]
     */
    public function getIndividualStops(): Collection
    {
        return $this->individualStops;
    }
    
    public function addIndividualStop(IndividualStop $individualStop): self
    {
        if (!$this->individualStops->contains($individualStop)) {
            $this->individualStops[] = $individualStop;
            $individualStop->setProposal($this);
        }
        
        return $this;
    }
    
    public function removeIndividualStop(IndividualStop $individualStop): self
    {
        if ($this->individualStops->contains($individualStop)) {
            $this->individualStops->removeElement($individualStop);
            // set the owning side to null (unless already changed)
            if ($individualStop->getProposal() === $this) {
                $individualStop->setProposal(null);
            }
        }
        
        return $this;
    }

    public function getMatchingProposal(): ?Proposal
    {
        return $this->matchingProposal;
    }

    public function setMatchingProposal(?Proposal $matchingProposal): self
    {
        $this->matchingProposal = $matchingProposal;

        return $this;
    }

    public function getMatchingLinked()
    {
        return $this->matchingLinked;
    }

    public function setMatchingLinked($matchingLinked): self
    {
        $this->matchingLinked = $matchingLinked;

        return $this;
    }

    public function getAskLinked()
    {
        return $this->askLinked;
    }

    public function setAskLinked($askLinked): self
    {
        $this->askLinked = $askLinked;

        return $this;
    }

    public function hasFormalAsk(): bool
    {
        return $this->formalAsk;
    }

    public function setFormalAsk(?bool $formalAsk): self
    {
        $this->formalAsk = $formalAsk;

        return $this;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function setResults($results)
    {
        $this->results = $results;

        return $this;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }
    // If you want more info from user you just have to add it to the jsonSerialize function
    public function jsonSerialize()
    {
        return
        [
            'id'                        => $this->getId(),
            'matchingProposal'          => $this->getMatchingProposal(),
            'matchingLinked'            => $this->getMatchingLinked(),
            'askLinked'                 => $this->getAskLinked(),
            'proposalLinked'            => $this->getProposalLinked(),
            'comment'                   => $this->getComment(),
            'user'                      => $this->getUser(),
            'criteria'                  => $this->getCriteria(),
            'type'                      => $this->getType(),
            'waypoints'                 => $this->getWaypoints(),
            'results'                   => $this->getResults(),
            'gamificationNotifications' => $this->getGamificationNotifications(),

        ];
    }
}
