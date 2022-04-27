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

namespace Mobicoop\Bundle\MobicoopBundle\Event\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\Community;
use Mobicoop\Bundle\MobicoopBundle\Gamification\Entity\GamificationEntity;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An event.
 */
class Event extends GamificationEntity implements ResourceInterface, \JsonSerializable
{
    /**
     * @var int the id of this event
     */
    private $id;

    /**
     * @var null|string the iri of this event
     *
     * @Groups({"post","put"})
     */
    private $iri;

    /**
     * @var string the name of the event
     *
     * @Groups({"post","put"})
     * @Assert\NotBlank(groups={"create","update"})
     */
    private $name;

    /**
     * @var string the urlkey of the event
     */
    private $urlKey;

    /**
     * @var int the status of the event (active/inactive)
     *
     * @Groups({"post","put"})
     */
    private $status;

    /**
     * @var bool Private event. Should be filtered when event list is publicly displayed.
     *
     *  @Groups({"post","put"})
     */
    private $private;

    /**
     * @var string the short description of the event
     *
     * @Groups({"post","put"})
     * @Assert\NotBlank(groups={"create","update"})
     */
    private $description;

    /**
     * @var string the full description of the event
     *
     * @Groups({"post","put"})
     * @Assert\NotBlank(groups={"create","update"})
     */
    private $fullDescription;

    /**
     * @var \DateTimeInterface the starting date of the event
     *
     * @Assert\NotBlank(groups={"create","update"})
     * @Groups({"post","put"})
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface the ending date of the event
     *
     * @Assert\NotBlank
     * @Groups({"post","put"})
     */
    private $toDate;

    /**
     * @var bool use the time for the starting and ending date of the event
     *
     * @Groups({"post","put"})
     * @Assert\NotBlank(groups={"create","update"})
     */
    private $useTime;

    /**
     * @var string the information url for the event
     *
     * @Groups({"post","put"})
     */
    private $url;

    /**
     * @var Address the address of the event
     *
     * @Groups({"post","put"})
     * @Assert\NotBlank(groups={"create","update"})
     */
    private $address;

    /**
     * @var User the creator of the event
     *
     * @Groups({"post","put"})
     * @Assert\NotBlank(groups={"create","update"})
     */
    private $user;

    /**
     * @var null|Image[] The images of the event.
     *
     * Groups({"post","put"})
     * @Assert\Valid
     */
    private $images;

    /**
     * @var string Url of the default Avatar for an Event
     */
    private $defaultAvatar;

    /**
     * @var string the url of the image of the external event
     */
    private $externalImageUrl;

    /**
     * @var Community the community linked to the event
     *
     * @Groups({"post","put"})
     */
    private $community;

    public function __construct($id = null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri('/events/'.$id);
        }
        $this->images = new ArrayCollection();
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

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status)
    {
        $this->status = $status;
    }

    public function isPrivate(): bool
    {
        return $this->private ? true : false;
    }

    public function setPrivate(bool $private)
    {
        $this->private = $private;
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

    public function getFromDate(): ?\DateTimeInterface
    {
        return $this->fromDate;
    }

    public function setFromDate(\DateTimeInterface $fromDate): self
    {
        $this->fromDate = $fromDate;

        return $this;
    }

    public function getToDate(): ?\DateTimeInterface
    {
        return $this->toDate;
    }

    public function setToDate(\DateTimeInterface $toDate): self
    {
        $this->toDate = $toDate;

        return $this;
    }

    public function getUseTime(): ?bool
    {
        return $this->useTime;
    }

    public function setUseTime(bool $useTime): self
    {
        $this->useTime = $useTime;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url)
    {
        $this->url = $url;
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

    public function setAddress(Address $address): self
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
            $image->setEvent($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->contains($image)) {
            $this->images->removeElement($image);
            // set the owning side to null (unless already changed)
            if ($image->getEvent() === $this) {
                $image->setEvent(null);
            }
        }

        return $this;
    }

    public function removeImages(): self
    {
        foreach ($this->images as $image) {
            $this->removeImage($image);
        }

        return $this;
    }

    public function getExternalImageUrl(): ?string
    {
        return $this->externalImageUrl;
    }

    public function setExternalImageUrl(?string $externalImageUrl)
    {
        $this->externalImageUrl = $externalImageUrl;
    }

    public function getCommunity(): ?Community
    {
        return $this->community;
    }

    public function setommunity(Community $community): self
    {
        $this->community = $community;

        return $this;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => $this->getId(),
                'iri' => $this->getIri(),
                'name' => $this->getName(),
                'urlKey' => $this->getUrlKey(),
                'status' => $this->getStatus(),
                'private' => $this->isPrivate(),
                'fullDescription' => $this->getFullDescription(),
                'description' => $this->getDescription(),
                'fromDate' => $this->getFromDate(),
                'toDate' => $this->getToDate(),
                'useTime' => $this->getUseTime(),
                'url' => $this->getUrl(),
                'address' => $this->getAddress(),
                'user' => $this->getUser(),
                'images' => $this->getImages(),
                'defaultAvatar' => $this->getDefaultAvatar(),
                'externalImageUrl' => $this->getExternalImageUrl(),
                'gamificationNotifications' => $this->getGamificationNotifications(),
                'community' => $this->getCommunity(),
            ];
    }
}
