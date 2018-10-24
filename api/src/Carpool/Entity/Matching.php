<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Carpool\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : matching between an offer and a request.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"}
 * )
 */
class Matching
{
    /**
     * @var int The id of this matching.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var \DateTimeInterface Creation date of the matching.
     *
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @var int|null Real distance in metres of the matching route.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $distanceReal;

    /**
     * @var int|null Flying distance in metres of the matching route.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $distanceFly;

    /**
     * @var int|null Duration in seconds of the matching route (based on real distance).
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $duration;

    /**
     * @var Proposal The offer proposal.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Carpool\Entity\Proposal", inversedBy="matchingRequests")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $proposalOffer;

    /**
     * @var Proposal The request proposal.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Carpool\Entity\Proposal", inversedBy="matchingOffers")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $proposalRequest;

    /**
     * @var Point|null Starting point of the offer proposal used for the matching.
     *
     * @ORM\ManyToOne(targetEntity="App\Carpool\Entity\Point")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $pointOfferFrom;

    /**
     * @var Point|null Ending point of the offer proposal used for the matching.
     *
     * @ORM\ManyToOne(targetEntity="App\Carpool\Entity\Point")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $pointOfferTo;

    /**
     * @var Point Starting point of the request used for the matching (if multimodal travel, otherwise it's always the starting point).
     *
     * @ORM\ManyToOne(targetEntity="App\Carpool\Entity\Point")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $pointRequestFrom;

    /**
     * @var Solicitation[]|null The solicitations created with this matching as a source.
     *
     * @ORM\OneToMany(targetEntity="App\Carpool\Entity\Solicitation", mappedBy="matching")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $solicitations;

    /**
     * @var Criteria The criteria applied to this solicitation.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="App\Carpool\Entity\Criteria", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $criteria;

    public function __construct()
    {
        $this->solicitations = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
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

    public function getProposalOffer(): ?Proposal
    {
        return $this->proposalOffer;
    }

    public function setProposalOffer(?Proposal $proposalOffer): self
    {
        $this->proposalOffer = $proposalOffer;

        return $this;
    }

    public function getProposalRequest(): ?Proposal
    {
        return $this->proposalRequest;
    }

    public function setProposalRequest(?Proposal $proposalRequest): self
    {
        $this->proposalRequest = $proposalRequest;

        return $this;
    }

    public function getPointOfferFrom(): ?Point
    {
        return $this->pointOfferFrom;
    }

    public function setPointOfferFrom(?Point $pointOfferFrom): self
    {
        $this->pointOfferFrom = $pointOfferFrom;

        return $this;
    }

    public function getPointOfferTo(): ?Point
    {
        return $this->pointOfferTo;
    }

    public function setPointOfferTo(?Point $pointOfferTo): self
    {
        $this->pointOfferTo = $pointOfferTo;

        return $this;
    }

    public function getPointRequestFrom(): ?Point
    {
        return $this->pointRequestFrom;
    }

    public function setPointRequestFrom(?Point $pointRequestFrom): self
    {
        $this->pointRequestFrom = $pointRequestFrom;

        return $this;
    }

    /**
     * @return Collection|Solicitation[]
     */
    public function getSolicitations(): Collection
    {
        return $this->solicitations;
    }

    public function addSolicitation(Solicitation $solicitation): self
    {
        if (!$this->solicitations->contains($solicitation)) {
            $this->solicitations[] = $solicitation;
            $solicitation->setMatching($this);
        }

        return $this;
    }

    public function removeSolicitation(Solicitation $solicitation): self
    {
        if ($this->solicitations->contains($solicitation)) {
            $this->solicitations->removeElement($solicitation);
            // set the owning side to null (unless already changed)
            if ($solicitation->getMatching() === $this) {
                $solicitation->setMatching(null);
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
