<?php

/**
 * Copyright (c) 2025, MOBICOOP. All rights reserved.
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

namespace App\PublicTransport\Service;

use App\Geography\Repository\TerritoryRepository;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ProviderFinder
{
    private $_territoryRepository;
    private $_originLatitude;
    private $_originLongitude;
    private $_PTProviders;

    private $_territoryId;

    public function __construct(TerritoryRepository $territoryRepository, array $PTProviders, float $originLatitude, float $originLongitude)
    {
        $this->_territoryRepository = $territoryRepository;
        $this->_originLatitude = $originLatitude;
        $this->_originLongitude = $originLongitude;
        $this->_PTProviders = $PTProviders;
        $this->_territoryId = 'default';
    }

    public function findProvider(): array
    {
        if (count($this->_PTProviders) > 1) {
            // If there is a territory, we look for the right provider. If there is no, we take the default.
            // Get the territory of the request
            $territories = $this->_territoryRepository->findPointTerritories($this->_originLatitude, $this->_originLongitude);
            foreach ($territories as $territory) {
                // If the territoryId is in the providers.json for PT, we use this one
                if (isset($this->_PTProviders[$territory['id']])) {
                    $this->_territoryId = $territory['id'];

                    break;
                }
            }
        }

        return (isset($this->_PTProviders[$this->_territoryId])) ? $this->_PTProviders[$this->_territoryId] : [];
    }

    public function getTerritoryId(): string
    {
        return $this->_territoryId;
    }
}
