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

namespace App\ExternalJourney\Ressource;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A driver or a passenger involved in an external connection
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ExternalConnectionActor
{

    /**
     * @var String  Uuid of this actor
     * @Groups({"readExternalConnection","writeExternalConnection"})
     */
    private $uuid;

    /**
     * @var String  Alias of this actor
     * @Groups({"readExternalConnection","writeExternalConnection"})
     */
    private $alias;

    /**
     * @var String  State of this actor (sender or recipient)
     * @Groups({"readExternalConnection","writeExternalConnection"})
     */
    private $state;

    public function getUuid(): String
    {
        return $this->uuid;
    }

    public function setUuid(String $uuid): self
    {
        $this->uuid = $uuid;
        
        return $this;
    }

    public function getAlias(): String
    {
        return $this->alias;
    }

    public function setAlias(String $alias): self
    {
        $this->alias = $alias;
        
        return $this;
    }

    public function getState(): String
    {
        return $this->state;
    }

    public function setState(String $state): self
    {
        $this->state = $state;
        
        return $this;
    }
}
