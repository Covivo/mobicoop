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

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Geography\Entity\Address;

/**
 * A payment item.
 *
 * @ApiResource(
 *     attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readPayment"}, "enable_max_depth"="true"}
 *     },
 *     collectionOperations={
 *          "get"={
  *              "swagger_context" = {
 *                  "tags"={"Payment"}
 *              }
*          }
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
 *  @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class PaymentItem
{
    public const DEFAULT_ID = 999999999999;

    public const FREQUENCY_PUNCTUAL = 1;
    public const FREQUENCY_REGULAR = 2;

    public const TYPE_PAY = 1;
    public const TYPE_COLLECT = 2;

    public const DAY_UNAVAILABLE = 0;
    public const DAY_CARPOOLED = 1;
    public const DAY_NOT_CARPOOLED = 2;
    public const DAY_UNPAID = 3;

    /**
     * @var int The id of this payment item.
     * @Groups({"readPayment"})
     *
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var int The id of the ask associated to this payment item.
     * @Groups({"readPayment"})
     *
     */
    private $askId;

    /**
     * @var int The frequency (1 = punctual; 2 = regular).
     * @Groups({"readPayment"})
     */
    private $frequency;

    /**
     * @var int The payment type (different that type PAY and COLLECT used only in request params)
     * 1 : one way trip
     * 2 : outward of a round trip
     * 3 : return of a round trip).
     * @Groups({"readPayment"})
     */
    private $type;

    /**
     * @var string|null The avatar of the user
     * @Groups({"readPayment"})
     */
    private $avatar;

    /**
     * @var string|null The first name of the user.
     * @Groups({"readPayment"})
     */
    private $givenName;

    /**
     * @var string|null The shorten family name of the user.
     * @Groups({"readPayment"})
     */
    private $shortFamilyName;

    /**
     * @var Address The origin.
     * @Groups({"readPayment"})
     */
    private $origin;

    /**
     * @var Address The destination.
     * @Groups({"readPayment"})
     */
    private $destination;

    /**
     * @var string|null The amount, if punctual.
     * @Groups({"readPayment"})
     */
    private $amount;

    /**
     * @var string|null The amount for the outward, if regular.
     * @Groups({"readPayment"})
     */
    private $outwardAmount;

    /**
     * @var string|null The amount for the return, if regular.
     * @Groups({"readPayment"})
     */
    private $returnAmount;

    /**
     * @var \DateTimeInterface|null The date of the item, if punctual.
     * @Groups({"readPayment"})
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context"={"type"="string", "format"="date"}
     *     }
     * )
     */
    private $date;

    /**
     * @var \DateTimeInterface|null The start date of the item, if regular.
     * @Groups({"readPayment"})
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context"={"type"="string", "format"="date"}
     *     }
     * )
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface|null The end date of the item, if regular.
     * @Groups({"readPayment"})
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context"={"type"="string", "format"="date"}
     *     }
     * )
     */
    private $toDate;

    /**
     * @var array|null The days concerned by the outward trip.
     * Each item of the array contains the id of the CarpoolItem and the status for the day :
     * 0 : unavailable
     * 1 : carpooled
     * 2 : not carpooled
     * 3 : unpaid
     * The array is indexed by the numeric representation of the week day, from 0 (sunday) to 6 (saturday).
     * outwardDays => [
     *  ["id"=>5, "status=>1],
     *  ["id"=>null, "status=>0],
     *  ...
     * ]
     * @Groups({"readPayment"})
     */
    private $outwardDays;

    /**
     * @var array|null The days concerned by the return trip.
     * Each item of the array contains the id of the CarpoolItem and the status for the day :
     * 0 : unavailable
     * 1 : carpooled
     * 2 : not carpooled
     * 3 : unpaid
     * The array is indexed by the numeric representation of the week day, from 0 (sunday) to 6 (saturday).
     * returnDays => [
     *  ["id"=>5, "status=>1],
     *  ["id"=>null, "status=>0],
     *  ...
     * ]
     * @Groups({"readPayment"})
     */
    private $returnDays;

    /**
     * @var bool If the current payment profile is linked to one or several bank accounts
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context"={"type"="boolean"}
     *     }
     * )

     * @Groups({"readPayment"})
     */
    private $electronicallyPayable;

    /**
     * @var bool If the current User can pay electronically this item (i.e. has a complete address for subscription or an already registered bank account)
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context"={"type"="boolean"}
     *     }
     * )

     * @Groups({"readPayment"})
     */
    private $canPayElectronically;

    /**
     * @var \DateTimeInterface|null The unpaid date for this Item
     * @Groups({"readPayment"})
     *
     * @ApiProperty(
     *     attributes={
     *         "swagger_context"={"type"="string", "format"="date"}
     *     }
     * )
     */
    private $unpaidDate;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getAskId(): int
    {
        return $this->askId;
    }

    public function setAskId(int $askId): self
    {
        $this->askId = $askId;

        return $this;
    }

    public function getFrequency(): ?int
    {
        return $this->frequency;
    }

    public function setFrequency(int $frequency): self
    {
        $this->frequency = $frequency;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function setGivenName(?string $givenName): self
    {
        $this->givenName = $givenName;

        return $this;
    }

    public function getShortFamilyName(): ?string
    {
        return $this->shortFamilyName;
    }

    public function setShortFamilyName(?string $shortFamilyName): self
    {
        $this->shortFamilyName = $shortFamilyName;

        return $this;
    }

    public function getOrigin(): ?Address
    {
        return $this->origin;
    }

    public function setOrigin(?Address $origin): self
    {
        $this->origin = $origin;

        return $this;
    }

    public function getDestination(): ?Address
    {
        return $this->destination;
    }

    public function setDestination(?Address $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount)
    {
        $this->amount = $amount;
    }

    public function getOutwardAmount(): ?string
    {
        return $this->outwardAmount;
    }

    public function setOutwardAmount(?string $outwardAmount)
    {
        $this->outwardAmount = $outwardAmount;
    }

    public function getReturnAmount(): ?string
    {
        return $this->returnAmount;
    }

    public function setReturnAmount(?string $returnAmount)
    {
        $this->returnAmount = $returnAmount;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
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

    public function setToDate(?\DateTimeInterface $toDate): self
    {
        $this->toDate = $toDate;

        return $this;
    }

    public function getOutwardDays(): ?array
    {
        return $this->outwardDays;
    }

    public function setOutwardDays(array $outwardDays): self
    {
        $this->outwardDays = $outwardDays;

        return $this;
    }

    public function getReturnDays(): ?array
    {
        return $this->returnDays;
    }

    public function setReturnDays(array $returnDays): self
    {
        $this->returnDays = $returnDays;

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

    public function getCanPayElectronically(): ?bool
    {
        return $this->canPayElectronically;
    }

    public function setCanPayElectronically(bool $canPayElectronically): self
    {
        $this->canPayElectronically = $canPayElectronically;

        return $this;
    }

    public function getUnpaidDate(): ?\DateTimeInterface
    {
        return $this->unpaidDate;
    }

    public function setUnpaidDate(?\DateTimeInterface $unpaidDate): self
    {
        $this->unpaidDate = $unpaidDate;

        return $this;
    }
}
