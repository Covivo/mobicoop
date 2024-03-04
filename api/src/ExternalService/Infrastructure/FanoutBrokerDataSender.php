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

use App\ExternalService\Core\Application\Ports\DataSenderPort;
use App\ExternalService\Core\Domain\Entity\AbstractEntity;
use App\ExternalService\Infrastructure\Exception\UnauthorizedContextException;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class FanoutBrokerDataSender implements DataSenderPort
{
    private const AUTHORIZED_CONTEXTS = [
        'CarpoolProof' => 'carpool.proof',
    ];

    private $_brokerConnector;

    public function __construct(BrokerConnector $brokerConnector)
    {
        $this->_brokerConnector = $brokerConnector;
    }

    public function send(AbstractEntity $entity): string
    {
        if (!isset(self::AUTHORIZED_CONTEXTS[$entity->getContext()])) {
            throw new UnauthorizedContextException(UnauthorizedContextException::MESSAGE);
        }

        return $this->_brokerConnector->sendMessage(self::AUTHORIZED_CONTEXTS[$entity->getContext()], $entity);
    }
}
