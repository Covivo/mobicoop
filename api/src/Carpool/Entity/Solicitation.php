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
use App\Address\Entity\Address;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Carpooling : solicitation from/to a driver and/or a passenger (after a matching between an offer and a request).
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 */
class Solicitation
{
    /**
     * @var int The id of this solicitation.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var int Solicitation status (0 = waiting; 1 = accepted; 2 = declined).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $status;

    /**
     * @var int The journey type (1 = one way trip; 2 = outward of a round trip; 3 = return of a round trip)).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $journeyType;

    /**
     * @var \DateTimeInterface Creation date of the solicitation.
     *
     * @ORM\Column(type="datetime")
     */
    private $createdDate;

    /**
     * @var int|null Real distance of the matching journey in metres.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $distanceReal;

    /**
     * @var int|null Flying distance of the matching journey in metres.
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $distanceFly;

    /**
     * @var int|null Estimated duration of the matching journey in seconds (based on real distance).
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"read"})
     */
    private $duration;

    /**
     * @var Address The starting point address.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="App\Address\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $addressFrom;

    /**
     * @var Address The destination address.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="App\Address\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $addressTo;

    /**
     * @var User The user that creates the solicitation.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="solicitations")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var User The user that created the offer (= shortcut to the driver)
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="solicitationsOffer")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $userOffer;

    /**
     * @var User The user that created the request (= shortcut to the passenger)
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User", inversedBy="solicitationsRequest")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $userRequest;

    /**
     * @var Matching The matching at the origin of the solicitation.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\Carpool\Entity\Matching", inversedBy="solicitations")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $matching;

    /**
     * @var Solicitation|null The linked solicitation.
     *
     * @ORM\OneToOne(targetEntity="App\Carpool\Entity\Solicitation")
     * @Groups({"read"})
     * @MaxDepth(1)
     */
    private $solicitationLinked;

    /**
     * @var Criteria The criteria applied to the solicitation.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="App\Carpool\Entity\Criteria", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $criteria;
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

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

    public function getAddressFrom(): ?Address
    {
        return $this->addressFrom;
    }

    public function setAddressFrom(?Address $addressFrom): self
    {
        $this->addressFrom = $addressFrom;

        return $this;
    }

    public function getAddressTo(): ?Address
    {
        return $this->addressTo;
    }

    public function setAddressTo(?Address $addressTo): self
    {
        $this->addressTo = $addressTo;

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

    public function getUserOffer(): ?User
    {
        return $this->userOffer;
    }

    public function setUserOffer(?User $userOffer): self
    {
        $this->userOffer = $userOffer;

        return $this;
    }

    public function getUserRequest(): ?User
    {
        return $this->userRequest;
    }

    public function setUserRequest(?User $userRequest): self
    {
        $this->userRequest = $userRequest;

        return $this;
    }

    public function getMatching(): ?Matching
    {
        return $this->matching;
    }

    public function setMatching(?Matching $matching): self
    {
        $this->matching = $matching;

        return $this;
    }

    public function getSolicitationLinked(): ?self
    {
        return $this->solicitationLinked;
    }

    public function setSolicitationLinked(?self $solicitationLinked): self
    {
        $this->solicitationLinked = $solicitationLinked;

        // set (or unset) the owning side of the relation if necessary
        $newSolicitationLinked = $solicitationLinked === null ? null : $this;
        if ($newSolicitationLinked !== $solicitationLinked->getSolicitationlLinked()) {
            $solicitationLinked->setSolicitationLinked($newSolicitationLinked);
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
