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

use App\ExternalService\Core\Domain\Entity\AbstractEntity;
use App\ExternalService\Core\Domain\Entity\CarpoolProof\CarpoolProofEntity;
use App\Tests\ExternalService\Mock\CarpoolProof;
use App\Tests\ExternalService\Mock\Waypoint;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class CarpoolProofEntityBuilderTest extends TestCase
{
    private $_carpoolProofEntityBuilder;

    public function setUp(): void
    {
        $waypointEntityBuilder = $this->getMockBuilder(WaypointEntityBuilder::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $waypointEntityBuilder->method('build')->willReturn(Waypoint::getWaypointEntity());
        $this->_carpoolProofEntityBuilder = new CarpoolProofEntityBuilder($waypointEntityBuilder);
    }

    /**
     * @test
     */
    public function testBuildReturnsACarpoolProofEntity()
    {
        $this->assertInstanceOf(CarpoolProofEntity::class, $this->_carpoolProofEntityBuilder->build(CarpoolProof::getCarpoolProofDto()));
    }

    /**
     * @test
     *
     * @dataProvider dataCarpoolProofs
     *
     * @param mixed $carpoolProofDto
     * @param mixed $carpoolProofEntity
     */
    public function testBuildReturnsACarpoolProofEntityWithIdenticalProperties($carpoolProofDto, $carpoolProofEntity)
    {
        $this->assertEquals($carpoolProofEntity, $this->_carpoolProofEntityBuilder->build($carpoolProofDto));
    }

    /**
     * @test
     *
     * @dataProvider dataCarpoolProofs
     *
     * @param mixed $carpoolProofDto
     * @param mixed $carpoolProofEntity
     */
    public function testBuildReturnsACarpoolProofEntityWithAContextProperties($carpoolProofDto, $carpoolProofEntity)
    {
        $carpoolProofEntity = $this->_carpoolProofEntityBuilder->build($carpoolProofDto);
        $this->assertNotEmpty($carpoolProofEntity->getContext());
    }

    /**
     * @test
     *
     * @dataProvider dataCarpoolProofs
     *
     * @param mixed $carpoolProofDto
     * @param mixed $carpoolProofEntity
     */
    public function testBuildReturnsACarpoolProofEntityImplementingAbstractClass($carpoolProofDto, $carpoolProofEntity)
    {
        $this->assertInstanceOf(AbstractEntity::class, $this->_carpoolProofEntityBuilder->build(CarpoolProof::getCarpoolProofDto()));
    }

    public function dataCarpoolProofs(): array
    {
        return [
            [CarpoolProof::getCarpoolProofDto(), CarpoolProof::getCarpoolProofEntity()],
        ];
    }
}
