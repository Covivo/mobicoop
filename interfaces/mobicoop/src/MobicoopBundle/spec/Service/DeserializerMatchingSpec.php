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
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Matching;

/**
 * DeserializerMatchingSpec.php
 * Tests for Deserializer - Matching
 * @author Sofiane Belaribi <sofiane.belaribi@mobicoop.org>
 * Date: 24/12/2018
 * Time: 14:04
 *
 */

describe('deserializeMatching', function () {
    describe('deserialize Matching', function () {
        it('deserialize Matching should return a Matching object', function () {
            $jsonMatching = <<<JSON
{
  "id": 0,
  "proposalOffer": {
    "id": 0,
    "type": 0,
    "user": {
      "id": 0,
      "status": 0,
      "givenName": "string",
      "familyName": "string",
      "email": "string",
      "gender": "female",
      "nationality": "string",
      "birthDate": "string",
      "telephone": "string",
      "maxDeviationTime": 0,
      "maxDeviationDistance": 0,
      "anyRouteAsPassenger": true,
      "multiTransportMode": true,
      "addresses": [
        {
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
      ],
      "cars": [
        {
          "id": 0,
          "brand": "string",
          "model": "string",
          "color": "string",
          "siv": "string",
          "seats": 0
        }
      ]
    },
    "waypoints": [
      {
        "id": 0,
        "position": 0,
        "isDestination": true,
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
    ],
    "travelModes": [
      {
        "id": 0,
        "name": "string"
      }
    ],
    "criteria": {
      "id": 0,
      "isDriver": true,
      "isPassenger": true,
      "frequency": 0,
      "seats": 0,
      "fromDate": "2019-01-08T14:40:09.733Z",
      "fromTime": "2019-01-08T14:40:09.733Z",
      "toDate": "2019-01-08T14:40:09.733Z",
      "monCheck": true,
      "tueCheck": true,
      "wedCheck": true,
      "thuCheck": true,
      "friCheck": true,
      "satCheck": true,
      "sunCheck": true,
      "monTime": "2019-01-08T14:40:09.733Z",
      "tueTime": "2019-01-08T14:40:09.733Z",
      "wedTime": "2019-01-08T14:40:09.733Z",
      "thuTime": "2019-01-08T14:40:09.733Z",
      "friTime": "2019-01-08T14:40:09.733Z",
      "satTime": "2019-01-08T14:40:09.733Z",
      "sunTime": "2019-01-08T14:40:09.733Z",
      "marginTime": 0,
      "maxDeviationTime": 0,
      "maxDeviationDistance": 0,
      "anyRouteAsPassenger": true,
      "multiTransportMode": true,
      "car": {
        "id": 0,
        "brand": "string",
        "model": "string",
        "color": "string",
        "siv": "string",
        "seats": 0
      },
      "directionDriver": "string",
      "directionPassenger": "string",
      "ptjourney": "string"
    },
    "individualStops": [
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
    ]
  },
  "proposalRequest": {
    "id": 0,
    "type": 0,
    "user": {
      "id": 0,
      "status": 0,
      "givenName": "string",
      "familyName": "string",
      "email": "string",
      "gender": "female",
      "nationality": "string",
      "birthDate": "string",
      "telephone": "string",
      "maxDeviationTime": 0,
      "maxDeviationDistance": 0,
      "anyRouteAsPassenger": true,
      "multiTransportMode": true,
      "addresses": [
        {
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
      ],
      "cars": [
        {
          "id": 0,
          "brand": "string",
          "model": "string",
          "color": "string",
          "siv": "string",
          "seats": 0
        }
      ]
    },
    "waypoints": [
      {
        "id": 0,
        "position": 0,
        "isDestination": true,
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
    ],
    "travelModes": [
      {
        "id": 0,
        "name": "string"
      }
    ],
    "criteria": {
      "id": 0,
      "isDriver": true,
      "isPassenger": true,
      "frequency": 0,
      "seats": 0,
      "fromDate": "2019-01-08T14:40:09.733Z",
      "fromTime": "2019-01-08T14:40:09.733Z",
      "toDate": "2019-01-08T14:40:09.733Z",
      "monCheck": true,
      "tueCheck": true,
      "wedCheck": true,
      "thuCheck": true,
      "friCheck": true,
      "satCheck": true,
      "sunCheck": true,
      "monTime": "2019-01-08T14:40:09.733Z",
      "tueTime": "2019-01-08T14:40:09.733Z",
      "wedTime": "2019-01-08T14:40:09.733Z",
      "thuTime": "2019-01-08T14:40:09.733Z",
      "friTime": "2019-01-08T14:40:09.733Z",
      "satTime": "2019-01-08T14:40:09.733Z",
      "sunTime": "2019-01-08T14:40:09.733Z",
      "marginTime": 0,
      "maxDeviationTime": 0,
      "maxDeviationDistance": 0,
      "anyRouteAsPassenger": true,
      "multiTransportMode": true,
      "car": {
        "id": 0,
        "brand": "string",
        "model": "string",
        "color": "string",
        "siv": "string",
        "seats": 0
      },
      "directionDriver": "string",
      "directionPassenger": "string",
      "ptjourney": "string"
    },
    "individualStops": [
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
    ]
  },
  "criteria": {
    "id": 0,
    "isDriver": true,
    "isPassenger": true,
    "frequency": 0,
    "seats": 0,
    "fromDate": "2019-01-08T14:40:09.733Z",
    "fromTime": "2019-01-08T14:40:09.733Z",
    "toDate": "2019-01-08T14:40:09.733Z",
    "monCheck": true,
    "tueCheck": true,
    "wedCheck": true,
    "thuCheck": true,
    "friCheck": true,
    "satCheck": true,
    "sunCheck": true,
    "monTime": "2019-01-08T14:40:09.733Z",
    "tueTime": "2019-01-08T14:40:09.733Z",
    "wedTime": "2019-01-08T14:40:09.733Z",
    "thuTime": "2019-01-08T14:40:09.733Z",
    "friTime": "2019-01-08T14:40:09.733Z",
    "satTime": "2019-01-08T14:40:09.733Z",
    "sunTime": "2019-01-08T14:40:09.733Z",
    "marginTime": 0,
    "maxDeviationTime": 0,
    "maxDeviationDistance": 0,
    "anyRouteAsPassenger": true,
    "multiTransportMode": true,
    "car": {
      "id": 0,
      "brand": "string",
      "model": "string",
      "color": "string",
      "siv": "string",
      "seats": 0
    },
    "directionDriver": "string",
    "directionPassenger": "string",
    "ptjourney": "string"
  }
}
JSON;

            $deserializer = new Deserializer();
            $Matching = $deserializer->deserialize(Matching::class, json_decode($jsonMatching, true));
            expect($Matching)->toBeAnInstanceOf(Matching::class);
        });
    });
});
