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

namespace App\Solidary\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A solidary search (transport or carpool)
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidary","readSolidarySearch"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidary"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "normalization_context"={"groups"={"readSolidarySearch"}},
 *             "security"="is_granted('solidary_read',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidarySearch
{
    const DEFAULT_ID = 999999999999;
    
    /**
     * @var int The id of this subject.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $id;

    /**
    * @var Solidary The solidary this search is for.
    * @Assert\NotBlank
    * @Groups({"readSolidary","writeSolidary","readSolidarySearch"})
    * @MaxDepth(1)
    */
    private $solidary;

    /**
    * @var string If it's a search on outward or return
    * @Assert\NotBlank
    * @Assert\Choice({"outward", "return"})
    * @Groups({"readSolidary","writeSolidary","readSolidarySearch"})
    * @MaxDepth(1)
    */
    private $way;

    /**
    * @var array The results for this search (array of SolidaryUser)
    * Array of SolidaryResult
    * @Groups({"readSolidary","readSolidarySearch"})
    */
    private $results;


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

    public function getSolidary(): ?Solidary
    {
        return $this->solidary;
    }
    
    public function setSolidary(Solidary $solidary): self
    {
        $this->solidary = $solidary;
        
        return $this;
    }

    public function getWay(): ?string
    {
        return $this->way;
    }
    
    public function setWay(string $way): self
    {
        $this->way = $way;
        
        return $this;
    }

    public function getResults(): ?array
    {
        return $this->results;
    }
    
    public function setResults(array $results): self
    {
        $this->results = $results;
        
        return $this;
    }
}
