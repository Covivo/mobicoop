<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\Gamification\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Gamification\Interfaces\GamificationNotificationInterface;
use App\Geography\Entity\Territory;
use App\Image\Entity\Image;
use App\User\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
* Gamification : A Badge that can be won/achieved by a User
* @author Maxime Bardot <maxime.bardot@mobicoop.org>
*
* @ORM\Entity
* @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *     attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readGamification"}, "enable_max_depth"="true"}
 *     },
 *     collectionOperations={
 *          "get"={
 *              "security"="is_granted('badge_list',object)",
 *              "swagger_context" = {
 *                  "summary"="Get the badges list of the instance",
 *                  "tags"={"Gamification"}
 *               }
 *           }
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('badge_read',object)",
 *              "swagger_context" = {
 *                  "summary"="Get a Badge",
 *                  "tags"={"Gamification"}
 *              }
 *          }
 *      }
 * )
 */
class Badge implements GamificationNotificationInterface
{
    const STATUS_DRAFT = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_INACTIVE = 2;

    const TRANSLATABLE_ITEMS = [
        "title",
        "text"
    ];

    /**
     * @var int The Badge's id
     *
     * @ApiProperty(identifier=true)
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"readGamification"})
     * @MaxDepth(1)
     */
    private $id;

    /**
     * @var string Badge's name. Mostly used for intern managment
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"readGamification","writeGamification"})
     */
    private $name;

    /**
     * @var string Badge's title. Used for display.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"readGamification","writeGamification"})
     */
    private $title;

    /**
     * @var string Badge's text, description.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"readGamification","writeGamification"})
     */
    private $text;

    /**
     * @var int Badge's status. (0 : Draft, 1 : Active, 2 : Inactive)
     *
     * @ORM\Column(type="integer")
     * @Groups({"readGamification","writeGamification"})
     */
    private $status;

    /**
     * @var boolean If it's a public badge or not. If it can be seen by anybody.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readGamification","writeGamification"})
     */
    private $public;

    /**
     * @var Image|null The Badges Icon
     *
     * @ORM\OneToOne(targetEntity="\App\Image\Entity\Image", mappedBy="badge")
     * @Groups({"readGamification"})
     * @MaxDepth(1)
     */
    private $icon;

    /**
     * @var Image|null The Badges reward Image
     *
     * @ORM\OneToOne(targetEntity="\App\Image\Entity\Image", mappedBy="badgeImage")
     * @Groups({"readGamification"})
     * @MaxDepth(1)
     */
    private $image;

    /**
     * @var Image|null The Badges reward Image
     *
     * @ORM\OneToOne(targetEntity="\App\Image\Entity\Image", mappedBy="badgeImageLight")
     * @Groups({"readGamification"})
     * @MaxDepth(1)
     */
    private $imageLight;

    /**
     * @var \DateTimeInterface Start Date of the active period of this Badge (if there is any)
     *
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"readGamification","writeGamification"})
     */
    private $startDate;

    /**
     * @var \DateTimeInterface End Date of the active period of this Badge (if there is any)
     *
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"readGamification","writeGamification"})
     */
    private $endDate;

    /**
     * @var ArrayCollection|null A Badge needs multiple SequenceItems to be earned
     *
     * @ORM\OneToMany(targetEntity="\App\Gamification\Entity\SequenceItem", mappedBy="badge", cascade={"persist","remove"})
     * @Groups({"readGamification","writeGamification"})
     * @MaxDepth(1)
     */
    private $sequenceItems;

    /**
     * @var ArrayCollection|null The territories of this Badge.
     *
     * @ORM\ManyToMany(targetEntity="\App\Geography\Entity\Territory")
     * @Groups({"readGamification"})
     */
    private $territories;
    
    /**
     * @var ArrayCollection|null The Users owning this Badge
     *
     * @ORM\OneToMany(targetEntity="\App\Gamification\Entity\Reward", mappedBy="badge")
     * @ORM\JoinTable(name="reward")
     */
    private $rewards;

    /**
     * @var \DateTimeInterface Badge's creation date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readGamification"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Badge's update date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readGamification"})
     */
    private $updatedDate;

    public function __construct()
    {
        $this->sequenceItems = new ArrayCollection();
        $this->territories = new ArrayCollection();
        $this->badges = new ArrayCollection();
        $this->rewards = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(?string $text): self
    {
        $this->text = $text;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isPublic(): bool
    {
        return $this->public;
    }

    public function setPublic(?bool $public): self
    {
        $this->public = $public;

        return $this;
    }

    public function getIcon(): ?Image
    {
        return $this->icon;
    }

    public function setIcon(?Image $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getImage(): ?Image
    {
        return $this->image;
    }

    public function setImage(?Image $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImageLight(): ?Image
    {
        return $this->imageLight;
    }

    public function setImageLight(?Image $imageLight): self
    {
        $this->imageLight = $imageLight;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(?\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getSequenceItems()
    {
        return $this->sequenceItems->getValues();
    }

    public function addSequenceItem(SequenceItem $sequenceItem): self
    {
        if (!$this->sequenceItems->contains($sequenceItem)) {
            $this->sequenceItems[] = $sequenceItem;
            $sequenceItem->getBadge($this);
        }

        return $this;
    }

    public function removeSequenceItem(SequenceItem $sequenceItem): self
    {
        if ($this->sequenceItems->contains($sequenceItem)) {
            $this->sequenceItems->removeElement($sequenceItem);
        }

        return $this;
    }

    public function getTerritories()
    {
        return $this->territories->getValues();
    }

    public function addTerritory(Territory $territory): self
    {
        if (!$this->territories->contains($territory)) {
            $this->territories[] = $territory;
        }
        
        return $this;
    }
    
    public function removeTerritory(Territory $territory): self
    {
        if ($this->territories->contains($territory)) {
            $this->territories->removeElement($territory);
        }
        return $this;
    }
    
    public function getRewards()
    {
        return $this->rewards->getValues();
    }

    public function addReward(Reward $reward): self
    {
        if (!$this->rewards->contains($reward)) {
            $this->rewards[] = $reward;
        }
        
        return $this;
    }
    
    public function removeReward(Reward $reward): self
    {
        if ($this->rewards->contains($reward)) {
            $this->rewards->removeElement($reward);
        }
        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(?\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updatedDate;
    }

    public function setUpdatedDate(?\DateTimeInterface $updatedDate): self
    {
        $this->updatedDate = $updatedDate;

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
}
