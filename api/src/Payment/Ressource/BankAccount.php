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

namespace App\Payment\Ressource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\Geography\Entity\Address;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Bank Account.
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readPayment"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writePayment"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('bank_account_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          },
 *          "disable"={
 *              "normalization_context"={"groups"={"readPayment"}},
 *              "method"="GET",
 *              "path"="/bank_accounts/disable",
 *              "read"="false",
 *              "security"="is_granted('bank_account_disable',object)",
 *              "swagger_context" = {
 *                  "tags"={"Payment"},
 *                  "parameters" = {
 *                      {
 *                          "name" = "idBankAccount",
 *                          "type" = "int",
 *                          "required" = true,
 *                          "description" = "Id of the bank account"
 *                      }
 *                  }
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
 *          }
 *      }
 * )
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankAccount
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

    public const DEFAULT_ID = '999999999999';

    /**
     * @var string The id of this bank account
     *
     * @ApiProperty(identifier=true)
     *
     * @Groups({"readPayment"})
     */
    private $id;

    /**
     * @var null|string The identifier of the user owning this bank account
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $userIdentifier;

    /**
     * @var null|string The litteral name of the user owning this bank account
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $userLitteral;

    /**
     * @var null|Address The litteral name of the user owning this bank account
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $address;

    /**
     * @var string The iban number of this bank account
     *
     * @Assert\NotBlank
     *
     * @Assert\Iban
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $iban;

    /**
     * @var null|string The bic number of this bank account
     *
     * @Assert\Bic
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $bic;

    /**
     * @var null|string A comment for this bank account
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $comment;

    /**
     * @var int The status of this bank account (0 : Inactive, 1 : Active)
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $status;

    /**
     * @var int The validation status of this bank account (0 : pending, 1 : validated, 2 : rejected, 3 : outdated)
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $validationStatus;

    /**
     * @var int The reason why the identity document associated to the bankaccount is not validated
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $refusalReason;

    /**
     * @var \DateTimeInterface Date when the validation has been asked to the payment provider
     *
     * @Groups({"readPayment"})
     */
    private $validationAskedDate;

    /**
     * @var \DateTimeInterface Date when the validation has been granted by the payment provider
     *
     * @Groups({"readPayment"})
     */
    private $validatedDate;

    /**
     * @var \DateTimeInterface Date when the validation has been declared outdated by the payment provider
     *
     * @Groups({"readPayment"})
     */
    private $validationOutdatedDate;

    /**
     * @var \DateTimeInterface creation date
     *
     * @Groups({"readPayment"})
     */
    private $createdDate;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(string $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUserIdentifier(): ?string
    {
        return $this->userIdentifier;
    }

    public function setUserIdentifier(?string $userIdentifier): self
    {
        $this->userIdentifier = $userIdentifier;

        return $this;
    }

    public function getUserLitteral(): ?string
    {
        return $this->userLitteral;
    }

    public function setUserLitteral(?string $userLitteral): self
    {
        $this->userLitteral = $userLitteral;

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

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(?string $iban): self
    {
        $this->iban = $iban;

        return $this;
    }

    public function getBic(): ?string
    {
        return $this->bic;
    }

    public function setBic(?string $bic): self
    {
        $this->bic = $bic;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
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

    public function getValidationStatus(): ?int
    {
        return $this->validationStatus;
    }

    public function setValidationStatus(?int $validationStatus)
    {
        $this->validationStatus = $validationStatus;
    }

    public function getRefusalReason(): ?int
    {
        return $this->refusalReason;
    }

    public function setRefusalReason(?int $refusalReason)
    {
        $this->refusalReason = $refusalReason;
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

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->createdDate;
    }

    public function setCreatedDate(\DateTimeInterface $createdDate): self
    {
        $this->createdDate = $createdDate;

        return $this;
    }
}
