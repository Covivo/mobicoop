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
 */

namespace App\Payment\Entity;

use ApiPlatform\Core\Annotation\ApiProperty;
use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A payment profile.
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PaymentProfile
{
    public const STATUS_INACTIVE = 0;
    public const STATUS_ACTIVE = 1;

    public const VALIDATION_PENDING = 0;
    public const VALIDATION_VALIDATED = 1;
    public const VALIDATION_REJECTED = 2;
    public const VALIDATION_OUTDATED = 3;

    public const OUT_OF_DATE = 1;
    public const UNDERAGE_PERSON = 2;
    public const DOCUMENT_FALSIFIED = 3;
    public const DOCUMENT_MISSING = 4;
    public const DOCUMENT_HAS_EXPIRED = 5;
    public const DOCUMENT_NOT_ACCEPTED = 6;
    public const DOCUMENT_DO_NOT_MATCH_USER_DATA = 7;
    public const DOCUMENT_UNREADABLE = 8;
    public const DOCUMENT_INCOMPLETE = 9;
    public const SPECIFIC_CASE = 10;

    /**
     * @var int The id of this payment profile
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"readPayment"})
     */
    private $id;

    /**
     * @var User The user owning this payment profile
     *
     * @ApiProperty(push=true)
     * @ORM\ManyToOne(targetEntity="\App\User\Entity\User")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @Groups({"readPayment","writePayment"})
     * @MaxDepth(1)
     * @Assert\NotBlank
     */
    private $user;

    /**
     * @var string The provider managing this payment profile
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"readPayment","writePayment"})
     */
    private $provider;

    /**
     * @var string The id used by the provider of this payment profile
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"readPayment","writePayment"})
     */
    private $identifier;

    /**
     * @var string The id used by the provider for a validation (i.e KYC document...)
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"readPayment","writePayment"})
     */
    private $validationId;

    /**
     * @var int The status of this payment profile (0 : Inactive, 1 : Active)
     *
     * @ORM\Column(type="integer")
     * @Groups({"readPayment","writePayment"})
     */
    private $status;

    /**
     * @var bool If the current payment profile is linked to one or several bank accounts
     *
     * @ORM\Column(type="boolean")
     * @Groups({"readPayment","writePayment"})
     */
    private $electronicallyPayable;

    /**
     * @var int The validation status of the profile (0 : pending, 1 : validated, 2 : rejected, 3 : outdated)
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"readPayment","writePayment"})
     */
    private $validationStatus;

    /**
     * @var \DateTimeInterface creation date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readPayment"})
     */
    private $createdDate;

    /**
     * @var \DateTimeInterface updated date
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readPayment"})
     */
    private $updatedDate;

    /**
     * @var \DateTimeInterface Date when the validation has been asked to the payment provider
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readPayment"})
     */
    private $validationAskedDate;

    /**
     * @var \DateTimeInterface Date when the validation has been granted by the payment provider
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readPayment"})
     */
    private $validatedDate;

    /**
     * @var \DateTimeInterface Date when the validation has been declared outdated by the payment provider
     *
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"readPayment"})
     */
    private $validationOutdatedDate;

    /**
     * @var null|array A user Bank accounts
     * @Groups({"readPayment","writePayment"})
     */
    private $bankAccounts;

    /**
     * @var null|array A user wallets
     * @Groups({"readPayment"})
     */
    private $wallets;

    /**
     * @var null|int The reason why the document is refused
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"readPayment","writePayment"})
     */
    private $refusalReason;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): self
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function getValidationId(): ?string
    {
        return $this->validationId;
    }

    public function setValidationId(?string $validationId): self
    {
        $this->validationId = $validationId;

        return $this;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getValidationStatus(): ?int
    {
        return $this->validationStatus;
    }

    public function setValidationStatus(?int $validationStatus): self
    {
        $this->validationStatus = $validationStatus;

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

    public function getValidationAskedDate(): ?\DateTimeInterface
    {
        return $this->validationAskedDate;
    }

    public function setValidationAskedDate(?\DateTimeInterface $validationAskedDate): self
    {
        $this->validationAskedDate = $validationAskedDate;

        return $this;
    }

    public function getValidatedDate(): ?\DateTimeInterface
    {
        return $this->validatedDate;
    }

    public function setValidatedDate(?\DateTimeInterface $validatedDate): self
    {
        $this->validatedDate = $validatedDate;

        return $this;
    }

    public function getValidationOutdatedDate(): ?\DateTimeInterface
    {
        return $this->validationOutdatedDate;
    }

    public function setValidationOutdatedDate(?\DateTimeInterface $validationOutdatedDate): self
    {
        $this->validationOutdatedDate = $validationOutdatedDate;

        return $this;
    }

    public function getBankAccounts(): ?array
    {
        return $this->bankAccounts;
    }

    public function setBankAccounts(array $bankAccounts): self
    {
        $this->bankAccounts = $bankAccounts;

        return $this;
    }

    public function getWallets(): ?array
    {
        return $this->wallets;
    }

    public function setWallets(array $wallets): self
    {
        $this->wallets = $wallets;

        return $this;
    }

    public function isElectronicallyPayable(): ?bool
    {
        return $this->electronicallyPayable;
    }

    public function setElectronicallyPayable(bool $electronicallyPayable): self
    {
        $this->electronicallyPayable = $electronicallyPayable;

        return $this;
    }

    public function getRefusalReason(): ?int
    {
        return $this->refusalReason;
    }

    public function setRefusalReason(int $refusalReason): self
    {
        $this->refusalReason = $refusalReason;

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

    /**
     * Update date.
     *
     * @ORM\PreUpdate
     */
    public function setAutoUpdatedDate()
    {
        $this->setUpdatedDate(new \DateTime());
    }
}
