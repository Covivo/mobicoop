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

namespace App\Communication\Ressource;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\Event\Entity\Event;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\User\Entity\User;

/**
 * A Report
 *
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readReport"}, "enable_max_depth"="true"},
 *          "denormalization_context"={"groups"={"writeReport"}}
 *      },
 *      collectionOperations={
 *          "get"={
 *              "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Report"}
 *              }
 *          },
 *          "post"={
 *              "security_post_denormalize"="is_granted('report_create',object)",
 *              "swagger_context" = {
 *                  "tags"={"Report"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *              "is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Report"}
 *              }
 *          }
 *      }
 * )
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class Report
{
    const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of the Report
     *
     * @ApiProperty(identifier=true)
     * @Groups({"readReport","writeReport"})
     */
    private $id;

    /**
     * @var string|null Email of the reporter
     * @Assert\NotBlank
     * @Assert\Email()
     * @Groups({"readReport","writeReport"})
     */
    private $reporterEmail;

    /**
     * @var string|null Text of the Report
     * @Assert\NotBlank
     * @Groups({"readReport","writeReport"})
     */
    private $text;

    /**
     * @var int|null If the report is about a User
     * @Groups({"readReport","writeReport"})
     */
    private $userId;

    /**
     * @var int|null If the report is about an Event
     * @Groups({"readReport","writeReport"})
     */
    private $eventId;

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

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        
        return $this;
    }

    public function getEventId(): ?int
    {
        return $this->eventId;
    }

    public function setEventId(int $eventId): self
    {
        $this->eventId = $eventId;
        
        return $this;
    }

    public function getReporterEmail(): ?string
    {
        return $this->reporterEmail;
    }

    public function setReporterEmail(string $reporterEmail): self
    {
        $this->reporterEmail = $reporterEmail;
        
        return $this;
    }
    
    public function getText(): ?string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;
        
        return $this;
    }
}
