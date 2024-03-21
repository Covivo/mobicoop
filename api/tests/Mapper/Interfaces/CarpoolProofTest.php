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

namespace App\Mapper\Interfaces;

use App\Carpool\Event\CarpoolProofCreatedEvent;
use App\ExternalService\Core\Application\Service\MessageDataSender;
use App\ExternalService\Interfaces\MessageSend;
use App\Mapper\Core\Domain\Builder\CarpoolProofBuilder;
use App\Tests\Mapper\Mock\CarpoolProof as MockCarpoolProof;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class CarpoolProofTest extends TestCase
{
    private $_carpoolProof;
    private $_carpoolProofDisabled;

    public function setUp(): void
    {
        $messageDataSender = $this->getMockBuilder(MessageDataSender::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $messageDataSender->method('send')->willReturn('OK');
        $messageSend = new MessageSend($messageDataSender);

        $this->_carpoolProof = new CarpoolProof(new CarpoolProofBuilder(), $messageSend, true);
        $this->_carpoolProofDisabled = new CarpoolProof(new CarpoolProofBuilder(), $messageSend, false);
    }

    /**
     * @test
     */
    public function testMapReturnsOK()
    {
        $carpoolProofCreatedEvent = new CarpoolProofCreatedEvent(MockCarpoolProof::getCarpoolProof());

        $this->assertEquals('OK', $this->_carpoolProof->map($carpoolProofCreatedEvent));
    }

    /**
     * @test
     */
    public function testDisabledMapReturnsNull()
    {
        $carpoolProofCreatedEvent = new CarpoolProofCreatedEvent(MockCarpoolProof::getCarpoolProof());

        $this->assertNull($this->_carpoolProofDisabled->map($carpoolProofCreatedEvent));
    }
}
