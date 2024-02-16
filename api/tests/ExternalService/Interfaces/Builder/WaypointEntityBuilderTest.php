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

use App\ExternalService\Core\Domain\Entity\CarpoolProof\WaypointEntity;
use App\Tests\ExternalService\Mock\Waypoint;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class WaypointEntityBuilderTest extends TestCase
{
    private $_waypointEntityBuilder;

    public function setUp(): void
    {
        $this->_waypointEntityBuilder = new WaypointEntityBuilder();
    }

    /**
     * @test
     */
    public function testBuildReturnsAWaypointEntity()
    {
        $this->assertInstanceOf(WaypointEntity::class, $this->_waypointEntityBuilder->build(Waypoint::getWaypointDto()));
    }

    /**
     * @test
     *
     * @dataProvider dataWaypoints
     *
     * @param mixed $waypointDto
     * @param mixed $waypointEntity
     */
    public function testBuildReturnsAWaypointEntityWithIdenticalProperties($waypointDto, $waypointEntity)
    {
        $this->assertEquals($waypointEntity, $this->_waypointEntityBuilder->build($waypointDto));
    }

    public function dataWaypoints(): array
    {
        return [
            [Waypoint::getWaypointDto(), Waypoint::getWaypointEntity()],
        ];
    }
}
