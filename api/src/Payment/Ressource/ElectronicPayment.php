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

namespace App\Payment\Ressource;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * An Electronic Payment
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readPayment"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writePayment"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)"
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('electronic_payment_create',object)"
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)"
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ElectronicPayment
{
    const DEFAULT_ID = "999999999999";

    /**
     * @var int The id of this payment
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readPayment"})
     */
    private $id;

    /**
     * @var User Author of this Payment
     *
     * @Assert\NotBlank
     * @Groups({"readPayment","writePayment"})
     */
    private $author;

    /**
     * @var User Recipient of this Payment
     *
     * @Assert\NotBlank
     * @Groups({"readPayment","writePayment"})
     */
    private $recipient;
    
    /**
     * @var int Amount of this Payment (in cents)
     *
     * @Assert\NotBlank
     * @Groups({"readPayment","writePayment"})
     */
    private $amount;

    /**
     * @var \DateTimeInterface Creation date.
     *
     * @Groups({"readPayment"})
     */
    private $createdDate;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(String $id): self
    {
        $this->id = $id;
        
        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;
        
        return $this;
    }

    public function getRecipient(): ?User
    {
        return $this->recipient;
    }

    public function setRecipient(User $recipient): self
    {
        $this->recipient = $recipient;
        
        return $this;
    }

    public function getAmount(): ?int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;
        
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
