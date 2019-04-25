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
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Criteria;

describe('deserializeCriteria', function () {
    describe('deserialize Criteria', function () {
        it('deserializeCriteria should return data given', function () {
            $jsonCriteria = <<<JSON
 {
  "id": 0,
  "driver": true,
  "passenger": true,
  "frequency": 0,
  "seats": 0,
  "fromDate": "2019-04-25T12:47:49.707Z",
  "fromTime": "2019-04-25T12:47:49.707Z",
  "minTime": "2019-04-25T12:47:49.707Z",
  "maxTime": "2019-04-25T12:47:49.707Z",
  "marginDuration": 0,
  "strictDate": true,
  "toDate": "2019-04-25T12:47:49.707Z",
  "monCheck": true,
  "tueCheck": true,
  "wedCheck": true,
  "thuCheck": true,
  "friCheck": true,
  "satCheck": true,
  "sunCheck": true,
  "monTime": "2019-04-25T12:47:49.707Z",
  "monMinTime": "2019-04-25T12:47:49.707Z",
  "monMaxTime": "2019-04-25T12:47:49.707Z",
  "tueTime": "2019-04-25T12:47:49.707Z",
  "tueMinTime": "2019-04-25T12:47:49.707Z",
  "tueMaxTime": "2019-04-25T12:47:49.707Z",
  "wedTime": "2019-04-25T12:47:49.707Z",
  "wedMinTime": "2019-04-25T12:47:49.707Z",
  "wedMaxTime": "2019-04-25T12:47:49.707Z",
  "thuTime": "2019-04-25T12:47:49.707Z",
  "thuMinTime": "2019-04-25T12:47:49.707Z",
  "thuMaxTime": "2019-04-25T12:47:49.707Z",
  "friTime": "2019-04-25T12:47:49.707Z",
  "friMinTime": "2019-04-25T12:47:49.707Z",
  "friMaxTime": "2019-04-25T12:47:49.707Z",
  "satTime": "2019-04-25T12:47:49.707Z",
  "satMinTime": "2019-04-25T12:47:49.707Z",
  "satMaxTime": "2019-04-25T12:47:49.707Z",
  "sunTime": "2019-04-25T12:47:49.707Z",
  "sunMinTime": "2019-04-25T12:47:49.707Z",
  "sunMaxTime": "2019-04-25T12:47:49.707Z",
  "monMarginDuration": 0,
  "tueMarginDuration": 0,
  "wedMarginDuration": 0,
  "thuMarginDuration": 0,
  "friMarginDuration": 0,
  "satMarginDuration": 0,
  "sunMarginDuration": 0,
  "maxDetourDuration": 0,
  "maxDetourDistance": 0,
  "anyRouteAsPassenger": true,
  "multiTransportMode": true,
  "priceKm": "string",
  "car": {
    "id": 0,
    "brand": "string",
    "model": "string",
    "color": "string",
    "siv": "string",
    "seats": 0,
    "priceKm": "string",
    "user": {
      "id": 0,
      "status": 0,
      "givenName": "string",
      "familyName": "string",
      "email": "string",
      "password": "string",
      "gender": 0,
      "nationality": "string",
      "birthDate": "string",
      "telephone": "string",
      "maxDetourDuration": 0,
      "maxDetourDistance": 0,
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
        null
      ]
    }
  },
  "directionDriver": {
    "id": 0,
    "distance": 0,
    "duration": 0,
    "ascend": 0,
    "descend": 0,
    "bboxMinLon": "string",
    "bboxMinLat": "string",
    "bboxMaxLon": "string",
    "bboxMaxLat": "string",
    "detail": "string",
    "snapped": "string",
    "format": "string",
    "points": [
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
    ]
  },
  "directionPassenger": {
    "id": 0,
    "distance": 0,
    "duration": 0,
    "ascend": 0,
    "descend": 0,
    "bboxMinLon": "string",
    "bboxMinLat": "string",
    "bboxMaxLon": "string",
    "bboxMaxLat": "string",
    "detail": "string",
    "snapped": "string",
    "format": "string",
    "points": [
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
    ]
  },
  "ptjourney": "string",
  "proposal": {
    "id": 0,
    "type": 0,
    "comment": "string",
    "createdDate": "2019-04-25T12:47:49.713Z",
    "user": {
      "id": 0,
      "status": 0,
      "givenName": "string",
      "familyName": "string",
      "email": "string",
      "password": "string",
      "gender": 0,
      "nationality": "string",
      "birthDate": "string",
      "telephone": "string",
      "maxDetourDuration": 0,
      "maxDetourDistance": 0,
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
        null
      ]
    },
    "waypoints": [
      {
        "id": 0,
        "position": 0,
        "destination": true,
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
    "matchingOffers": [
      {
        "id": 0,
        "asks": [
          {
            "id": 0,
            "status": 0,
            "type": 0,
            "user": {
              "id": 0,
              "status": 0,
              "givenName": "string",
              "familyName": "string",
              "email": "string",
              "password": "string",
              "gender": 0,
              "nationality": "string",
              "birthDate": "string",
              "telephone": "string",
              "maxDetourDuration": 0,
              "maxDetourDistance": 0,
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
                null
              ]
            },
            "waypoints": [
              {
                "id": 0,
                "position": 0,
                "destination": true,
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
          }
        ],
        "waypoints": [
          {
            "id": 0,
            "position": 0,
            "destination": true,
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
        "filters": [
          "string"
        ]
      }
    ],
    "matchingRequests": [
      {
        "id": 0,
        "asks": [
          {
            "id": 0,
            "status": 0,
            "type": 0,
            "user": {
              "id": 0,
              "status": 0,
              "givenName": "string",
              "familyName": "string",
              "email": "string",
              "password": "string",
              "gender": 0,
              "nationality": "string",
              "birthDate": "string",
              "telephone": "string",
              "maxDetourDuration": 0,
              "maxDetourDistance": 0,
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
                null
              ]
            },
            "waypoints": [
              {
                "id": 0,
                "position": 0,
                "destination": true,
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
          }
        ],
        "waypoints": [
          {
            "id": 0,
            "position": 0,
            "destination": true,
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
        "filters": [
          "string"
        ]
      }
    ],
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
  }
}
JSON;

            $deserializer = new Deserializer();
            $criteria = $deserializer->deserialize(Criteria::class, json_decode($jsonCriteria, true));
            expect($criteria)->toBe($criteria);
        });
    });
});
