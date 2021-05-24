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
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Carpool\Controller\AskPost;
use App\Carpool\Controller\AskPut;
use App\Payment\Entity\CarpoolItem;
use App\Solidary\Entity\SolidaryAsk;

/**
 * Carpooling : ask from/to a driver and/or a passenger (after a matching between an offer and a request).
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "post"={
 *              "method"="POST",
 *              "path"="/asks",
 *              "controller"=AskPost::class,
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "delete"={
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "put"={
 *              "method"="PUT",
 *              "path"="/asks/{id}",
 *              "controller"=AskPut::class,
 *              "swagger_context" = {
 *                  "tags"={"Carpool"}
 *              }
 *          },
 *          "payment_status"={
 *              "method"="GET",
 *              "path"="/asks/{id}/paymentStatus",
 *              "normalization_context"={"groups"={"readPaymentStatus"}},
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          },
 *          "pending_payment"={
 *              "method"="GET",
 *              "path"="/asks/{id}/pendingPayment",
 *              "normalization_context"={"groups"={"readPayment"}},
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          }
 *      }
 * )
 */
class Ask
{
    const STATUS_INITIATED = 1;
    const STATUS_PENDING_AS_DRIVER = 2;
    const STATUS_PENDING_AS_PASSENGER = 3;
    const STATUS_ACCEPTED_AS_DRIVER = 4;
    const STATUS_ACCEPTED_AS_PASSENGER = 5;
    const STATUS_DECLINED_AS_DRIVER = 6;
    const STATUS_DECLINED_AS_PASSENGER = 7; // asked by remi
    
    const ALL_ASKS = 0;
    const ASKS_WITHOUT_SOLIDARY = 1;
    const ASKS_WITH_SOLIDARY = 2;

    const TYPE_ONE_WAY = 1;
    const TYPE_OUTWARD_ROUNDTRIP = 2;
    const TYPE_RETURN_ROUNDTRIP = 3;

    const PAYMENT_STATUS_PENDING = 0;
    const PAYMENT_STATUS_ONLINE = 1;
    const PAYMENT_STATUS_DIRECT = 2;
    const PAYMENT_STATUS_UNPAID = 3;
    const PAYMENT_STATUS_PAID = 4; // Paid but with undetermined method
    
    /**
     * @var int The id of this ask.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read","threads","thread","readPaymentStatus"})
     */
    private $id;

    /**
     * @var int Ask status (1 = initiated; 2 = pending as driver, 3 = pending as passenger, 4 = accepted as driver; 5 = accepted as passenger, 6 = declined as driver, 7 = declined as passenger).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write","threads","thread"})
     */
    private $status;

    /**
     * @var int The ask type (1 = one way trip; 2 = outward of a round trip; 3 = return of a round trip)).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write","threads","thread"})
     */
    private $type;

    /**
     * @var \DateTimeInterface Creation date of the solicitation.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"threads","thread"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the solicitation.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"threads","thread"})
     */
    private $updatedDate;

    /**
     * @var User The user that creates the ask.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="asks")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write","thread"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var User The user the ask is for
     * This field is nullable for migration purpose but it can't be null
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="asksRelated")
     * @ORM\JoinColumn(nullable=true)
     * @Groups({"read","write","thread"})
     * @MaxDepth(1)
     */
    private $userRelated;

    /**
     * @var User|null User that create the proposal for another user.
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="asksDelegate")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $userDelegate;

    /**
     * @var Matching The matching at the origin of the ask.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="\App\Carpool\Entity\Matching", inversedBy="asks")
     * @ORM\JoinColumn(onDelete="CASCADE", nullable=false)
     * @Groups({"read","write","threads","thread"})
     * @MaxDepth(1)
     */
    private $matching;

    /**
     * @var Ask|null The linked ask if a user proposes another ask.
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Ask")
     * @Groups({"read","threads","thread"})
     * @MaxDepth(1)
     */
    private $ask;

    /**
     * @var Ask|null The linked ask for return trips.
     *
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Ask", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"read","threads","thread"})
     * @MaxDepth(1)
     */
    private $askLinked;

    /**
     * @var Ask|null Related ask for opposite role : driver ask if the current ask is as passenger, passenger ask if the current ask is as driver.
     * Used when the ask is created with an undefined role.
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Ask", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"read","threads","thread"})
     * @MaxDepth(1)
     */
    private $askOpposite;

    /**
     * @var Criteria The criteria applied to the ask.
     *
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Carpool\Entity\Criteria", cascade={"persist", "remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $criteria;
    
    /**
     * @var ArrayCollection The waypoints of the ask.
     *
     * @Assert\NotBlank
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\Waypoint", mappedBy="ask", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"read","write","threads","thread"})
     * @MaxDepth(1)
     * ApiSubresource(maxDepth=1)
     */
    private $waypoints;

