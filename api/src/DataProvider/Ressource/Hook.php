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

namespace App\DataProvider\Ressource;

use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A payment hook.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Hook
{
    public const DEFAULT_ID = '999999999999';

    public const STATUS_FAILED = 0;
    public const STATUS_SUCCESS = 1;
    public const STATUS_DELAYED = 2;
    public const STATUS_REFUSED = 3;
    public const STATUS_OUTDATED_RESSOURCE = 4;

    /**
     * @var int The id of this pay in
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readPayment"})
     */
    private $id;

    /**
     * @var string The event type pay in
     *
     * @Groups({"readPayment"})
     */
    private $eventType;

    /**
     * @var int The ressource id (from the payment provider) of this pay in
     *
     * @Groups({"readPayment"})
     */
    private $ressourceId;

    /**
     * @var int The date (timestamp) of this pay in
     *
     * @Groups({"readPayment"})
     */
    private $date;

    /**
     * @var string The security token of the pay in
     *
     * @Groups({"readPayment"})
     */
    private $securityToken;

    /**
     * @var int The status of the hook's transaction
     *
     * @Groups({"readPayment"})
     */
    private $status;

    public function __construct()
    {
        $this->id = self::DEFAULT_ID;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getEventType(): ?string
    {
        return $this->eventType;
    }

    public function setEventType(string $eventType): self
    {
        $this->eventType = $eventType;

        return $this;
    }

    public function getRessourceId(): ?int
    {
        return $this->ressourceId;
    }

    public function setRessourceId(int $ressourceId): self
    {
        $this->ressourceId = $ressourceId;

        return $this;
    }

    public function getDate(): ?int
    {
        return $this->date;
    }

    public function setDate(int $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getSecurityToken(): ?string
    {
        return $this->securityToken;
    }

    public function setSecurityToken(string $securityToken): self
    {
        $this->securityToken = $securityToken;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }
}
