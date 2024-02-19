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

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BrokerConnector
{
    private $_uri;
    private $_port;
    private $_username;
    private $_password;

    private $_connection;
    private $_channel;

    public function __construct(string $uri, int $port, string $username, string $password)
    {
        $this->_uri = $uri;
        $this->_port = $port;
        $this->_username = $username;
        $this->_password = $password;
    }

    public function sendTopicMessage(string $topic, string $routingKey, $data): string
    {
        return $this->_send($topic, $routingKey, $data);
    }

    protected function _send(string $topic, string $routingKey, $data): string
    {
        $this->_connect();
        $this->_channel->exchange_declare($topic, 'topic', false, false, false);
        $msg = new AMQPMessage($data);
        $this->_channel->basic_publish($msg, $topic, $routingKey);
        $this->_close();

        return 'OK';
    }

    private function _connect()
    {
        $this->_connection = new AMQPStreamConnection($this->_uri, $this->_port, $this->_username, $this->_password);
        $this->_channel = $this->_connection->channel();
    }

    private function _close()
    {
        $this->_channel->close();
        $this->_connection->close();
    }
}
