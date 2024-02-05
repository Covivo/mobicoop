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

namespace App\DataProvider\Entity\MessageBrokerV3;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MessageBrokerProvider
{
    private $_brokerUri;
    private $_brokerPort;
    private $_brokerUsername;
    private $_brokerPassword;

    public function __construct(
        string $brokerUri,
        int $brokerPort,
        string $brokerUsername,
        string $brokerPassword
    ) {
        $this->_brokerUri = $brokerUri;
        $this->_brokerPort = $brokerPort;
        $this->_brokerUsername = $brokerUsername;
        $this->_brokerPassword = $brokerPassword;
    }
}
