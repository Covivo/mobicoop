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

namespace App\PublicTransport\Service;

use App\PublicTransport\Entity\Journey;
use App\DataProvider\Entity\CitywayProvider;

/**
 * Public transport DataProvider.
 *
 * To add a provider :
 * - write the custom Provider class
 * - complete the PROVIDERS array with the new provider
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class PTDataProvider
{
    const PROVIDERS = [
        "cityway" => CitywayProvider::class
    ];
        
    public function getJourneys($provider, $apikey, $origin_latitude, $origin_longitude, $destination_latitude, $destination_longitude, $date)
    {
        if (!array_key_exists($provider, self::PROVIDERS)) {
            return null;
        }
        $providerClass = self::PROVIDERS[$provider];
        $providerInstance = new $providerClass();
        return call_user_func_array([$providerInstance,"getCollection"], [Journey::class,$apikey,[
                "origin_latitude" => $origin_latitude,
                "origin_longitude" => $origin_longitude,
                "destination_latitude" => $destination_latitude,
                "destination_longitude" => $destination_longitude,
                "date" => $date
        ]]);
    }
}
