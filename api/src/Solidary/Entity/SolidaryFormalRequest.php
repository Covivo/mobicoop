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
use App\Communication\Entity\Medium;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A solidary formal request about a SolidarySolution
 *
 * Exemple of formal request data :
 *
 * {
 *   "outwardDate": "2020-04-28T00:00:00+00:00",
 *   "outwardLimitDate": "2020-05-05T00:00:00+00:00",
 *   "outwardSchedule": [
 *    {
 *      "outwardTime": "08:30",
 *      "mon": 1,
 *      "tue": 1,
 *      "wed": 0,
 *      "thu": 0,
 *      "fri": 0,
 *      "sat": 0,
 *      "sun": 0
 *    }
 *  ]
 * }
 *
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readSolidaryFormalRequest"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeSolidaryFormalRequest"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *             "security"="is_granted('solidary_contact',object)",
 *              "swagger_context" = {
 *                  "tags"={"Solidary"}
 *              }
 *          },
 *          "post"={
 *             "security_post_denormalize"="is_granted('solidary_contact',object)",
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
class SolidaryFormalRequest
{
    const DEFAULT_ID = 999999999999;
    
    /**
     * @var int The id of this subject.
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readSolidaryFormalRequest","writeSolidaryFormalRequest"})
     */
    private $id;

    /**
    * @var SolidarySolution The solidary solution this contact is for
    * @Assert\NotBlank
    * @Groups({"readSolidaryFormalRequest","writeSolidaryFormalRequest"})
    * @MaxDepth(1)
    */
    private $solidarySolution;

    /**
     * @var \DateTimeInterface|null The date for the outward if the frequency is punctual, the start date of the outward if the frequency is regular.
     * @Groups({"readSolidaryFormalRequest","writeSolidaryFormalRequest"})
     */
    private $outwardDate;

    /**
     * @var \DateTimeInterface|null The limit date for the outward if the frequency is regular.
     * @Groups({"readSolidaryFormalRequest","writeSolidaryFormalRequest"})
     */
    private $outwardLimitDate;

    /**
     * @var \DateTimeInterface|null The date for the return if the frequency is punctual, the start date of the return if the frequency is regular.
     * @Groups({"readSolidaryFormalRequest","writeSolidaryFormalRequest"})
     */
    private $returnDate;

    /**
     * @var \DateTimeInterface|null The limit date for the return if the frequency is regular.
     * @Groups({"readSolidaryFormalRequest","writeSolidaryFormalRequest"})
     */
    private $returnLimitDate;

    /**
     * @var array|null The outward schedule
     *
     * @Groups({"readSolidaryFormalRequest","writeSolidaryFormalRequest"})
     */
    private $outwardSchedule;

    /**
     * @var array|null The return schedule
     *
     * @Groups({"readSolidaryFormalRequest","writeSolidaryFormalRequest"})
     */
    private $returnSchedule;


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

    public function getSolidarySolution(): ?SolidarySolution
    {
        return $this->solidarySolution;
    }
    
    public function setSolidarySolution(SolidarySolution $solidarySolution): self
    {
        $this->solidarySolution = $solidarySolution;
        
        return $this;
    }

    public function getOutwardDate(): ?\DateTimeInterface
    {
        return $this->outwardDate;
    }

    public function setOutwardDate(?\DateTimeInterface $outwardDate): self
    {
        $this->outwardDate = $outwardDate;

        return $this;
    }

    public function getOutwardLimitDate(): ?\DateTimeInterface
    {
        return $this->outwardLimitDate;
    }

    public function setOutwardLimitDate(?\DateTimeInterface $outwardLimitDate): self
    {
        $this->outwardLimitDate = $outwardLimitDate;

        return $this;
    }

    public function getReturnDate(): ?\DateTimeInterface
    {
        return $this->returnDate;
    }

    public function setReturnDate(?\DateTimeInterface $returnDate): self
    {
        $this->returnDate = $returnDate;

        return $this;
    }

    public function getReturnLimitDate(): ?\DateTimeInterface
    {
        return $this->returnLimitDate;
    }

    public function setReturnLimitDate(?\DateTimeInterface $returnLimitDate): self
    {
        $this->returnLimitDate = $returnLimitDate;

        return $this;
    }

    public function getOutwardSchedule(): ?array
    {
        return $this->outwardSchedule;
    }
    
    public function setOutwardSchedule(?array $outwardSchedule): self
    {
        $this->outwardSchedule = $outwardSchedule;
        
        return $this;
    }

    public function getReturnSchedule(): ?array
    {
        return $this->returnSchedule;
    }
    
    public function setReturnSchedule(?array $returnSchedule): self
    {
        $this->returnSchedule = $returnSchedule;
        
        return $this;
    }
}
