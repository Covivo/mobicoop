<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\CarpoolStandard\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\CarpoolStandard\Entity\Message;
use Symfony\Component\Security\Core\Security;

/**
 * Message management service.
 */
class MessageManager
{
    private $dataProvider;

    private $security;

    /**
     * Constructor.
     *
     * @throws \ReflectionException
     */
    public function __construct(DataProvider $dataProvider, Security $security)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Message::class, 'carpool_standard');
        $this->dataProvider->setFormat(DataProvider::RETURN_OBJECT);
        $this->security = $security;
    }

    public function postCarpoolStandardMessage(Message $message)
    {
        $response = $this->dataProvider->post($message, 'messages');
        if (201 != $response->getCode()) {
            return $response->getValue();
        }

        return $response->getValue();
    }
}
