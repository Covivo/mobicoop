<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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
 **************************/

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ask;
use Psr\Log\LoggerInterface;

/**
 * Ad management service.
 */
class AskManager
{

    private $dataProvider;
    private $logger;
    
    /**
     * Constructor.
     *
     */
    public function __construct(DataProvider $dataProvider, LoggerInterface $loggerInterface)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Ask::class);
        $this->logger = $loggerInterface;
    }

    /**
     * Get an Ask by its identifier
     *
     * @param int $id The Ask id
     *
     * @return Ask|null The Ask found or null if not found.
     */
    public function getAsk(int $id)
    {
        $response = $this->dataProvider->getItem($id);
        if ($response->getCode() == 200) {
            $ask = $response->getValue();
            $this->logger->info('Ask | Is found');
            return $ask;
        }
        $this->logger->error('Ask | is Not found');
        return null;
    }
    
    /**
     * Update an Ask
     *
     * @param Ask $ask The Ask to update
     *
     * @return Ask|null The Ask updated or null if error.
     */
    public function updateAsk(Ask $ask)
    {
        $response = $this->dataProvider->put($ask);
        if ($response->getCode() == 200) {
            $this->logger->info('Ask Update | Start');
            return $response->getValue();
        }
        return null;
    }    
}
