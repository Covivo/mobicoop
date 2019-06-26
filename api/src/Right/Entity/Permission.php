<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Right\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\Right\Controller\PermissionCheck;

/**
 * A permission to execute an action.
 *
 * @ApiResource(
 *      collectionOperations={
 *          "granted"={
 *              "method"="GET",
 *              "controller"=PermissionCheck::class,
 *              "path"="/permissions",
 *              "swagger_context"={
 *                  "parameters"={
 *                      {
 *                          "name" = "action",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "The name of the action to check"
 *                      },
 *                      {
 *                          "name" = "user",
 *                          "in" = "query",
 *                          "type" = "number",
 *                          "format" = "integer",
 *                          "description" = "The user id"
 *                      },
 *                      {
 *                          "name" = "territory",
 *                          "in" = "query",
 *                          "type" = "number",
 *                          "format" = "integer",
 *                          "description" = "The territory id"
 *                      },
 *                   }
 *              }
 *          }
 *      }
 * )
 */
class Permission
{
    /**
     * @var int The id of this permission.
     *
     * @ApiProperty(identifier=true)
     */
    private $id;

    /**
     * @var boolean The action is granted
     */
    private $granted;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function isGranted(): ?bool
    {
        return $this->granted;
    }

    public function setGranted(bool $granted): self
    {
        $this->granted = $granted;

        return $this;
    }
}
