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

namespace Mobicoop\Bundle\MobicoopBundle\Payment\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;

/**
 * A PaymentItem
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class PaymentItem implements ResourceInterface, \JsonSerializable
{
    const FREQUENCY_PUNCTUAL = 1;
    const FREQUENCY_REGULAR = 2;

    const TYPE_PAY = 1;
    const TYPE_COLLECT = 2;

    const DAY_UNAVAILABLE = 0;
    const DAY_CARPOOLED = 1;
    const DAY_NOT_CARPOOLED = 2;

    /**
     * @var int The id of this payment item.
     */
    private $id;

    /**
     * @var int The id of the ask associated to this payment item.
     *
     */
    private $askId;

    /**
     * @var int The frequency (1 = punctual; 2 = regular).
     */
    private $frequency;

    /**
     * @var int The payment type (1 = a payment to be made, 2 = a payment to collect).
     */
    private $type;

    /**
     * @var string|null The avatar of the user
     */
    private $avatar;

    /**
     * @var string|null The first name of the user.
     */
    private $givenName;

    /**
     * @var string|null The shorten family name of the user.
    */
    private $shortFamilyName;

    /**
     * @var Address The origin.
     */
    private $origin;

    /**
     * @var Address The destination.
     */
    private $destination;

    /**
     * @var string|null The amount, if punctual.
     */
    private $amount;

    /**
     * @var string|null The amount for the outward, if regular.
     */
    private $outwardAmount;

    /**
     * @var string|null The amount for the return, if regular.
     */
    private $returnAmount;

    /**
     * @var \DateTimeInterface|null The date of the item, if punctual.
     */
    private $date;

    /**
     * @var \DateTimeInterface|null The start date of the item, if regular.
     */
    private $fromDate;

    /**
     * @var \DateTimeInterface|null The end date of the item, if regular.
     */
    private $toDate;

    /**
     * @var array|null The days concerned by the outward trip.
     * Each item of the array contains the status for the day :
     * 0 : unavailable
     * 1 : carpooled
     * 2 : not carpooled
     * The array is indexed by the numeric representation of the week day, from 0 (sunday) to 6 (saturday).
     */
    private $outwardDays;

    /**
     * @var array|null The days concerned by the return trip.
     * Each item of the array contains the status for the day :
     * 0 : unavailable
     * 1 : carpooled
     * 2 : not carpooled
     * The array is indexed by the numeric representation of the week day, from 0 (sunday) to 6 (saturday).
     */
    private $returnDays;

    /**
     * @var bool If the current payment profile is linked to one or several bank accounts
     */

    private $electronicallyPayable;

    /**
     * @var \DateTimeInterface|null The unpaid date for this Item
     */
    private $unpaidDate;


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

    public function getUnpaidDate(): ?\DateTimeInterface
    {
        return $this->unpaidDate;
    }

    public function setUnpaidDate(\DateTimeInterface $unpaidDate): self
    {
        $this->unpaidDate = $unpaidDate;

        return $this;
    }

    public function jsonSerialize()
    {
        return
            [
                'id'                        => $this->getId(),
                'askId'                     => $this->getAskId(),
                'frequency'                 => $this->getFrequency(),
                'type'                      => $this->getType(),
                'avatar'                    => $this->getAvatar(),
                'givenName'                 => $this->getGivenName(),
                'shortFamilyName'           => $this->getShortFamilyName(),
                'origin'                    => $this->getOrigin(),
                'destination'               => $this->getDestination(),
                'amount'                    => $this->getAmount(),
                'outwardAmount'             => $this->getOutwardAmount(),
                'returnAmount'              => $this->getReturnAmount(),
                'date'                      => $this->getDate(),
                'toDate'                    => $this->getToDate(),
                'outwardDays'               => $this->getOutwardDays(),
                'returnDays'                => $this->getReturnDays(),
                'electronicallyPayable'     => $this->isElectronicallyPayable(),
                'unpaidDate'                => $this->getUnpaidDate()
            ];
    }
}
