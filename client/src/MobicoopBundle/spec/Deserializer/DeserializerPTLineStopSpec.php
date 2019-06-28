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
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTLineStop;

/**
 * DeserializerPTLineStopSpec.php
 * Tests for Deserializer - PTLineStop
 * @author Sylvain briat <sylvain.briat@mobicoop.org>
 * Date: 24/06/2019
 * Time: 14:00
 *
 */

describe('deserializePTLineStop', function () {
    it('deserialize PTLineStop should return an PTLineStop object', function () {
        $jsonPTLineStop = <<<JSON
{
  "id": 0,
  "direction": 0,
  "line": {
    "name": "string",
    "number": "string",
    "origin": "string",
    "destination": "string",
    "direction": "string",
    "ptcompany": {
      "name": "string"
    },
    "travelMode": {
      "name": "string"
    },
    "transportMode": 0,
    "color": "string"
  },
  "lineId": 0,
  "stop": {
    "id": 0,
    "name": "string",
    "latitude": 0,
    "longitude": 0,
    "accessibilityStatus": {
      "id": 0,
      "blindAccess": 0,
      "deafAccess": 0,
      "mentalIllnessAccess": 0,
      "wheelChairAccess": 0
    },
    "isDisrupted": "string",
    "pointType": 0
  },
  "stopId": 0
}
JSON;

        $deserializer = new Deserializer();
        $PTLineStop = $deserializer->deserialize(PTLineStop::class, json_decode($jsonPTLineStop, true));
        expect($PTLineStop)->toBeAnInstanceOf(PTLineStop::class);
    });
});
