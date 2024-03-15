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

namespace App\Mapper\Core\Domain\Builder;

use App\Carpool\Entity\CarpoolProof;
use App\ExternalService\Interfaces\DTO\DTO;
use App\Tests\Mapper\Mock\CarpoolProof as MockCarpoolProof;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class CarpoolProofBuilderTest extends TestCase
{
    private $_carpoolProofBuilder;

    public function setUp(): void
    {
        $this->_carpoolProofBuilder = new CarpoolProofBuilder();
    }

    /**
     * @test
     */
    public function testBuildReturnsADTO()
    {
        $carpoolProof = $this->getMockBuilder(CarpoolProof::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->assertInstanceOf(DTO::class, $this->_carpoolProofBuilder->build($carpoolProof));
    }

    /**
     * @test
     */
    public function testBuildReturnsTheRightCarpoolProofDTO()
    {
        $carpoolProof = $this->getMockBuilder(CarpoolProof::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->assertEquals(MockCarpoolProof::getCarpoolProofDto(), $this->_carpoolProofBuilder->build(MockCarpoolProof::getCarpoolProof()));
    }
}
