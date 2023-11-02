<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\User\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\App\Entity\App;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * An Sso Account owned by a User.
 *
 * @ORM\Entity
 *
 * @ORM\HasLifecycleCallbacks
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SsoAccount
{
    /**
     * @var int the id of this SSO Account
     *
     * @ORM\Id
     *
     * @ORM\GeneratedValue
     *
     * @ORM\Column(type="integer")
     *
     * @Groups({"readSsoAccount"})
     *
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var User The owner of this SSO Account
     *
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User", inversedBy="ssoAccounts")
     *
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     */
    private $user;

    /**
     * @var null|string External ID of the user for a SSO Account
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"readSsoAccount"})
     */
    private $ssoId;

    /**
     * @var null|string External Provider for a SSO Account
     *
     * @ORM\Column(type="string", length=255)
     *
     * @Groups({"readSsoAccount"})
     */
    private $ssoProvider;

    /**
     * @var null|App app that create the user
     *
     * @ORM\ManyToOne(targetEntity="\App\App\Entity\App")
     *
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Groups({"readUser","write"})
     *
     * @MaxDepth(1)
     */
    private $appDelegate;

    /**
     * @var null|bool true : the user has been created by sso (false mean no sso or only attached a previously existing account)
     *
     * @ORM\Column(type="boolean")
     *
     * @Groups({"readSsoAccount"})
     */
    private $createdBySso;

    /**
     * @var \DateTimeInterface creation date of this SSO Account
     *
     * @ORM\Column(type="datetime")
     *
     * @Groups({"readSsoAccount"})
     */
    private $createdDate;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getSsoId(): ?string
    {
        return $this->ssoId;
    }

    public function setSsoId(?string $ssoId): self
    {
        $this->ssoId = $ssoId;

        return $this;
    }

    public function getSsoProvider(): ?string
    {
        return $this->ssoProvider;
    }

    public function setSsoProvider(?string $ssoProvider): self
    {
        $this->ssoProvider = $ssoProvider;

        return $this;
    }

    public function getAppDelegate(): ?App
    {
        return $this->appDelegate;
    }

    public function setAppDelegate(?App $appDelegate): self
    {
        $this->appDelegate = $appDelegate;

        return $this;
    }

    public function isCreatedBySso(): ?bool
    {
        return (is_null($this->createdBySso)) ? false : $this->createdBySso;
    }

    public function setCreatedBySso(?bool $createdBySso): self
    {
        $this->createdBySso = $createdBySso;

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

    // DOCTRINE EVENTS

    /**
     * Creation date.
     *
     * @ORM\PrePersist
     */
    public function setAutoCreatedDate()
    {
        $this->setCreatedDate(new \DateTime());
    }
}
