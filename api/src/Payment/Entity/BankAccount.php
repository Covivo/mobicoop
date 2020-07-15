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

namespace App\Payment\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Bank Account
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readPayment"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writePayment"}}
 *      },
 *      collectionOperations={"get","post"},
 *      itemOperations={"get","put"}
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankAccount
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * @var int The id of this bank account
     *
     * @Groups({"readPayment"})
     */
    private $id;

    /**
     * @var User The user owning this bank account
     *
     * @Groups({"readPayment","writePayment"})
     * @MaxDepth(1)
     * @Assert\NotBlank
     */
    private $user;

    /**
     * @var string The litteral name of the user owning this bank account
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $userLitteral;

    /**
     * @var string The provider managing this bank account
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $provider;

    /**
     * @var string The iban number of this bank account
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $iban;

    /**
     * @var string The bic number of this bank account
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $bic;

    /**
     * @var string A comment for this bank account
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $comment;

    /**
     * @var int The status of this payment profil (0 : Inactive, 1 : Active)
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $status;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @Groups({"readPayment"})
     */
    private $createdDate;

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

    public function getProvider(): String
    {
        return $this->provider;
    }

    public function setProvider(String $provider): self
    {
        $this->provider = $provider;

        return $this;
    }

    public function getUserLitteral(): String
    {
        return $this->userLitteral;
    }

    public function setUserLitteral(String $userLitteral): self
    {
        $this->userLitteral = $userLitteral;

        return $this;
    }

    public function getIban(): String
    {
        return $this->iban;
    }

    public function setIban(String $iban): self
    {
        $this->iban = $iban;

        return $this;
    }

    public function getBic(): String
    {
        return $this->bic;
    }

    public function setBic(String $bic): self
    {
        $this->bic = $bic;

        return $this;
    }

    public function getComment(): String
    {
        return $this->comment;
    }

    public function setComment(String $comment): self
    {
        $this->comment = $comment;

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
