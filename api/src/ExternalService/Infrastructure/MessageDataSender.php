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

use App\ExternalService\Infrastructure\Exception\BrokerConnectionException;
use App\ExternalService\Infrastructure\Exception\BrokerMissingParamException;
use App\ExternalService\Infrastructure\Exception\BrokerPublishException;
use App\ExternalService\Infrastructure\Exception\UnauthorizedContextException;
use App\ExternalService\Interfaces\DTO\DTO;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MessageDataSender extends BrokerPublisher
{
    private const AUTHORIZED_CONTEXTS = [
        'CarpoolProof' => 'carpool.proof',
    ];

    public function send(DTO $data): string
    {
        if (!isset(self::AUTHORIZED_CONTEXTS[$data->getContext()])) {
            throw new UnauthorizedContextException(UnauthorizedContextException::MESSAGE);
        }

        if (is_null($data)) {
            throw new BrokerMissingParamException(BrokerMissingParamException::MESSAGE_MISSING_DATA);
        }

        try {
            $this->connect();
        } catch (\Exception $e) {
            throw new BrokerConnectionException(BrokerConnectionException::MESSAGE.PHP_EOL.$e->getMessage());
        }

        try {
            $this->publish($data);
        } catch (\Exception $e) {
            throw new BrokerPublishException(BrokerPublishException::MESSAGE.PHP_EOL.$e->getMessage());
        }

        $this->close();

        return 'OK';
    }

    public function publish(DTO $data)
    {
        $this->_channel->exchange_declare(self::AUTHORIZED_CONTEXTS[$data->getContext()], 'fanout', false, false, false);
        $msg = new AMQPMessage(json_encode($data));
        $this->_channel->basic_publish($msg, self::AUTHORIZED_CONTEXTS[$data->getContext()]);
    }
}
