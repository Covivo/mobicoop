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
    /**
     * @var int The id of this Analytic
     *
     * @ApiProperty(identifier=true)
     * @Groups({"aRead"})
     */
    private $id;

    /**
     * Analytic url.
     *
     * @Groups({"aRead"})
     */
    private $url;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url): self
    {
        $this->url = $url;

        return $this;
    }
}
