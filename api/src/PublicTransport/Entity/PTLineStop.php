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

namespace App\PublicTransport\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Annotation\ApiProperty;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use App\Geography\Entity\Address;
use Doctrine\ORM\Mapping as ORM;

/**
 * A public transport Line Stops list.
 *
 * @ApiResource(
 *      routePrefix="/public_transport",
 *      attributes={
 *          "normalization_context"={"groups"={"pt"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={
 *          "get"={
 *              "path"="/linestops",
 *              "swagger_context" = {
 *                  "parameters" = {
 *                      {
 *                          "name" = "provider",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "string",
 *                          "description" = "The public transport data provider"
 *                      },
 *                      {
 *                          "name" = "logicalId",
 *                          "in" = "query",
 *                          "required" = "true",
 *                          "type" = "number",
 *                          "format" = "int",
 *                          "description" = "The id of the line stop"
 *                      },
 *                  },
 *              }
 *          }
 *     },
 *      itemOperations={"get"={"path"="/linestop/{id}"}}
 * )
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PTLineStop
{
    /**
     * @var int a direction of this Line Stop.
     * @Groups("pt")
     */
    private $direction;

    /**
     * @var PTLine the Line of this Line Stop
     * @Groups("pt")
     */
    private $line;


    /**
     * @var int id of the line of this line stop
     * @Groups("pt")
     */
    private $lineid;


    public function getDirection(): int
    {
        return $this->direction;
    }

    public function setDirection(int $direction): self
    {
        $this->direction = $direction;

        return $this;
    }

    public function getLine(): PTLine
    {
        return $this->line;
    }

    public function setLine(PTLine $line): self
    {
        $this->line = $line;

        return $this;
    }

    public function getLineid(): int
    {
        return $this->lineid;
    }

    public function setLineid(int $lineid): self
    {
        $this->lineid = $lineid;

        return $this;
    }


}
