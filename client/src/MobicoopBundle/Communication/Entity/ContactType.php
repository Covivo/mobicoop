<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\Communication\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A contact message.
 */
class ContactType implements ResourceInterface, \JsonSerializable
{
    /**
     * @var int The id of the contact type
     */
    private $id;

    /**
     * @var string|null Demand for this contact type
     */
    private $demand;

    /**
     * @var array|null Receiving emails for this contact type
     */
    private $to;

    /**
     * @var array|null Receiving emails (carbon copy) for this contact type
     */
    private $cc;

    /**
     * @var array|null Receiving emails (blind carbon copy) for this contact type
     */
    private $bcc;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        
        return $this;
    }

    public function getDemand(): ?string
    {
        return $this->demand;
    }

    public function setDemand(string $demand): self
    {
        $this->demand = $demand;
        
        return $this;
    }

    public function getTo(): ?array
    {
        return $this->to;
    }

    public function setTo(array $to): self
    {
        $this->to = $to;
        
        return $this;
    }

    public function getCc(): ?array
    {
        return $this->cc;
    }

    public function setCc(array $cc): self
    {
        $this->cc = $cc;
        
        return $this;
    }

    public function getBcc(): ?array
    {
        return $this->bcc;
    }

    public function setBcc(array $bcc): self
    {
        $this->bcc = $bcc;
        
        return $this;
    }

    public function jsonSerialize()
    {
        return
            [
                'id'                        => $this->getId(),
                'demand'                    => $this->getDemand()
            ];
    }
}
