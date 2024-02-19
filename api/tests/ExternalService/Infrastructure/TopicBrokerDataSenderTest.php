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

namespace App\ExternalService\Infrastructure;

use App\ExternalService\Core\Domain\Entity\CarpoolProof\CarpoolProofEntity;
use App\ExternalService\Infrastructure\Exception\UnauthorizedContextException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class TopicBrokerDataSenderTest extends TestCase
{
    private $_brokerDataSender;

    public function setUp(): void
    {
        $brokerConnector = $this->getMockBuilder(BrokerConnector::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $brokerConnector->method('sendTopicMessage')->willReturn('OK');

        $this->_brokerDataSender = new TopicBrokerDataSender($brokerConnector);
    }

    /**
     * @test
     */
    public function testSendReturnOk()
    {
        $carpoolProofEntity = $this->getMockBuilder(CarpoolProofEntity::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $carpoolProofEntity->method('getContext')->willReturn('CarpoolProof');

        $this->assertEquals('OK', $this->_brokerDataSender->send($carpoolProofEntity));
    }

    /**
     * @test
     */
    public function testContextUnauthorizedRaisesException()
    {
        $this->expectException(UnauthorizedContextException::class);

        $carpoolProofEntity = $this->getMockBuilder(CarpoolProofEntity::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $carpoolProofEntity->method('getContext')->willReturn('UnauthorizedContext');

        $this->_brokerDataSender->send($carpoolProofEntity);
    }
}
