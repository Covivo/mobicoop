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

namespace App\Solidary\Entity\SolidaryResult;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\Solidary\Entity\SolidaryMatching;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A solidary Result after a Solidary Search (transport or carpool)
 *
 * ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidarySearch"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidarySearch"}}
 *      },
 *      collectionOperations={
 *          "get"
 *      },
 *      itemOperations={
 *          "get"
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryResult
{
    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this subject.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readSolidarySearch"})
     */
    private $id;

    /**
     * @var SolidaryResultTransport if the SolidaryResult is a SolidaryResultTransport
     *
     * @Groups({"readSolidarySearch"})
     */
    private $solidaryResultTransport;

    /**
     * @var SolidaryResultCarpool if the SolidaryResult is a SolidaryResultCarpool
     *
     * @Groups({"readSolidarySearch"})
     */
    private $solidaryResultCarpool;

    /**
     * @var SolidaryMatching The source SolidaryMatching of this result
     *
     * @Groups({"readSolidarySearch"})
     */
    private $solidaryMatching;

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

    public function getSolidaryResultTransport(): ?SolidaryResultTransport
    {
        return $this->solidaryResultTransport;
    }

    public function setSolidaryResultTransport(SolidaryResultTransport $solidaryResultTransport): self
    {
        $this->solidaryResultTransport = $solidaryResultTransport;

        return $this;
    }

    public function getSolidaryResultCarpool(): ?SolidaryResultCarpool
    {
        return $this->solidaryResultCarpool;
    }

    public function setSolidaryResultCarpool(SolidaryResultCarpool $solidaryResultCarpool): self
    {
        $this->solidaryResultCarpool = $solidaryResultCarpool;

        return $this;
    }

    public function getSolidaryMatching(): ?SolidaryMatching
    {
        return $this->solidaryMatching;
    }

    public function setSolidaryMatching(SolidaryMatching $solidaryMatching): self
    {
        $this->solidaryMatching = $solidaryMatching;

        return $this;
    }
}
