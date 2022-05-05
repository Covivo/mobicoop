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
 */

namespace Mobicoop\Bundle\MobicoopBundle\Community\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *  A community.
 */
class Community implements ResourceInterface, \JsonSerializable
{
    public const AUTO_VALIDATION = 0;
    public const MANUAL_VALIDATION = 1;
    public const DOMAIN_VALIDATION = 2;

    public const SESSION_VAR_NAME = 'userCommunities';

    /**
     * @var int the id of this community
     */
    private $id;

    /**
     * @var null|string the iri of this community
     *
     * @Groups({"post","put"})
     */
    private $iri;

    /**
     * @var string the name of the community
     *
     * @Groups({"post","put"})
     */
    private $name;

    /**
     * @var string urlKey of the community
     */
    private $urlKey;

    /**
     * @var null|bool members are only visible by the members of the community
     *
     * @Groups({"post","put"})
     */
    private $membersHidden;

    /**
     * @var null|bool proposals are only visible by the members of the community
     *
     * @Groups({"post","put"})
     */
    private $proposalsHidden;

    /**
     * @var int the type of validation (automatic/manual/domain)
     *
     * @Groups({"post","put"})
     */
    private $validationType;

    /**
     * @var null|string the domain of the community
     *
     * @Groups({"post","put"})
     */
    private $domain;

    /**
     * @var string the short description of the community
     *
     * @Groups({"post","put"})
     */
    private $description;

    /**
     * @var string the full description of the community
     *
     * @Groups({"post","put"})
     */
    private $fullDescription;

    /**
     * @var \DateTimeInterface creation date of the community
     *
     * @Groups("post")
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date of the community
     *
     * @Groups("post")
     */
    private $updatedDate;

    /**
     * @var User the creator of the community
     *
     * @Assert\NotBlank
     * @Groups({"post","put"})
     */
    private $user;

    /**
     * @var Address the address of the community
     *
     * @Groups({"post","put"})
     * @Assert\NotBlank(groups={"create","update"})
     */
    private $address;

    /**
     * @var null|Image[] the images of the community
     *
     * @Groups({"post","put"})
     */
    private $images;

    /**
     * @var string Url of the default Avatar for a community
     */
    private $defaultAvatar;

    /**
     * @var null|Proposal[] the proposals in this community
     *
     * @Groups({"post","put"})
     */
    private $proposals;

    /**
     * @var null|CommunityUser[] the members of the community
     *
     * @Groups({"post","put"})
     */
    private $communityUsers;

    /**
     * @var null|bool the community is secured
     *
     * @Groups({"post","put"})
     */
    private $secured;

    /**
     * @var null|bool the community is private
     *
     * @Groups({"post","put"})
     */
    private $private;

    /**
     * @var null|bool If the current user asking is member of the community
     */
    private $member;

    /**
     * @var null|int If the current user asking is member of the community this is his membership status (cf. CommunityUser status)
     */
    private $memberStatus;

    /**
     * @var null|bool Number of members of this community
     */
    private $nbMembers;

    /**
     * @var null|array Store the ads of the community
     */
    private $ads;

    /**
     * @var string the login to join the community if the community is secured
     * @Groups({"put"})
     */
    private $login;

    /**
     * @var string the password to join the community if the community is secured
     * @Groups({"put"})
     */
    private $password;

    /**
     * @var string the community main image
     * @Groups({"post","put","get"})
     */
    private $image;

    public function __construct($id = null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri('/communities/'.$id);
        }
        $this->images = new ArrayCollection();
        $this->proposals = new ArrayCollection();
        $this->communityUsers = new ArrayCollection();
        $this->setSecured(false);
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
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

    public function getName(): ?string
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

    public function isMembersHidden(): ?bool
    {
        return $this->membersHidden;
    }

    public function setMembersHidden(?bool $isMembersHidden): self
    {
        $this->membersHidden = boolval($isMembersHidden);

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

    public function getValidationType()
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description)
    {
        $this->description = $description;
    }

    public function getFullDescription(): ?string
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

    public function getUser(): ?User
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

    /**
     * @return Collection|Image[]
     */
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

    /**
     * @return Collection|Proposal[]
     */
    public function getProposals(): Collection
    {
        return $this->proposals;
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

    /**
     * @return Collection|CommunityUser[]
     */
    public function getCommunityUsers(): Collection
    {
        return $this->communityUsers;
    }

    public function addCommunityUser(CommunityUser $communityUser): self
    {
        if (!$this->communityUsers->contains($communityUser)) {
            $this->communityUsers[] = $communityUser;
            //$communityUser->setCommunity($this);
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

    public function isSecured(): ?bool
    {
        return $this->secured;
    }

    public function setSecured(bool $secured): self
    {
        $this->secured = $secured;

        return $this;
    }

    public function isPrivate(): ?bool
    {
        return $this->private;
    }

    public function setPrivate(?bool $private): self
    {
        $this->private = $private;

        return $this;
    }

    public function isMember(): ?bool
    {
        return $this->member;
    }

    public function setMember(?bool $member): self
    {
        $this->member = boolval($member);

        return $this;
    }

    public function getMemberStatus(): ?int
    {
        return $this->memberStatus;
    }

    public function setMemberStatus(?int $memberStatus): self
    {
        $this->memberStatus = $memberStatus;

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

    public function getNbMembers()
    {
        return $this->nbMembers;
    }

    public function setNbMembers(?int $nbMembers): self
    {
        $this->nbMembers = $nbMembers;

        return $this;
    }

    public function getLogin(): ?string
    {
        return $this->login;
    }

    public function setLogin(?string $login): self
    {
        $this->login = $login;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function jsonSerialize()
    {
        return
        [
            'id' => $this->getId(),
            'iri' => $this->getIri(),
            'name' => $this->getName(),
            'urlKey' => $this->getUrlKey(),
            'description' => $this->getDescription(),
            'defaultAvatar' => $this->getDefaultAvatar(),
            'images' => $this->getImages(),
            'fullDescription' => $this->getFullDescription(),
            'proposalsHidden' => $this->isProposalsHidden(),
            'membersHidden' => $this->isMembersHidden(),
            'address' => $this->getAddress(),
            'user' => $this->getUser(),
            'isSecured' => $this->isSecured(),
            'validationType' => $this->getValidationType(),
            'domain' => $this->getDomain(),
            'isMember' => $this->isMember(),
            'memberStatus' => $this->getMemberStatus(),
            'ads' => $this->getAds(),
            'nbMembers' => $this->getNbMembers(),
            'image' => $this->getImage(),
        ];
    }
}
