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

use App\Tests\ExternalService\Mock\Waypoint;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class ToolsTest extends TestCase
{
    private $_reference;
    private $_dto;
    private $_entity;

    public function setUp(): void
    {
        $this->_reference = Waypoint::getWaypointDto();

        $this->_dto = Waypoint::getWaypointDto();

        $this->_entity = Waypoint::getWaypointEntity();
    }

    /**
     * @test
     */
    public function testCloneSimpleObjectDtoToEntityReturnsObject()
    {
        $this->assertIsObject(Tools::cloneSimpleObjectDtoToEntity($this->_reference, $this->_dto, $this->_entity));
    }

    /**
     * @test
     */
    public function testCloneSimpleObjectDtoToEntityReturnsADriverEntityWithIdenticalProperties()
    {
        $this->assertEquals($this->_entity, Tools::cloneSimpleObjectDtoToEntity($this->_reference, $this->_dto, $this->_entity));
    }
}
