<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\App\Entity;

use App\Auth\Entity\AuthItem;
use Doctrine\Common\Collections\ArrayCollection;
// use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * An app which can have access to the api : front (web), mobile or any other app.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"read"}, "enable_max_depth"="true"}
 *      },
 *      collectionOperations={"get"},
 *      itemOperations={"get"}
 * )
 */
class App implements UserInterface, EquatableInterface, PasswordAuthenticatedUserInterface
{
    // default role
    public const DEFAULT_ROLE = 1;

    /**
     * @var int the id of this app
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("read")
     */
    private $id;

    /**
     * @var null|string the name of the app
     *
     * @ORM\Column(type="string", length=45)
     * @Groups("read")
     */
    private $name;

    /**
     * @var null|string the username of the app (for authentication)
     *
     * @ORM\Column(type="string", length=45)
     * @Groups("read")
     */
    private $username;

    /**
     * @var string the encoded password of the app
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"read","write"})
     */
    private $password;

    /**
     * @var null|ArrayCollection the auth assignments for the app
     *
     * @ORM\ManyToMany(targetEntity="\App\Auth\Entity\AuthItem")
     * @Groups({"read","write"})
     */
    private $authItems;

    /**
     * @var \DateTimeInterface creation date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"read"})
     */
    private $updatedDate;

    public function __construct()
    {
        $this->authItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
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

    public function getRoles(): array
    {
        // we return an array of ROLE_***
        $roles = [];
        foreach ($this->authItems as $authItem) {
            if (AuthItem::TYPE_ROLE == $authItem->getType()) {
                $roles[] = $authItem->getName();
            }
        }

        return $roles;
    }

    public function getAuthItems()
    {
        return $this->authItems->getValues();
    }

    public function addAuthItem(AuthItem $authItem): self
    {
        if (!$this->authItems->contains($authItem)) {
            $this->authItems[] = $authItem;
        }

        return $this;
    }

    public function removeAuthItem(AuthItem $authItem): self
    {
        if ($this->authItems->contains($authItem)) {
            $this->authItems->removeElement($authItem);
        }

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials()
    {
    }

    public function isEqualTo(UserInterface $app): bool
    {
        if (!$app instanceof App) {
            return false;
        }

        if ($this->password !== $app->getPassword()) {
            return false;
        }

        if ($this->email !== $app->getUsername()) {
            return false;
        }

        return true;
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

    public function getRefresh()
    {
        return $this->username;
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
