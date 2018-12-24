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

use Mobicoop\Bundle\MobicoopBundle\Service\Deserializer;
use Mobicoop\Bundle\MobicoopBundle\Entity\PTJourney;

/**
 * DeserializerPTSpec.php
 * Tests for Deserializer - PTArrival | PTCompany | PTDeparture | PTJourney | PTLeg | PTLeg | PTLine | PTLine | PTMode | PTStep
 * @author Sofiane Belaribi <sofiane.belaribi@mobicoop.org>
 * Date: 24/12/2018
 * Time: 12:36
 *
 */

describe('deserializePT', function () {
    describe('deserialize PTJourney', function () {
        it('deserializePTJourney should return a PTJourney object', function () {
            $jsonPTJourney = <<<JSON
{
    "distance": 0,
    "duration": 0,
    "changeNumber": 0,
    "price": 0,
    "co2": 0,
    "ptdeparture": {
      "name": "string",
      "date": "2018-12-24T12:40:59.973Z",
      "address": {
        "streetAddress": "string",
        "postalCode": "string",
        "addressLocality": "string",
        "addressCountry": "string",
        "latitude": "string",
        "longitude": "string",
        "elevation": 0
      }
    },
    "ptarrival": {
      "name": "string",
      "date": "2018-12-24T12:40:59.973Z",
      "address": {
        "streetAddress": "string",
        "postalCode": "string",
        "addressLocality": "string",
        "addressCountry": "string",
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
        "pos": 0,
        "last": true,
        "magneticDirection": "string",
        "relativeDirection": "string",
        "ptdeparture": {
          "name": "string",
          "date": "2018-12-24T12:40:59.973Z",
          "address": {
            "streetAddress": "string",
            "postalCode": "string",
            "addressLocality": "string",
            "addressCountry": "string",
            "latitude": "string",
            "longitude": "string",
            "elevation": 0
          }
        },
        "ptarrival": {
          "name": "string",
          "date": "2018-12-24T12:40:59.973Z",
          "address": {
            "streetAddress": "string",
            "postalCode": "string",
            "addressLocality": "string",
            "addressCountry": "string",
            "latitude": "string",
            "longitude": "string",
            "elevation": 0
          }
        },
        "ptmode": {
          "name": "string"
        },
        "ptline": {
          "name": "string",
          "number": "string",
          "origin": "string",
          "destination": "string",
          "ptcompany": {
            "name": "string"
          }
        },
        "direction": "string",
        "ptsteps": [
          {
            "distance": 0,
            "duration": 0,
            "pos": 0,
            "last": true,
            "magneticDirection": "string",
            "relativeDirection": "string",
            "ptsection": "string",
            "ptdeparture": {
              "name": "string",
              "date": "2018-12-24T12:40:59.973Z",
              "address": {
                "streetAddress": "string",
                "postalCode": "string",
                "addressLocality": "string",
                "addressCountry": "string",
                "latitude": "string",
                "longitude": "string",
                "elevation": 0
              }
            },
            "ptarrival": {
              "name": "string",
              "date": "2018-12-24T12:40:59.973Z",
              "address": {
                "streetAddress": "string",
                "postalCode": "string",
                "addressLocality": "string",
                "addressCountry": "string",
                "latitude": "string",
                "longitude": "string",
                "elevation": 0
              }
            }
          }
        ]
      }
    ]
  }
JSON;

            $deserializer = new Deserializer();
            $PTJourney = $deserializer->deserialize(PTJourney::class, json_decode($jsonPTJourney, true));
            expect($PTJourney)->toBeAnInstanceOf(PTJourney::class);
        });
    });
});
