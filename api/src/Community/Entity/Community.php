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

namespace App\Community\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiSubresource;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Image\Entity\Image;
use App\User\Entity\User;
use App\Geography\Entity\Address;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Carpool\Entity\Proposal;
use App\Community\Controller\JoinAction;
use App\RelayPoint\Entity\RelayPoint;
use App\Community\Filter\TerritoryFilter;
use App\Match\Entity\Mass;

/**
 * A community : a group of users sharing common interests.
 *
 * @ORM\Entity()
 * @UniqueEntity("name")
 * @ORM\HasLifecycleCallbacks
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readCommunity"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"write"}},
 *          "pagination_client_items_per_page"=true
 *      },
 *      collectionOperations={
 *          "get"={
 *              "swagger_context" = {
 *                  "parameters" = {
 *                      {
 *                          "name" = "userId",
 *                          "in" = "query",
 *                          "type" = "number",
 *                          "format" = "integer",
 *                          "description" = "Check if this userId is already an accepted member"
 *                      }
 *                  }
 *              },
 *              "normalization_context"={"groups"={"communities"}},
 *              "security_post_denormalize"="is_granted('community_list',object)"
 *          },
 *          "post"={
 *              "security_post_denormalize"="is_granted('community_create',object)"
 *          },
 *          "available"={
 *              "method"="GET",
 *              "path"="/communities/available",
 *              "normalization_context"={"groups"={"read"}},
 *              "swagger_context" = {
 *                  "parameters" = {
 *                      {
 *                          "name" = "userId",
 *                          "in" = "query",
 *                          "type" = "number",
 *                          "format" = "integer",
 *                          "description" = "The id of the user for which we want the communities"
 *                      }
 *                  }
 *              },
 *              "security_post_denormalize"="is_granted('community_list',object)"
 *          },
 *          "exists"={
 *              "method"="GET",
 *              "path"="/communities/exists",
 *              "normalization_context"={"groups"={"existsCommunity"}},
 *              "swagger_context" = {
 *                  "parameters" = {
 *                      {
 *                          "name" = "name",
 *                          "in" = "query",
 *                          "type" = "string",
 *                          "required" = "true",
 *                          "description" = "The name of the community"
 *                      }
 *                  }
 *              },
 *              "security_post_denormalize"="is_granted('community_list',object)"
 *          },
 *          "owned"={
 *              "method"="GET",
 *              "path"="/communities/owned",
 *              "normalization_context"={"groups"={"readCommunity"}},
 *              "security_post_denormalize"="is_granted('community_list',object)"
 *          },
 *          "ismember"={
 *              "method"="GET",
 *              "path"="/communities/ismember",
 *              "normalization_context"={"groups"={"readCommunity"}},
 *              "security_post_denormalize"="is_granted('community_list',object)"
 *          },
 *          "manage"={
 *              "normalization_context"={"groups"={"readCommunity","readCommunityAdmin"}},
 *              "method"="GET",
 *              "path"="/communities/manage",
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/communities",
 *              "method"="GET",
 *              "normalization_context"={
 *                  "groups"={"aRead"},
 *                  "skip_null_values"=false
 *              },
 *              "security"="is_granted('admin_community_list',object)"
 *          },
 *          "ADMIN_post"={
 *              "path"="/admin/communities",
 *              "method"="POST",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_community_create',object)",
 *          },
 *          "ADMIN_moderated"={
 *              "method"="GET",
 *              "path"="/admin/communities/moderated",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "security_post_denormalize"="is_granted('admin_community_list',object)"
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *              "security"="is_granted('community_read',object)"
 *          },
 *          "public"={
 *              "method"="GET",
 *              "path"="/communities/{id}/public",
 *              "normalization_context"={"groups"={"readCommunityPublic"}},
 *          },
 *          "put"={
 *              "method"="PUT",
 *              "security"="is_granted('community_update',object)"
 *          },
 *          "delete"={
 *              "security"="is_granted('community_delete',object)"
 *          },
 *          "ADMIN_get"={
 *              "path"="/admin/communities/{id}",
 *              "method"="GET",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "security"="is_granted('admin_community_read',object)"
 *          },
 *          "ADMIN_patch"={
 *              "path"="/admin/communities/{id}",
 *              "method"="PATCH",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_community_update',object)"
 *          },
 *          "ADMIN_delete"={
 *              "path"="/admin/communities/{id}",
 *              "method"="DELETE",
 *              "normalization_context"={"groups"={"aRead"}},
 *              "denormalization_context"={"groups"={"aWrite"}},
 *              "security"="is_granted('admin_community_delete',object)"
 *          },
 *      }
 * )
 * @ApiFilter(OrderFilter::class, properties={"id", "name", "description", "createdDate"}, arguments={"orderParameterName"="order"})
 * @ApiFilter(SearchFilter::class, properties={"name":"partial"})
 * @ApiFilter(NumericFilter::class, properties={"communityUsers.user.id"})
 * @ApiFilter(TerritoryFilter::class, properties={"territory"})
 */
class Community
{
    const AUTO_VALIDATION = 0;
    const MANUAL_VALIDATION = 1;
    const DOMAIN_VALIDATION = 2;

