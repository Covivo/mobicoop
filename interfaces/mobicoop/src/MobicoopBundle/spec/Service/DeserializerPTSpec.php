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
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTJourney;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTLeg;
use Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity\PTStep;

/**
 * DeserializerPTSpec.php
 * Tests for Deserializer - PTArrival | PTCompany | PTDeparture | PTJourney | PTLeg | PTStep | PTLine | TravelMode
 * @author Sofiane Belaribi <sofiane.belaribi@mobicoop.org>
 * Date: 24/12/2018
 * Time: 12:36
 *
 */

describe('deserializePT', function () {
    describe('deserialize PTJourney', function () {
        it('deserialize PTJourney should return a PTJourney object', function () {
            $jsonPTJourney = <<<JSON
{
  "distance": 0,
  "duration": "string",
  "changeNumber": 0,
  "price": "string",
  "co2": 0,
  "ptdeparture": {
    "name": "string",
    "date": "2019-04-29T15:36:55.474Z",
    "address": {
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
    }
  },
  "ptarrival": {
    "name": "string",
    "date": "2019-04-29T15:36:55.474Z",
    "address": {
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
    }
  },
  "ptlegs": [
    {
      "indication": "string",
      "distance": 0,
      "duration": 0,
      "position": 0,
      "isLast": true,
      "magneticDirection": "string",
      "relativeDirection": "string",
      "ptdeparture": {
        "name": "string",
        "date": "2019-04-29T15:36:55.474Z",
        "address": {
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
        }
      },
      "ptarrival": {
        "name": "string",
        "date": "2019-04-29T15:36:55.474Z",
        "address": {
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
        }
      },
      "travelMode": {
        "name": "string"
      },
      "ptline": {
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
        }
      },
      "direction": "string",
      "ptsteps": [
        {
          "distance": 0,
          "duration": 0,
          "position": 0,
          "isLast": true,
          "magneticDirection": "string",
          "relativeDirection": "string",
          "ptdeparture": {
            "name": "string",
            "date": "2019-04-29T15:36:55.474Z",
            "address": {
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
            }
          },
          "ptarrival": {
            "name": "string",
            "date": "2019-04-29T15:36:55.474Z",
            "address": {
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
            }
          },
          "geometry": "string"
        }
      ]
    }
  ]
}
JSON;

            $deserializer = new Deserializer();
            $PTJourney = $deserializer->deserialize(PTJourney::class, json_decode($jsonPTJourney, true));
            expect($PTJourney)->toBeAnInstanceOf(PTJourney::class);
            expect($PTJourney->getPTLegs()[0])->toBeAnInstanceOf(PTLeg::class);
            expect($PTJourney->getPTLegs()[0]->getPTSteps()[0])->toBeAnInstanceOf(PTStep::class);
        });
    });
});
