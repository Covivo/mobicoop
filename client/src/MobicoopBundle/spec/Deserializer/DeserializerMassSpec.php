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
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\Mass;

/**
 * DeserializerMassSpec.php
 * Tests for Deserializer - Mass
 * @author Sylvain briat <sylvain.briat@mobicoop.org>
 * Date: 24/06/2019
 * Time: 14:00
 *
 */

describe('deserializeMass', function () {
    it('deserialize Mass should return an Mass object', function () {
        $jsonMass = <<<JSON
{
  "id": 0,
  "status": 0,
  "fileName": "string",
  "originalName": "string",
  "size": 0,
  "mimeType": "string",
  "createdDate": "2019-06-25T12:35:44.131Z",
  "analyzeDate": "2019-06-25T12:35:44.131Z",
  "calculationDate": "2019-06-25T12:35:44.131Z",
  "persons": [
    {
      "id": 0,
      "givenId": "string",
      "personalAddress": {
        "houseNumber": "string",
        "street": "string",
        "streetAddress": "string",
        "postalCode": "string",
        "subLocality": "string",
        "addressLocality": "string",
        "localAdmin": "string",
        "county": "string",
        "macroCounty": "string",
        "region": "string",
        "macroRegion": "string",
        "addressCountry": "string",
        "countryCode": "string",
        "latitude": "string",
        "longitude": "string",
        "elevation": 0
      },
      "workAddress": {
        "houseNumber": "string",
        "street": "string",
        "streetAddress": "string",
        "postalCode": "string",
        "subLocality": "string",
        "addressLocality": "string",
        "localAdmin": "string",
        "county": "string",
        "macroCounty": "string",
        "region": "string",
        "macroRegion": "string",
        "addressCountry": "string",
        "countryCode": "string",
        "latitude": "string",
        "longitude": "string",
        "elevation": 0
      },
      "distance": 0,
      "duration": 0,
      "bboxMinLon": "string",
      "bboxMinLat": "string",
      "bboxMaxLon": "string",
      "bboxMaxLat": "string",
      "bearing": 0,
      "matchingsAsDriver": [
        {
          "massPerson1Id": 0,
          "massPerson2Id": 0,
          "distance": 0,
          "duration": 0
        }
      ],
      "matchingsAsPassenger": [
        {
          "massPerson1Id": 0,
          "massPerson2Id": 0,
          "distance": 0,
          "duration": 0
        }
      ],
      "outwardTime": "2019-06-25T12:35:44.131Z",
      "returnTime": "2019-06-25T12:35:44.131Z",
      "driver": true,
      "passenger": true
    }
  ],
  "errors": [
    "string"
  ]
}
JSON;

        $deserializer = new Deserializer();
        $mass = $deserializer->deserialize(Mass::class, json_decode($jsonMass, true));
        expect($mass)->toBeAnInstanceOf(Mass::class);
    });
});