    /**
     * @var int The id of this community.
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"aRead","readCommunity","readCommunityUser","results","existsCommunity","communities","readUserAdmin"})
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var string The name of the community.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","aWrite","readCommunity","readCommunityUser","write","results","existsCommunity","communities","readCommunityPublic","readUserAdmin","readUser"})
     */
    private $name;

    /**
     * @var string UrlKey of the community.
     *
     * @Groups({"readCommunity","readCommunityUser","write","results","existsCommunity","communities","readCommunityPublic","readUserAdmin","readUser"})
     */
    private $urlKey;

    /**
     * @var int Community status.
     *
     * @ORM\Column(type="smallint", nullable=true)
     * @Groups({"aRead","aWrite","readCommunity","write","readUserAdmin"})
     */
    private $status;

    /**
     * @var boolean|null Members are only visible by the members of the community.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readCommunity","write","communities"})
     */
    private $membersHidden;

    /**
     * @var boolean|null Proposals are only visible by the members of the community.
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"aRead","aWrite","readCommunity","write","communities"})
     */
    private $proposalsHidden;

    /**
     * @var int|null The type of validation (automatic/manual/domain).
     *
     * @ORM\Column(type="smallint")
     * @Groups({"aRead","aWrite","readCommunity","write", "communities"})
     */
    private $validationType;

    /**
     * @var string|null The domain of the community.
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"aRead","aWrite","readCommunity","write"})
     */
    private $domain;

    /**
     * @var string The short description of the community.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="string", length=255)
     * @Groups({"aRead","aWrite","readCommunity","write","communities"})
     */
    private $description;

    /**
     * @var string The full description of the community.
     *
     * @Assert\NotBlank
     * @ORM\Column(type="text")
     * @Groups({"aRead","aWrite","readCommunity","write","communities"})
     */
    private $fullDescription;

    /**
    * @var \DateTimeInterface Creation date of the community.
    *
    * @ORM\Column(type="datetime")
    * @Groups({"aRead","readCommunity"})
    */
    private $createdDate;

    /**
     * @var \DateTimeInterface Updated date of the community.
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"aRead", "readCommunity","communities"})
     */
    private $updatedDate;

    /**
     * @var User The creator of the community.
     *
     * @ApiProperty(push=true)
     * @Assert\NotBlank(groups={"write"})
     * @ORM\ManyToOne(targetEntity="App\User\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readCommunity","readCommunityUser","write","results","existsCommunity","communities"})
     */
    private $user;

    /**
     * @var Address The address of the community.
     *
     * @ApiProperty(push=true)
     * @Assert\NotBlank
     * @ORM\OneToOne(targetEntity="\App\Geography\Entity\Address", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Groups({"aRead","aWrite","readCommunity","write"})
     * @MaxDepth(1)
     */
    private $address;

    /**
     * @var ArrayCollection|null The images of the community.
     *
     * @ApiProperty(push=true)
     * @ORM\OneToMany(targetEntity="\App\Image\Entity\Image", mappedBy="community", cascade={"persist","remove"}, orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     * @Groups({"readCommunity","readCommunityUser","write","communities"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $images;

    /**
     * @var string $defaultAvatar Url of the default Avatar for a community
     * @Groups({"readCommunity","readCommunityUser","write","communities"})
     */
    private $defaultAvatar;

    /**
     * @var ArrayCollection|null The proposals in this community.
     *
     * @ORM\ManyToMany(targetEntity="\App\Carpool\Entity\Proposal", mappedBy="communities")
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $proposals;

    /**
     * @var ArrayCollection|null The members of the community.
     *
     * @ApiProperty(push=true)
     * @ORM\OneToMany(targetEntity="\App\Community\Entity\CommunityUser", mappedBy="community", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"readCommunity","readCommunityUser","write","results","existsCommunity","communities","readCommunityPublic"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $communityUsers;

    /**
     * @var ArrayCollection|null The security files of the community.
     *
     * @ORM\OneToMany(targetEntity="\App\Community\Entity\CommunitySecurity", mappedBy="community", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"readCommunity","write","communities"})
     * @MaxDepth(1)
     * @ApiSubresource(maxDepth=1)
     */
    private $communitySecurities;

    /**
     * @var ArrayCollection|null The relay points related to the community.
     *
     * @ORM\OneToMany(targetEntity="\App\RelayPoint\Entity\RelayPoint", mappedBy="community", cascade={"persist","remove"}, orphanRemoval=true)
     * @Groups({"readCommunity","write"})
     * @MaxDepth(1)
     */
    private $relayPoints;

    /**
     * @var boolean|null If the current user asking is member of the community
     * @Groups({"readCommunity","communities"})
     */
    private $member;

    /**
     * @var int|null Number of members of this community
     * @Groups({"aRead","readCommunity","communities"})
     */
    private $nbMembers;
    
    /**
     * @var array|null Store the ads of the community
     * @Groups({"readCommunity","readCommunityUser","write","results","existsCommunity","communities","readCommunityPublic"})
     */
    private $ads;

