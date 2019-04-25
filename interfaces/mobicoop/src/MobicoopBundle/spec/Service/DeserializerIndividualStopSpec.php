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
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\IndividualStop;

describe('deserializeIndividualStop', function () {
    describe('deserialize IndividualStop', function () {
        it('deserializeIndividualStop should return data given', function () {
            $jsonIndividualStop = <<<JSON
{
  "id": 0,
  "position": 0,
  "delay": 0,
  "address": {
    "id": 0,
    "streetAddress": "string",
    "postalCode": "string",
    "addressLocality": "string",
    "addressCountry": "string",
    "latitude": "string",
    "longitude": "string",
    "elevation": 0,
    "name": "string"
  }
}
JSON;

            $deserializer = new Deserializer();
            $individualStop = $deserializer->deserialize(IndividualStop::class, json_decode($jsonIndividualStop, true));
            expect($individualStop)->toBe($individualStop);
        });
    });
});
