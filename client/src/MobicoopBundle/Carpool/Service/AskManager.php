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

/**
 * Ask management service.
 */
class AskManager
{
    private $dataProvider;
    
    /**
     * Constructor.
     *
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Ask::class);
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
            return $ask;
        }
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
            return $response->getValue();
        }
        return null;
    }

    /**
     * Get all the AskHistories of an Ask
     *
     * @param int $idAsk The Ask id
     *
     * @return array|null The AskHistories found or null if not found.
     */
    public function getAskHistories(int $idAsk)
    {
        $this->dataProvider->setFormat($this->dataProvider::RETURN_JSON);
        $response = $this->dataProvider->getSubCollection($idAsk, 'askhistory', 'ask_histories');
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }
}
