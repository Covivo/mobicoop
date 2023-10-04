<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

namespace App\Stats\Admin\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ApiResource(
 *      attributes={
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"aRead"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={
 *          "ADMIN_get"={
 *              "method"="GET",
 *              "security"="is_granted('analytic_list',object)",
 *              "path"="/admin/analytics",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "ADMIN_get"={
 *              "method"="GET",
 *              "security"="is_granted('analytic_read',object)",
 *              "path"="/admin/analytics/{id}",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          }
 *      }
 * )
 */
class Analytic
{
    public const AUTHORIZED_TYPES = [
        'summary',
        'saved_co2',
        'published_ads',
        'users',
        'solidary_users',
        'communities',
        'summary_community',
    ];

    public const AUTHORIZED_PERIODICITY = [
        'monthly',
        'daily',
        'yearly',
    ];

    public const DEFAULT_ID = 999999999999;

    /**
     * @var int The id of this Analytic
     *
     * @Groups({"aRead"})
     */
    private $id;

    /**
     * @var string The type of this Analytic
     *
     * @ApiProperty(identifier=true)
     *
     * @Groups({"aRead"})
     */
    private $type;

    /**
     * @var string The periodicity of this Analytic
     *
     * @Groups({"aRead"})
     */
    private $periodicity;

    /**
     * @var string analytic url
     *
     * @Groups({"aRead"})
     */
    private $url;

    /**
     * @var int default Community id
     *
     * @Groups({"aRead"})
     */
    private $communityId;

    /**
     * @var int default Territory id
     *
     * @Groups({"aRead"})
     */
    private $territoryId;

    /**
     * @var bool Force to get the default value for communityId
     *
     * @Groups({"aRead"})
     */
    private $forceDefaultCommunityId;

    /**
     * @var bool Force to get the default value for terrotoryId
     *
     * @Groups({"aRead"})
     */
    private $forceDefaultTerritoryId;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPeriodicity(): ?string
    {
        return $this->periodicity;
    }

    public function setPeriodicity(?string $periodicity): self
    {
        $this->periodicity = $periodicity;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;

        return $this;
    }

    public function getCommunityId(): ?int
    {
        return $this->communityId;
    }

    public function setCommunityId(?int $communityId): self
    {
        $this->communityId = $communityId;

        return $this;
    }

    public function getTerritoryId(): ?int
    {
        return $this->territoryId;
    }

    public function setTerritoryId(?int $territoryId): self
    {
        $this->territoryId = $territoryId;

        return $this;
    }

    public function hasForceDefaultCommunityId(): ?bool
    {
        return $this->forceDefaultCommunityId;
    }

    public function setForceDefaultCommunityId(?bool $forceDefaultCommunityId): self
    {
        $this->forceDefaultCommunityId = $forceDefaultCommunityId;

        return $this;
    }

    public function hasforceDefaultTerritoryId(): ?bool
    {
        return $this->forceDefaultTerritoryId;
    }

    public function setForceDefaultTerritoryId(?bool $forceDefaultTerritoryId): self
    {
        $this->forceDefaultTerritoryId = $forceDefaultTerritoryId;

        return $this;
    }
}
