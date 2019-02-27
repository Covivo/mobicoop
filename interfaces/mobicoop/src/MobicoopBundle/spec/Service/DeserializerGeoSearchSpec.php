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

namespace Mobicoop\Bundle\MobicoopBundle\Spec\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\Deserializer;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\GeoSearch;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;

/**
 * DeserializerGeoSearchSpec.php
 * Tests for Deserializer - GeoSearch
 * @author Sofiane Belaribi <sofiane.belaribi@mobicoop.org>
 * Date: 24/12/2018
 * Time: 13:46
 *
 */

describe('deserializeGeoSearch', function () {
    describe('deserialize GeoSearch', function () {
        it('deserialize GeoSearch should return an Address object', function () {
            $jsonGeoSearch = <<<JSON
  {
    "@id": "\/addresses\/1",
    "id": 0
  }
JSON;

            $deserializer = new Deserializer();
            $GeoSearch = $deserializer->deserialize(GeoSearch::class, json_decode($jsonGeoSearch, true));
            expect($GeoSearch)->toBeAnInstanceOf(Address::class);
        });
    });
});
