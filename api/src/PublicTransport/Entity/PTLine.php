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
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A public transport line.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"pt"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"}
 * )
 */
class PTLine
{
    /**
     * @var string The name of this line.
     *
     * @Groups("pt")
     */
    private $name;
    
    /**
     * @var string The origin of this line.
     *
     * @Groups("pt")
     */
    private $origin;
    
    /**
     * @var string The destination of this line.
     *
     * @Groups("pt")
     */
    private $destination;
    
    /**
     * @var PTCompany The company that manage this line.
     *
     * @Groups("pt")
     */
    private $ptcompany;
    
    public function getName()
    {
        return $this->name;
    }

    public function getOrigin()
    {
        return $this->origin;
    }

    public function getDestination()
    {
        return $this->destination;
    }

    public function getPtcompany()
    {
        return $this->ptcompany;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setOrigin($origin)
    {
        $this->origin = $origin;
    }

    public function setDestination($destination)
    {
        $this->destination = $destination;
    }

    public function setPtcompany($ptcompany)
    {
        $this->ptcompany = $ptcompany;
    }
}
