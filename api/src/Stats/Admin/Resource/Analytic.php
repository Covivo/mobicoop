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
 *              "security"="is_granted('access_admin',object)",
 *              "path"="/admin/analytics",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "ADMIN_get"={
 *              "method"="GET",
 *              "security"="is_granted('access_admin',object)",
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
    public const VALUE_TYPE_SCALAR = 'scalar';
    public const VALUE_TYPE_COMPOSITE = 'composite';

    /**
     * @var int The id of this Analytic
     *
     * @ApiProperty(identifier=true)
     * @Groups({"aRead"})
     */
    private $id;

    /**
     * @var string Analytic domain
     *
     * @Groups({"aRead"})
     */
    private $domain;

    /**
     * @var string Analytic value type
     *
     * @Groups({"aRead"})
     */
    private $valueType;

    /**
     * Analytic value.
     *
     * @Groups({"aRead"})
     */
    private $value;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getValueType(): ?string
    {
        return $this->valueType;
    }

    public function setValueType(string $valueType): self
    {
        $this->valueType = $valueType;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value): self
    {
        $this->value = $value;

        return $this;
    }
}
