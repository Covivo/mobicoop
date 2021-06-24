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

namespace App\Auth\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use App\Auth\Controller\Permissions;
use App\Auth\Controller\PermissionCheck;

/**
 * A permission to execute an action.
 *
 * @ApiResource(
 *      collectionOperations={
 *          "permissions"={
 *              "method"="GET",
 *              "controller"=Permissions::class,
 *              "path"="/permissions",
 *              "security"="is_granted('permission',object)",
 *              "swagger_context" = {
 *                  "tags"={"Authentification"}
 *              }
 *          },
 *          "roles_granted_for_creation"={
 *              "method"="GET",
 *              "path"="/permissions/roles-granted-for-creation",
 *              "security"="is_granted('permission',object)",
 *              "swagger_context" = {
 *                  "tags"={"Authentification"}
 *              }
 *          },
 *          "granted"={
 *              "method"="GET",
 *              "controller"=PermissionCheck::class,
 *              "path"="/permissions/check",
 *              "security"="is_granted('permission',object)",
 *              "swagger_context"={
 *                  "tags"={"Authentification"},
 *                  "parameters"={
 *                      {
 *                          "name" = "item",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "The name of the auth item to check"
 *                      },
 *                      {
 *                          "name" = "id",
 *                          "in" = "query",
 *                          "type" = "number",
 *                          "format" = "integer",
 *                          "description" = "The related object id"
 *                      },
 *                   }
 *              }
 *          },
 *          "ADMIN_grantable"={
 *              "method"="GET",
 *              "path"="/permissions/grantable",
 *              "security"="is_granted('permission',object)",
 *              "swagger_context" = {
 *                  "tags"={"Administration"}
 *              }
 *          },
 *      },
 *      itemOperations={
 *          "get"={
 *             "security"="is_granted('reject',object)",
 *              "swagger_context" = {
 *                  "tags"={"Authentification"}
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