    /**
     * @var Mass The community created after the migration of this mass users
     *
     * @ORM\OneToOne(targetEntity="App\Match\Entity\Mass", mappedBy="community")
     * @Groups({"readCommunity","communities"})
     */
    private $mass;

    /**
     * @var string The referrer
     * @Groups({"aRead","aWrite"})
     */
    private $referrer;

    /**
     * @var int The referrer id
     * @Groups({"aRead","aWrite"})
     */
    private $referrerId;

    /**
     * @var string|null The referrer avatar
     * @Groups({"aRead"})
     */
    private $referrerAvatar;

    /**
     * @var string|null The community main image
     * @Groups({"aRead","aWrite"})
     */
    private $image;

    /**
     * @var string|null The community avatar
     * @Groups({"aRead","aWrite"})
     */
    private $avatar;

    public function __construct($id=null)
    {
        $this->id = $id;
        $this->images = new ArrayCollection();
        $this->proposals = new ArrayCollection();
        $this->communityUsers = new ArrayCollection();
        $this->communitySecurities = new ArrayCollection();
        $this->relayPoints = new ArrayCollection();
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

    public function getUrlKey(): ?string
    {
        return $this->urlKey;
    }

    public function setUrlKey(?string $urlKey)
    {
        $this->urlKey = $urlKey;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(?int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function isMembersHidden(): ?bool
    {
        return $this->membersHidden;
    }

    public function setMembersHidden(?bool $isMembersHidden): self
    {
        $this->membersHidden = $isMembersHidden;

        return $this;
    }

    public function isProposalsHidden(): ?bool
    {
        return $this->proposalsHidden;
    }

    public function setProposalsHidden(?bool $isProposalsHidden): self
    {
        $this->proposalsHidden = boolval($isProposalsHidden);

        return $this;
    }

    public function getValidationType(): ?int
    {
        return $this->validationType;
    }

    public function setValidationType(?int $validationType)
    {
        $this->validationType = $validationType;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(?string $domain)
    {
        $this->domain = $domain;
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

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(?Address $address): self
    {
        $this->address = $address;
        $address->setCommunity($this);

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

    public function setDefaultAvatar(?string $defaultAvatar): self
    {
        $this->defaultAvatar = $defaultAvatar;

        return $this;
    }

    public function getDefaultAvatar(): ?string
    {
        return $this->defaultAvatar;
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

    public function getRelayPoints()
    {
        return $this->relayPoints->getValues();
    }

    public function addRelayPoint(RelayPoint $relayPoint): self
    {
        if (!$this->relayPoints->contains($relayPoint)) {
            $this->relayPoint[] = $relayPoint;
            $relayPoint->setCommunity($this);
        }

        return $this;
    }

    public function removeRelayPoint(RelayPoint $relayPoint): self
    {
        if ($this->relayPoint->contains($relayPoint)) {
            $this->relayPoint->removeElement($relayPoint);
            // set the owning side to null (unless already changed)
            if ($relayPoint->getCommunity() === $this) {
                $relayPoint->setCommunity(null);
            }
        }

        return $this;
    }

    public function isMember(): ?bool
    {
        return $this->member;
    }

    public function setMember(?bool $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getNbMembers(): ?int
    {
        return $this->nbMembers;
    }

    public function setNbMembers(?int $nbMembers): self
    {
        $this->nbMembers = $nbMembers;

        return $this;
    }

    public function getAds()
    {
        return $this->ads;
    }

    public function setAds(?array $ads): self
    {
        $this->ads = $ads;

        return $this;
    }

    public function getMass(): ?Mass
    {
        return $this->mass;
    }

    public function setMass(?Mass $mass): self
    {
        $this->mass = $mass;

        return $this;
    }

    public function getReferrer(): string
    {
        return ucfirst(strtolower($this->getUser()->getGivenName())) . " " . $this->getUser()->getShortFamilyName();
    }

    public function getReferrerId(): int
    {
        if (is_null($this->referrerId)) {
            return $this->getUser()->getId();
        }
        return $this->referrerId;
    }

    public function setReferrerId(?int $referrerId)
    {
        $this->referrerId = $referrerId;
    }

    public function getReferrerAvatar(): ?string
    {
        if (count($this->getUser()->getAvatars())>0) {
            return $this->getUser()->getAvatars()[0];
        }
        return null;
    }

    public function getImage(): ?string
    {
        if (count($this->getImages())>0 && isset($this->getImages()[0]->getVersions()['square_800'])) {
            return $this->getImages()[0]->getVersions()['square_800'];
        }
        return null;
    }

    public function getAvatar(): ?string
    {
        if (count($this->getImages())>0 && isset($this->getImages()[0]->getVersions()['square_250'])) {
            return $this->getImages()[0]->getVersions()['square_250'];
        }
        return null;
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
     * Validation type.
     *
     * @ORM\PrePersist
     */
    public function setAutoValidationType()
    {
        if ($this->getDomain()) {
            $this->setValidationType(SELF::DOMAIN_VALIDATION);
        } else {
            $this->setValidationType(SELF::AUTO_VALIDATION);
        }
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
