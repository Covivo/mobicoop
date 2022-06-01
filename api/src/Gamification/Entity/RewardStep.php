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
use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
* Gamification : A RewarStep. A previously validated sequenceItem on the way to earn a Badge.
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
*              "security"="is_granted('reject',object)",
*              "swagger_context" = {
*                  "summary"="Not implemented",
*                  "tags"={"Gamification"}
*               }
*           }
*      },
*      itemOperations={
*          "get"={
*              "security"="is_granted('reject',object)",
*              "swagger_context" = {
*                  "summary"="Not implemented",
*                  "tags"={"Gamification"}
*              }
*          },
*          "tagAsNotified"={
*              "method"="GET",
*              "path"="/reward_steps/{id}/tagAsNotified",
*              "normalization_context"={"groups"={"tagAsNotified"}},
*              "swagger_context" = {
*                  "summary"="Tag a RewardStep as notified to the User",
*                  "tags"={"Gamification"}
*              }
*          },
*      }
* )
*/
class RewardStep implements GamificationNotificationInterface
{
    /**
     * @var int The RewardStep's id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"readGamification","tagAsNotified"})
     * @MaxDepth(1)
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var SequenceItem The SequenceItem's of this RewardStep
     *
     * @ORM\ManyToOne(targetEntity="\App\Gamification\Entity\SequenceItem", inversedBy="rewardSteps")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"readGamification","writeGamification"})
     * @MaxDepth(1)
     */
    private $sequenceItem;

    /**
     * @var User The User who validated this RewardStep
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="rewardSteps")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"readGamification","writeGamification"})
     * @MaxDepth(1)
     */
    private $user;

    /**
     * @var \DateTimeInterface RewardStep's notification date. Determine if this RewardStep has been notified to the user.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readGamification","tagAsNotified"})
     */
    private $notifiedDate;

    /**
     * @var \DateTimeInterface RewardStep's creation date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readGamification"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface RewardStep's update date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readGamification"})
     */
    private $updatedDate;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getSequenceItem(): ?SequenceItem
    {
        return $this->sequenceItem;
    }

    public function setSequenceItem(?SequenceItem $sequenceItem): self
    {
        $this->sequenceItem = $sequenceItem;

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

    public function getNotifiedDate(): ?\DateTimeInterface
    {
        return $this->notifiedDate;
    }

    public function setNotifiedDate(?\DateTimeInterface $notifiedDate): self
    {
        $this->notifiedDate = $notifiedDate;

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
