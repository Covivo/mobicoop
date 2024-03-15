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
use App\Mapper\Core\Application\Ports\BuilderPort;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CarpoolProof implements EventSubscriberInterface
{
    private $_carpoolProofBuilder;
    private $_externalServiceEnabled;

    public function __construct(BuilderPort $carpoolProofBuilder, bool $externalServiceEnabled)
    {
        $this->_carpoolProofBuilder = $carpoolProofBuilder;
        $this->_externalServiceEnabled = $externalServiceEnabled;
    }

    public static function getSubscribedEvents()
    {
        return [
            CarpoolProofCreatedEvent::NAME => 'map',
        ];
    }

    public function map(CarpoolProofCreatedEvent $carpoolProofCreatedEvent): ?string
    {
        if (!$this->_externalServiceEnabled) {
            return null;
        }

        $this->_carpoolProofBuilder->build($carpoolProofCreatedEvent->getCarpoolProof());

        return 'OK';
    }
}
