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

namespace App\Solidary\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Events;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Solidary Ask
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
 *          "get",
 *          "post"
 *      },
 *      itemOperations={"get","delete",
 *          "put"
 *      }
 * )
 */
class SolidaryAsk
{
    const STATUS_INITIATED = 1;
    const STATUS_PENDING_AS_DRIVER = 2;
    const STATUS_PENDING_AS_PASSENGER = 3;
    const STATUS_ACCEPTED_AS_DRIVER = 4;
    const STATUS_ACCEPTED_AS_PASSENGER = 5;
    const STATUS_DECLINED_AS_DRIVER = 6;
    const STATUS_DECLINED_AS_PASSENGER = 7; // asked by remi
    
    /**
     * @var int The id of this solidary ask.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"read"})
     */
    private $id;

    /**
     * @var int Solidary Ask status (1 = initiated; 2 = pending as driver, 3 = pending as passenger, 4 = accepted as driver; 5 = accepted as passenger, 6 = declined as driver, 7 = declined as passenger).
     *
     * @Assert\NotBlank
     * @ORM\Column(type="smallint")
     * @Groups({"read","write"})
     */
    private $status;

    /**
     * @var \DateTimeInterface Creation date of the solidary ask.
     *
     * @ORM\Column(type="datetime")
     * @Groups({"read","write"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the solidary ask.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read","write"})
     */
    private $updatedDate;

    /**
     * @var SolidarySolution The solidary solution this Ask is for.
     *
     * @ORM\ManyToOne(targetEntity="\App\Solidary\Entity\SolidarySolution")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     */
    private $solidarySolution;

    /**
     * @var ArrayCollection The ask history items linked with the ask.
     *
     * @ORM\OneToMany(targetEntity="\App\Solidary\Entity\SolidaryAskHistory", mappedBy="solidaryAsk", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"id" = "ASC"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     * ApiSubresource(maxDepth=1)
     */
    private $solidaryAskHistories;

    public function __construct()
    {
        $this->solidaryAskHistories = new ArrayCollection();
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

    public function getSolidarySolution(): ?SolidarySolution
    {
        return $this->solidarySolution;
    }

    public function setSolidarySolution(SolidarySolution $solidarySolution): self
    {
        $this->solidarySolution = $solidarySolution;

        return $this;
    }

    public function getSolidaryAskHistories()
    {
        return $this->solidaryAskHistories->getValues();
    }
    
    public function addSolidaryAskHistory(SolidaryAskHistory $solidaryAskHistory): self
    {
        if (!$this->solidaryAskHistories->contains($solidaryAskHistory)) {
            $this->solidaryAskHistories[] = $solidaryAskHistory;
            $solidaryAskHistory->setSolidaryAsk($this);
        }
        
        return $this;
    }
    
    public function removeSolidaryAskHistory(SolidaryAskHistory $solidaryAskHistory): self
    {
        if ($this->solidaryAskHistories->contains($solidaryAskHistory)) {
            $this->solidaryAskHistories->removeElement($solidaryAskHistory);
            // set the owning side to null (unless already changed)
            if ($solidaryAskHistory->getSolidaryAsk() === $this) {
                $solidaryAskHistory->setSolidaryAsk(null);
            }
        }
        
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
