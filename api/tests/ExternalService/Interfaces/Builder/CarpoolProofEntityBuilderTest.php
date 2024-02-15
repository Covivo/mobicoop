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

namespace App\ExternalService\Interfaces;

use App\ExternalService\Core\Domain\Entity\CarpoolProof\CarpoolProofEntity;
use App\ExternalService\Interfaces\Builder\CarpoolProofEntityBuilder;
use App\ExternalService\Interfaces\DTO\CarpoolProof\CarpoolProofDto;
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
        $this->_carpoolProofEntityBuilder = new CarpoolProofEntityBuilder();
    }

    /**
     * @test
     */
    public function testBuildReturnsACarpoolProofEntity()
    {
        $carpoolProofDto = new CarpoolProofDto();
        $this->assertInstanceOf(CarpoolProofEntity::class, $this->_carpoolProofEntityBuilder->build($carpoolProofDto));
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

    public function dataCarpoolProofs(): array
    {
        $carpoolProofDto = new CarpoolProofDto();
        $carpoolProofDto->setId(1);
        $carpoolProofDto->setDistance(10000);
        $carpoolProofEntity = new CarpoolProofEntity();
        $carpoolProofEntity->setId(1);
        $carpoolProofEntity->setDistance(10000);

        return [
            [$carpoolProofDto, $carpoolProofEntity],
        ];
    }
}
