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


namespace Mobicoop\Bundle\MobicoopBundle\Community\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;

/**
 *  A community.
 */
class MCommunity implements ResourceInterface, \JsonSerializable
{
    /**
     * @var int The id of this community.
     */
    private $id;

    /**
     * @var string The name of the community.
     */
    private $name;

    /**
     * @var int The type of validation (automatic/manual/domain).
     */
    private $validationType;

    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name)
    {
        $this->name = $name;
    }

    public function getValidationType()
    {
        return $this->validationType;
    }

    public function setValidationType(?int $validationType)
    {
        $this->validationType = $validationType;
    }

    public function jsonSerialize()
    {
        return
        [
            'id'                => $this->getId(),
            'name'              => $this->getName(),
            'validationType'    => $this->getValidationType()
        ];
    }
}
