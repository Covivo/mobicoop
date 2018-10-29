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

/**
 * A public transport mode.
 *
 * @ApiResource(
 *      attributes={
 *          "normalization_context"={"groups"={"pt"}, "enable_max_depth"="true"},
 *      },
 *      collectionOperations={},
 *      itemOperations={"get"}
 * )
 */
class PTMode
{
    const PT_MODE_BUS = "BUS";
    const PT_MODE_TRAIN = "TRAIN";
    const PT_MODE_BIKE = "BIKE";
    const PT_MODE_WALK = "WALK";

    private const PT_MODES = [
            self::PT_MODE_BUS => 1,
            self::PT_MODE_TRAIN => 2,
            self::PT_MODE_BIKE => 3,
            self::PT_MODE_WALK => 4
    ];
        
    /**
     * @ApiProperty(identifier=true)
     */
    private $id;
    
    /**
     * @var string The name of this mode.
     *
     * @Groups("pt")
     */
    private $name;
    
    public function __construct($mode)
    {
        $this->setId(self::PT_MODES[$mode]);
        $this->setName($mode);
    }
    
    public function getId()
    {
        return $this->id;
    }
    
    public function setId($id)
    {
        $this->id = $id;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function setName($name)
    {
        $this->name = $name;
    }
}
