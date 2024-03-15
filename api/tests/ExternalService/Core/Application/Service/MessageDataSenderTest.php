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

namespace App\ExternalService\Core\Application\Service;

use App\ExternalService\Core\Application\Exception\ContextNotProvidedException;
use App\ExternalService\Core\Application\Service\MessageDataSender as ServiceMessageDataSender;
use App\ExternalService\Infrastructure\MessageBrokerPublisher;
use App\Mapper\Interfaces\DTO\CarpoolProof\CarpoolProofDTO;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class MessageDataSenderTest extends TestCase
{
    private $_dataSender;

    public function setUp(): void
    {
        $messageDataSender = $this->getMockBuilder(MessageBrokerPublisher::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $messageDataSender->method('send')->willReturn('OK');

        $this->_dataSender = new ServiceMessageDataSender($messageDataSender);
    }

    /**
     * @test
     */
    public function testSendReturnOk()
    {
        $carpoolProofDTO = new CarpoolProofDTO();
        $this->assertEquals($this->_dataSender->send($carpoolProofDTO), 'OK');
    }

    /**
     * @test
     */
    public function testEntityWithoutDomainRaisesException()
    {
        $this->expectException(ContextNotProvidedException::class);

        $carpoolProofDTO = $this->getMockBuilder(CarpoolProofDTO::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $this->_dataSender->send($carpoolProofDTO);
    }
}
