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

namespace Mobicoop\Bundle\MobicoopBundle\Geography\Service;

use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\GeoSearch;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;

/**
 * GeoSearchManager.php
 * Geopoint search management service.
 * @author Sofiane Belaribi <sofiane.belaribi@mobicoop.org>
 * Date: 29/11/2018
 * Time: 16:38
 *
 */

class GeoSearchManager
{
    private $dataProvider;

    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(GeoSearch::class, 'geo_search');
    }

    /**
     * Get all Geosearch results
     *
     * @param array $params The params to send for geosearching
     * @return array|GeoSearch|null
     */
    public function getGeoSearch(array $params)
    {
        $response = $this->dataProvider->getCollection($params);
        if ($response->getCode() == 200) {
            return $response->getValue();
        }
        return null;
    }
}
