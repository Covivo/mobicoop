<?php

/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace App\ExternalService\Interfaces\Builder;

use App\ExternalService\Core\Domain\Entity\CarpoolProof\CarpoolProofEntity;
use App\ExternalService\Interfaces\DTO\CarpoolProof\CarpoolProofDto;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CarpoolProofEntityBuilder
{
    private $_waypointEntityBuilder;
    private $_actorEntityBuilder;

    public function __construct(WaypointEntityBuilder $waypointEntityBuilder, ActorEntityBuilder $actorEntityBuilder)
    {
        $this->_waypointEntityBuilder = $waypointEntityBuilder;
        $this->_actorEntityBuilder = $actorEntityBuilder;
    }

    public function build(CarpoolProofDto $carpoolProofDto): CarpoolProofEntity
    {
        $carpooProofEntity = new CarpoolProofEntity();

        $carpooProofEntity = Tools::cloneSimpleObjectDtoToEntity($carpoolProofDto, $carpoolProofDto, $carpooProofEntity);
        $carpooProofEntity->setPickUpDriver($this->_waypointEntityBuilder->build($carpoolProofDto->getPickUpDriver()));
        $carpooProofEntity->setPickUpPassenger($this->_waypointEntityBuilder->build($carpoolProofDto->getPickUpPassenger()));
        $carpooProofEntity->setDropOffDriver($this->_waypointEntityBuilder->build($carpoolProofDto->getDropOffDriver()));
        $carpooProofEntity->setDropOffPassenger($this->_waypointEntityBuilder->build($carpoolProofDto->getDropOffPassenger()));
        $carpooProofEntity->setDriver($this->_actorEntityBuilder->buildDriver($carpoolProofDto->getDriver()));
        $carpooProofEntity->setPassenger($this->_actorEntityBuilder->buildPassenger($carpoolProofDto->getPassenger()));

        return $carpooProofEntity;
    }
}
