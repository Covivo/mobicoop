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

use App\ExternalService\Core\Domain\Entity\CarpoolProof\DriverEntity;
use App\ExternalService\Core\Domain\Entity\CarpoolProof\PassengerEntity;
use App\Tests\ExternalService\Mock\Actor;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class ActorEntityBuilderTest extends TestCase
{
    private $_actorEntityBuilder;

    public function setUp(): void
    {
        $this->_actorEntityBuilder = new ActorEntityBuilder();
    }

    /**
     * @test
     */
    public function testBuildDriverReturnsADriverEntity()
    {
        $this->assertInstanceOf(DriverEntity::class, $this->_actorEntityBuilder->buildDriver(Actor::getDriverDto()));
    }

    /**
     * @test
     */
    public function testBuildPassengerReturnsADriverEntity()
    {
        $this->assertInstanceOf(PassengerEntity::class, $this->_actorEntityBuilder->buildPassenger(Actor::getPassengerDto()));
    }

    /**
     * @test
     *
     * @dataProvider dataDriver
     *
     * @param mixed $driverDto
     * @param mixed $driverEntity
     */
    public function testBuildDriverReturnsADriverEntityWithIdenticalProperties($driverDto, $driverEntity)
    {
        $this->assertEquals($driverEntity, $this->_actorEntityBuilder->buildDriver($driverDto));
    }

    /**
     * @test
     *
     * @dataProvider dataDriver
     *
     * @param mixed $passengerDto
     * @param mixed $passengerEntity
     */
    public function testBuildPassengerReturnsPassengerEntityWithIdenticalProperties($passengerDto, $passengerEntity)
    {
        $this->assertEquals($passengerEntity, $this->_actorEntityBuilder->buildPassenger($passengerDto));
    }

    public function dataDriver(): array
    {
        return [
            [Actor::getDriverDto(), Actor::getDriverEntity()],
        ];
    }

    public function dataPassenger(): array
    {
        return [
            [Actor::getPassengerDto(), Actor::getPassengerEntity()],
        ];
    }
}
