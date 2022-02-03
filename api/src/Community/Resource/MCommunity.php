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

namespace App\Community\Resource;

use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A community resource.
 * Prefixed by 'M' as long as the Community Entity is also a resource !
 *
 * @ApiResource(
 *     attributes={
 *          "order"={"name": "ASC"},
 *          "force_eager"=false,
 *          "normalization_context"={"groups"={"readCommunity"}, "enable_max_depth"="true"}
 *     },
 *     collectionOperations={
 *          "get"={
 *             "security"="is_granted('community_list',object)",
 *              "swagger_context" = {
 *                  "tags"={"Communities"}
 *              }
 *          }
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Communities"}
 *              }
 *          }
 *      }
 * )
 *
 *  @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class MCommunity
{
    public const DEFAULT_ID = 999999999999;
    public const ENTITY_RELATED = 'App\\Community\\Entity\\Community';

    /**
     * @var int the id of this community
     * @Groups({"readCommunity"})
     *
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var null|string the name of the community
     * @Groups({"readCommunity"})
     */
    private $name;

    /**
     * @var null|string the urlKey of the community
     * @Groups({"readCommunity"})
     */
    private $urlKey;

    /**
     * @var null|int the type of validation (automatic/manual/domain)
     * @Groups({"readCommunity"})
     */
    private $validationType;

    public function __construct($id = null)
    {
        $this->id = self::DEFAULT_ID;
        if ($id) {
            $this->id = $id;
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getUrlKey(): ?string
    {
        return $this->urlKey;
    }

    public function setUrlKey(?string $urlKey): self
    {
        $this->urlKey = $urlKey;

        return $this;
    }

    public function getValidationType(): ?int
    {
        return $this->validationType;
    }

    public function setValidationType(?int $validationType)
    {
        $this->validationType = $validationType;
    }
}
