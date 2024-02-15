<?php

/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace App\ExternalService\Interfaces\DTO;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class IdentityDto
{
    /**
     * @var string
     */
    private $_identityKey;

    /**
     * @var string
     */
    private $_phoneTrunc;

    /**
     * @var string
     */
    private $_phone;

    /**
     * @var string
     */
    private $_operatorUserId;

    /**
     * @var ?bool : can be null for a driver
     */
    private $_over18;

    public function getIdentityKey(): ?string
    {
        return $this->_identityKey;
    }

    public function setIdentityKey(?string $identityKey): self
    {
        $this->_identityKey = $identityKey;

        return $this;
    }

    public function getPhoneTrunc(): ?string
    {
        return $this->_phoneTrunc;
    }

    public function setPhoneTrunc(?string $phoneTrunc): self
    {
        $this->_phoneTrunc = $phoneTrunc;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->_phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->_phone = $phone;

        return $this;
    }

    public function getOperatorUserId(): ?string
    {
        return $this->_operatorUserId;
    }

    public function setOperatorUserId(?string $operatorUserId): self
    {
        $this->_operatorUserId = $operatorUserId;

        return $this;
    }

    public function isOver18(): ?bool
    {
        return $this->_over18;
    }

    public function setOver18(?bool $over18): self
    {
        $this->_over18 = $over18;

        return $this;
    }
}
