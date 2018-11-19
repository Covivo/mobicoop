<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Geography\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;

use App\Geography\Controller\CompletionController;

/**
 * A completed address from string
 *
 * @ApiResource(
 *      collectionOperations={
 *          "get"={
 *              "path"="/completions",
 *              "controller"=CompletionController::class,
 *              "swagger_context"={
 *                  "parameters"={
 *                     {
 *                         "name" = "input",
 *                         "in" = "query",
 *                         "required" = "true",
 *                         "type" = "string",
 *                         "description" = "user's input"
 *                     }
 *                   }
 *              }
 *          }
 *      },
 *      itemOperations={"get"}
 * )
 */
class Completion
{
    /**
     * @var int The id of this completion.
     *
     * @ApiProperty(identifier=true)
     */
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }
}