    /**
     * @var ArrayCollection The ask history items linked with the ask.
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\AskHistory", mappedBy="ask", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     * ApiSubresource(maxDepth=1)
     */
    private $askHistories;

    /**
     * @var ArrayCollection The proofs related to the ask.
     *
     * @ORM\OneToMany(targetEntity="\App\Carpool\Entity\CarpoolProof", mappedBy="ask", cascade={"persist"})
     */
    private $carpoolProofs;

    /**
     * @var Matching|null Related matching for a round trip (return or outward journey).
     * Not persisted : used only to get the return trip information.
     * @Groups("write")
     */
    private $matchingRelated;

    /**
     * @var Matching|null Opposite matching (if proposal and request can be switched, so if driver and passenger can switch roles)
     * Not persisted : used only to get the link information.
     * @Groups("write")
     */
    private $matchingOpposite;

    /**
     * @var array The filters returned to the user. The user can then filter and sort the results.
     * @Groups({"read","write"})
     */
    private $filters;

    /**
     * @var SolidaryAsk|null The SolidaryAsk possibly linked to this Ask
     *
     * @ORM\OneToOne(targetEntity="\App\Solidary\Entity\SolidaryAsk", mappedBy="ask", cascade={"persist","remove"})
     * @Groups({"read"})
     */
    private $solidaryAsk;

    /**
     * @var ArrayCollection|null A user may have many action logs.
     *
     * @ORM\OneToMany(targetEntity="\App\Payment\Entity\CarpoolItem", mappedBy="ask", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"itemDate" = "ASC"})
     */
    private $carpoolItems;
    
    /**
     * @var int|null The payment status of the Ask
     * @Groups({"read","readPaymentStatus"})
     */
    private $paymentStatus;

    /**
     * @var array The weeks with a pending payment.
     * @Groups({"readPayment"})
     */
    private $weekItems;

    /**
    * @var int|null The id of the PaymentItem of the Ask
    * @Groups({"read","readPaymentStatus"})
    */
    private $paymentItemId;

    /**
    * @var int|null The default week of the PaymentItem
    * @Groups({"read","readPaymentStatus"})
    */
    private $paymentItemWeek;

    /**
     * @var \DateTimeInterface|null The date of an unpaid declaration for this Ask
     * @Groups({"read","readPaymentStatus"})
     */
    private $unpaidDate;

    public function __construct()
    {
        $this->waypoints = new ArrayCollection();
        $this->askHistories = new ArrayCollection();
        $this->carpoolProofs = new ArrayCollection();
        $this->carpoolItems = new ArrayCollection();
    }
    
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

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

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

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUserRelated(): User
    {
        return $this->userRelated;
    }

