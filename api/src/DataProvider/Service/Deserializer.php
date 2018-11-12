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

namespace App\DataProvider\Service;

use App\PublicTransport\Entity\Journey;
use App\PublicTransport\Service\PTDataProvider;

/**
 * Custom deserializer service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class Deserializer
{
    private $provider;
    
    public function __construct($provider)
    {
        $this->provider = $provider;
    }

    /**
     * Deserialize an object.
     *
     * @param string $class The expected class of the object
     * @param array $data   The array to deserialize
     * @return Journey|null
     */
    public function deserialize(string $class, array $data)
    {
        switch ($class) {
            case Journey::class:
                return call_user_func_array([$this,PTDataProvider::PROVIDERS[$this->provider]["deserialize_method"]], [$data]);
                break;
            default:
                break;
        }
        return null;
    }
    
    private function deserializeCitywayJourney(array $data): ?Journey
    {
        var_dump($data);
        exit;
        /*$journey = new Journey(0);
        $journey = self::autoSet($journey, $data);
        if (isset($data["userAddresses"])) {
            $userAddresses = [];
            foreach ($data["userAddresses"] as $userAddress) {
                $userAddresses[] = self::deserializeUserAddress($userAddress);
            }
            $user->setUserAddresses($userAddresses);
        }
        return $user;*/
    }
}
