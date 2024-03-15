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

use App\ExternalService\Core\Application\Service\MessageDataSender;
use App\Mapper\Interfaces\DTO\CarpoolProof\CarpoolProofDTO;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class MessageSendTest extends TestCase
{
    private $_messageSend;

    public function setUp(): void
    {
        $messageDataSender = $this->getMockBuilder(MessageDataSender::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $messageDataSender->method('send')->willReturn('OK');

        $this->_messageSend = new MessageSend($messageDataSender);
    }

    /**
     * @test
     */
    public function testSendReturnsOk()
    {
        $carpoolProofDto = new CarpoolProofDTO();

        $this->assertEquals($this->_messageSend->send($carpoolProofDto), 'OK');
    }
}