    public function setUserRelated(?User $userRelated): self
    {
        $this->userRelated = $userRelated;

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

    public function getMatching(): Matching
    {
        return $this->matching;
    }

    public function setMatching(?Matching $matching): self
    {
        $this->matching = $matching;

        return $this;
    }

    public function getAsk(): ?self
    {
        return $this->ask;
    }

    public function setAsk(?self $ask): self
    {
        $this->ask = $ask;

        // set (or unset) the owning side of the relation if necessary
        $newAsk = $ask === null ? null : $this;
        if ($newAsk !== $ask->getAsk()) {
            $ask->setAsk($newAsk);
        }

        return $this;
    }

    public function getAskLinked(): ?self
    {
        return $this->askLinked;
    }

    public function setAskLinked(?self $askLinked): self
    {
        $this->askLinked = $askLinked;

        // set (or unset) the owning side of the relation if necessary
        $newAskLinked = $askLinked === null ? null : $this;
        if (!is_null($askLinked) && $newAskLinked !== $askLinked->getAskLinked()) {
            $askLinked->setAskLinked($newAskLinked);
        }

        return $this;
    }

    public function getAskOpposite(): ?self
    {
        return $this->askOpposite;
    }

    public function setAskOpposite(?self $askOpposite): self
    {
        $this->askOpposite = $askOpposite;

        // set (or unset) the owning side of the relation if necessary
        $newAskOpposite = $askOpposite === null ? null : $this;
        if ($newAskOpposite !== $askOpposite->getAskOpposite()) {
            $askOpposite->setAskOpposite($newAskOpposite);
        }

        return $this;
    }

    public function getCriteria(): Criteria
    {
        return $this->criteria;
    }

    public function setCriteria(Criteria $criteria): self
    {
        $this->criteria = $criteria;

        return $this;
    }
    
    public function getWaypoints()
    {
        return $this->waypoints->getValues();
    }
    
    public function addWaypoint(Waypoint $waypoint): self
    {
        if (!$this->waypoints->contains($waypoint)) {
            $this->waypoints[] = $waypoint;
            $waypoint->setAsk($this);
        }
        
        return $this;
    }
    
    public function removeWaypoint(Waypoint $waypoint): self
    {
        if ($this->waypoints->contains($waypoint)) {
            $this->waypoints->removeElement($waypoint);
            // set the owning side to null (unless already changed)
            if ($waypoint->getAsk() === $this) {
                $waypoint->setAsk(null);
            }
        }
        
        return $this;
    }

    public function getAskHistories()
    {
        return $this->askHistories->getValues();
    }
    
    public function addAskHistory(AskHistory $askHistory): self
    {
        if (!$this->askHistories->contains($askHistory)) {
            $this->askHistories[] = $askHistory;
            $askHistory->setAsk($this);
        }
        
        return $this;
    }
    
    public function removeAskHistory(AskHistory $askHistory): self
    {
        if ($this->askHistories->contains($askHistory)) {
            $this->askHistories->removeElement($askHistory);
            // set the owning side to null (unless already changed)
            if ($askHistory->getAsk() === $this) {
                $askHistory->setAsk(null);
            }
        }
        
        return $this;
    }

    public function getCarpoolProofs()
    {
        return $this->carpoolProofs->getValues();
    }
    
    public function addCarpoolProof(CarpoolProof $carpoolProof): self
    {
        if (!$this->carpoolProofs->contains($carpoolProof)) {
            $this->carpoolProofs[] = $carpoolProof;
            $carpoolProof->setAsk($this);
        }
        
        return $this;
    }
    
    public function removeCarpoolProof(CarpoolProof $carpoolProof): self
    {
        if ($this->carpoolProofs->contains($carpoolProof)) {
            $this->carpoolProofs->removeElement($carpoolProof);
            // set the owning side to null (unless already changed)
            if ($carpoolProof->getAsk() === $this) {
                $carpoolProof->setAsk(null);
            }
        }
        
        return $this;
    }

    public function getMatchingRelated(): ?Matching
    {
        return $this->matchingRelated;
    }
    
    public function setMatchingRelated(?Matching $matchingRelated): self
    {
        $this->matchingRelated = $matchingRelated;
                
        return $this;
    }

    public function getMatchingOpposite(): ?Matching
    {
        return $this->matchingOpposite;
    }
    
    public function setMatchingOpposite(?Matching $matchingOpposite): self
    {
        $this->matchingOpposite = $matchingOpposite;
        
        return $this;
    }

    public function getFilters(): ?array
    {
        return $this->filters;
    }

    public function setFilters(?array $filters): self
    {
        $this->filters = $filters;

        return $this;
    }
    
    public function getSolidaryAsk(): ?SolidaryAsk
    {
        return $this->solidaryAsk;
    }

    public function setSolidaryAsk(?SolidaryAsk $solidaryAsk): self
    {
        $this->solidaryAsk = $solidaryAsk;

        return $this;
    }

    public function getCarpoolItems()
    {
        return $this->carpoolItems->getValues();
    }

    public function addCarpoolItem(CarpoolItem $carpoolItem): self
    {
        if (!$this->carpoolItems->contains($carpoolItem)) {
            $this->carpoolItems->add($carpoolItem);
            $carpoolItem->setAsk($this);
        }

        return $this;
    }

    public function removecarpoolItem(CarpoolItem $carpoolItem): self
    {
        if ($this->carpoolItems->contains($carpoolItem)) {
            $this->carpoolItems->removeElement($carpoolItem);
            // set the owning side to null (unless already changed)
            if ($carpoolItem->getAsk() === $this) {
                $carpoolItem->setAsk(null);
            }
        }

        return $this;
    }

    public function getPaymentStatus(): ?int
    {
        return $this->paymentStatus;
    }

    public function setPaymentStatus(?int $paymentStatus): self
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    public function getWeekItems(): ?array
    {
        return $this->weekItems;
    }

    public function setWeekItems(?array $weekItems): self
    {
        $this->weekItems = $weekItems;

        return $this;
    }

    public function getPaymentItemId(): ?int
    {
        return $this->paymentItemId;
    }

    public function setPaymentItemId(?int $paymentItemId): self
    {
        $this->paymentItemId = $paymentItemId;

        return $this;
    }
    
    public function getPaymentItemWeek(): ?int
    {
        return $this->paymentItemWeek;
    }

    public function setPaymentItemWeek(?int $paymentItemWeek): self
    {
        $this->paymentItemWeek = $paymentItemWeek;

        return $this;
    }

    public function getUnpaidDate(): ?\DateTimeInterface
    {
        return $this->unpaidDate;
    }

    public function setUnpaidDate(?\DateTimeInterface $unpaidDate): self
    {
        $this->unpaidDate = $unpaidDate;

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

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \Datetime());
    }

    /**
     * User related by this Ask
     *
     * @ORM\PrePersist
     */
    public function setAutoUserRelated()
    {
        if ($this->getMatching()->getProposalOffer()->getUser()->getId()==$this->getUser()->getId()) {
            $this->setUserRelated($this->getMatching()->getProposalRequest()->getUser());
        } else {
            $this->setUserRelated($this->getMatching()->getProposalOffer()->getUser());
        }
    }
}
