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

namespace App\Community\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Image\Entity\Image;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Carpool\Entity\Proposal;
use App\Community\Controller\JoinAction;

/**
 * A community.
 *
 * @ORM\Entity()
 * @UniqueEntity("name")
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}},
 *          "pagination_client_items_per_page"=true
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put","delete"}
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "name", "description", "createdDate"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"name":"partial"})
 */
class Community
{
    /**
     * @var int The id of this community.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var string The name of the community.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $name;

    /**
     * @var boolean|null Members are only visible by the members of the community.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $membersHidden;

    /**
     * @var boolean|null Proposals are only visible by the members of the community.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"read","write"})
     */
    private $proposalsHidden;
    
    /**
     * @var string The short description of the community.
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"read","write"})
     */
    private $description;
    
    /**
     * @var string The full description of the community.
     *
     * @ORM\Column(type="text")
     * @Groups({"read","write"})
     */
    private $fullDescription;
    
    /**
    * @var \DateTimeInterface Creation date of the event.
    *
    * @ORM\Column(type="datetime")
    * @Groups("read")
    */
    private $createdDate;
    
    /**
     * @var User The creator of the community.
     *
     * @Assert\NotBlank
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"read","write"})
     */
    private $user;
    
    /**
     * @var ArrayCollection|null The images of the community.
     *
     * @ORM\OneToMany(targetEntity="\App\Image\Entity\Image", mappedBy="community", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"read","write"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $images;

    /**
     * @var ArrayCollection|null The proposals in this community.
     *
     * @ORM\ManyToMany(targetEntity="\App\Carpool\Entity\Proposal")
     * @Groups({"read","write"})
     * @MaxDepth(1)
     */
    private $proposals;

    /**
     * @var ArrayCollection|null The members of the community.
     *
     * @ORM\OneToMany(targetEntity="\App\Community\Entity\CommunityUser", mappedBy="community", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $communityUsers;

    /**
     * @var ArrayCollection|null The security files of the community.
     *
     * @ORM\OneToMany(targetEntity="\App\Community\Entity\CommunitySecurity", mappedBy="community", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"read","write"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $communitySecurities;
    
    public function __construct($id=null)
    {
        $this->id = $id;
        $this->images = new ArrayCollection();
        $this->proposals = new ArrayCollection();
        $this->communityUsers = new ArrayCollection();
        $this->communitySecurities = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getName(): string
    {
        return $this->name;
    }
    
    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function isMembersHidden(): ?bool
    {
        return $this->membersHidden ? true : false;
    }
    
    public function setMembersHidden(?bool $isMembersHidden): self
    {
        $this->membersHidden = $isMembersHidden ? true : false;
        
        return $this;
    }

    public function isProposalsHidden(): ?bool
    {
        return $this->proposalsHidden ? true : false;
    }
    
    public function setProposalsHidden(?bool $isProposalsHidden): self
    {
        $this->proposalsHidden = $isProposalsHidden ? true : false;
        
        return $this;
    }
    
    public function getDescription(): string
    {
        return $this->description;
    }
    
    public function setDescription(string $description)
    {
        $this->description = $description;
    }
    
    public function getFullDescription(): string
    {
        return $this->fullDescription;
    }
    
    public function setFullDescription(string $fullDescription)
    {
        $this->fullDescription = $fullDescription;
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
    
    public function getUser(): User
    {
        return $this->user;
    }
    
    public function setUser(User $user): self
    {
        $this->user = $user;
        
        return $this;
    }
    
    public function getImages()
    {
        return $this->images->getValues();
    }
    
    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setCommunity($this);
        }
        
        return $this;
    }
    
    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getCommunity() === $this) {
                $image->setCommunity(null);
            }
        }
        
        return $this;
    }

    public function getProposals()
    {
        return $this->proposals->getValues();
    }
    
    public function addProposal(Proposal $proposal): self
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals[] = $proposal;
        }
        
        return $this;
    }
    
    public function removeProposal(Proposal $proposal): self
    {
        if ($this->proposals->contains($proposal)) {
            $this->proposals->removeElement($proposal);
        }
        
        return $this;
    }

    public function getCommunityUsers()
    {
        return $this->communityUsers->getValues();
    }
    
    public function addCommunityUser(CommunityUser $communityUser): self
    {
        if (!$this->communityUsers->contains($communityUser)) {
            $this->communityUsers[] = $communityUser;
            $communityUser->setCommunity($this);
        }
        
        return $this;
    }
    
    public function removeCommunityUser(CommunityUser $communityUser): self
    {
        if ($this->communityUsers->contains($communityUser)) {
            $this->communityUsers->removeElement($communityUser);
            // set the owning side to null (unless already changed)
            if ($communityUser->getCommunity() === $this) {
                $communityUser->setCommunity(null);
            }
        }
        
        return $this;
    }

    public function getCommunitySecurities()
    {
        return $this->communitySecurities->getValues();
    }
    
    public function addCommunitySecurity(CommunitySecurity $communitySecurity): self
    {
        if (!$this->communitySecurities->contains($communitySecurity)) {
            $this->communitySecurities[] = $communitySecurity;
            $communitySecurity->setCommunity($this);
        }
        
        return $this;
    }
    
    public function removeCommunitySecurity(CommunityUser $communitySecurity): self
    {
        if ($this->communitySecurities->contains($communitySecurity)) {
            $this->communitySecurities->removeElement($communitySecurity);
            // set the owning side to null (unless already changed)
            if ($communitySecurity->getCommunity() === $this) {
                $communitySecurity->setCommunity(null);
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
}
