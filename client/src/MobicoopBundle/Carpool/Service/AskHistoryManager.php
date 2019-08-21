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
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\AskHistory;

/**
 * AskHistory management service.
 */
class AskHistoryManager
{
    private $dataProvider;
    
    /**
     * Constructor.
     *
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(AskHistory::class);
    }

    /**
     * Get an AskHistory by its identifier
     *
     * @param int $id The Ask id
     *
     * @return AskHistory|null The AskHistory found or null if not found.
     */
    public function getAskHistory(int $id)
    {
        $response = $this->dataProvider->getItem($id);
            $ask = $response->getValue();
            return $ask;
    }
    
    /**
     * Create a AskHistory
     *
     * @param AskHistory    $proposal The proposal to create
     * @param int           $format The format to use
     *
     * @return AskHistory|null The AskHistory created or null if error.
     */
    public function createAskHistory(AskHistory $askHistory, int $format=null)
    {
        if ($format!==null) {
            $this->dataProvider->setFormat($format);
        }

        $response = $this->dataProvider->post($askHistory);

        if ($response->getCode() == 201) {
            return $response->getValue();
        }
        return null;
    }
}
