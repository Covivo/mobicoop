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
      "comment": "string",
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
          {
            "id": 0,
            "brand": "string",
            "model": "string",
            "color": "string",
            "siv": "string",
            "seats": 0,
            "priceKm": "string"
          }
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
        null
      ],
      "matchingRequests": [
        null
      ],
      "criteria": {
        "id": 0,
        "driver": true,
        "passenger": true,
        "frequency": 0,
        "seats": 0,
        "fromDate": "2019-04-10T08:52:04.261Z",
        "fromTime": "2019-04-10T08:52:04.261Z",
        "minTime": "2019-04-10T08:52:04.261Z",
        "maxTime": "2019-04-10T08:52:04.261Z",
        "marginDuration": 0,
        "strictDate": true,
        "toDate": "2019-04-10T08:52:04.261Z",
        "monCheck": true,
        "tueCheck": true,
        "wedCheck": true,
        "thuCheck": true,
        "friCheck": true,
        "satCheck": true,
        "sunCheck": true,
        "monTime": "2019-04-10T08:52:04.261Z",
        "monMinTime": "2019-04-10T08:52:04.261Z",
        "monMaxTime": "2019-04-10T08:52:04.261Z",
        "tueTime": "2019-04-10T08:52:04.261Z",
        "tueMinTime": "2019-04-10T08:52:04.261Z",
        "tueMaxTime": "2019-04-10T08:52:04.261Z",
        "wedTime": "2019-04-10T08:52:04.261Z",
        "wedMinTime": "2019-04-10T08:52:04.261Z",
        "wedMaxTime": "2019-04-10T08:52:04.261Z",
        "thuTime": "2019-04-10T08:52:04.261Z",
        "thuMinTime": "2019-04-10T08:52:04.261Z",
        "thuMaxTime": "2019-04-10T08:52:04.261Z",
        "friTime": "2019-04-10T08:52:04.261Z",
        "friMinTime": "2019-04-10T08:52:04.261Z",
        "friMaxTime": "2019-04-10T08:52:04.261Z",
        "satTime": "2019-04-10T08:52:04.261Z",
        "satMinTime": "2019-04-10T08:52:04.261Z",
        "satMaxTime": "2019-04-10T08:52:04.261Z",
        "sunTime": "2019-04-10T08:52:04.261Z",
        "sunMinTime": "2019-04-10T08:52:04.261Z",
        "sunMaxTime": "2019-04-10T08:52:04.261Z",
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
          "priceKm": "string"
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
      "comment": "string",
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
          {
            "id": 0,
            "brand": "string",
            "model": "string",
            "color": "string",
            "siv": "string",
            "seats": 0,
            "priceKm": "string"
          }
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
        null
      ],
      "matchingRequests": [
        null
      ],
      "criteria": {
        "id": 0,
        "driver": true,
        "passenger": true,
        "frequency": 0,
        "seats": 0,
        "fromDate": "2019-04-10T08:52:04.261Z",
        "fromTime": "2019-04-10T08:52:04.261Z",
        "minTime": "2019-04-10T08:52:04.261Z",
        "maxTime": "2019-04-10T08:52:04.261Z",
        "marginDuration": 0,
        "strictDate": true,
        "toDate": "2019-04-10T08:52:04.261Z",
        "monCheck": true,
        "tueCheck": true,
        "wedCheck": true,
        "thuCheck": true,
        "friCheck": true,
        "satCheck": true,
        "sunCheck": true,
        "monTime": "2019-04-10T08:52:04.261Z",
        "monMinTime": "2019-04-10T08:52:04.261Z",
        "monMaxTime": "2019-04-10T08:52:04.261Z",
        "tueTime": "2019-04-10T08:52:04.261Z",
        "tueMinTime": "2019-04-10T08:52:04.261Z",
        "tueMaxTime": "2019-04-10T08:52:04.261Z",
        "wedTime": "2019-04-10T08:52:04.261Z",
        "wedMinTime": "2019-04-10T08:52:04.261Z",
        "wedMaxTime": "2019-04-10T08:52:04.261Z",
        "thuTime": "2019-04-10T08:52:04.261Z",
        "thuMinTime": "2019-04-10T08:52:04.261Z",
        "thuMaxTime": "2019-04-10T08:52:04.261Z",
        "friTime": "2019-04-10T08:52:04.261Z",
        "friMinTime": "2019-04-10T08:52:04.261Z",
        "friMaxTime": "2019-04-10T08:52:04.261Z",
        "satTime": "2019-04-10T08:52:04.261Z",
        "satMinTime": "2019-04-10T08:52:04.261Z",
        "satMaxTime": "2019-04-10T08:52:04.261Z",
        "sunTime": "2019-04-10T08:52:04.261Z",
        "sunMinTime": "2019-04-10T08:52:04.261Z",
        "sunMaxTime": "2019-04-10T08:52:04.261Z",
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
          "priceKm": "string"
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
      "driver": true,
      "passenger": true,
      "frequency": 0,
      "seats": 0,
      "fromDate": "2019-04-10T08:52:04.262Z",
      "fromTime": "2019-04-10T08:52:04.262Z",
      "minTime": "2019-04-10T08:52:04.262Z",
      "maxTime": "2019-04-10T08:52:04.262Z",
      "marginDuration": 0,
      "strictDate": true,
      "toDate": "2019-04-10T08:52:04.262Z",
      "monCheck": true,
      "tueCheck": true,
      "wedCheck": true,
      "thuCheck": true,
      "friCheck": true,
      "satCheck": true,
      "sunCheck": true,
      "monTime": "2019-04-10T08:52:04.262Z",
      "monMinTime": "2019-04-10T08:52:04.262Z",
      "monMaxTime": "2019-04-10T08:52:04.262Z",
      "tueTime": "2019-04-10T08:52:04.262Z",
      "tueMinTime": "2019-04-10T08:52:04.262Z",
      "tueMaxTime": "2019-04-10T08:52:04.262Z",
      "wedTime": "2019-04-10T08:52:04.262Z",
      "wedMinTime": "2019-04-10T08:52:04.262Z",
      "wedMaxTime": "2019-04-10T08:52:04.262Z",
      "thuTime": "2019-04-10T08:52:04.262Z",
      "thuMinTime": "2019-04-10T08:52:04.262Z",
      "thuMaxTime": "2019-04-10T08:52:04.262Z",
      "friTime": "2019-04-10T08:52:04.262Z",
      "friMinTime": "2019-04-10T08:52:04.262Z",
      "friMaxTime": "2019-04-10T08:52:04.262Z",
      "satTime": "2019-04-10T08:52:04.262Z",
      "satMinTime": "2019-04-10T08:52:04.262Z",
      "satMaxTime": "2019-04-10T08:52:04.262Z",
      "sunTime": "2019-04-10T08:52:04.262Z",
      "sunMinTime": "2019-04-10T08:52:04.262Z",
      "sunMaxTime": "2019-04-10T08:52:04.262Z",
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
        "priceKm": "string"
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
      "ptjourney": "string"
    },
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
            {
              "id": 0,
              "brand": "string",
              "model": "string",
              "color": "string",
              "siv": "string",
              "seats": 0,
              "priceKm": "string"
            }
          ]
        },
        "criteria": {
          "id": 0,
          "driver": true,
          "passenger": true,
          "frequency": 0,
          "seats": 0,
          "fromDate": "2019-04-10T08:52:04.262Z",
          "fromTime": "2019-04-10T08:52:04.262Z",
          "minTime": "2019-04-10T08:52:04.262Z",
          "maxTime": "2019-04-10T08:52:04.262Z",
          "marginDuration": 0,
          "strictDate": true,
          "toDate": "2019-04-10T08:52:04.262Z",
          "monCheck": true,
          "tueCheck": true,
          "wedCheck": true,
          "thuCheck": true,
          "friCheck": true,
          "satCheck": true,
          "sunCheck": true,
          "monTime": "2019-04-10T08:52:04.262Z",
          "monMinTime": "2019-04-10T08:52:04.262Z",
          "monMaxTime": "2019-04-10T08:52:04.262Z",
          "tueTime": "2019-04-10T08:52:04.262Z",
          "tueMinTime": "2019-04-10T08:52:04.262Z",
          "tueMaxTime": "2019-04-10T08:52:04.262Z",
          "wedTime": "2019-04-10T08:52:04.262Z",
          "wedMinTime": "2019-04-10T08:52:04.262Z",
          "wedMaxTime": "2019-04-10T08:52:04.262Z",
          "thuTime": "2019-04-10T08:52:04.262Z",
          "thuMinTime": "2019-04-10T08:52:04.262Z",
          "thuMaxTime": "2019-04-10T08:52:04.262Z",
          "friTime": "2019-04-10T08:52:04.262Z",
          "friMinTime": "2019-04-10T08:52:04.262Z",
          "friMaxTime": "2019-04-10T08:52:04.262Z",
          "satTime": "2019-04-10T08:52:04.262Z",
          "satMinTime": "2019-04-10T08:52:04.262Z",
          "satMaxTime": "2019-04-10T08:52:04.262Z",
          "sunTime": "2019-04-10T08:52:04.262Z",
          "sunMinTime": "2019-04-10T08:52:04.262Z",
          "sunMaxTime": "2019-04-10T08:52:04.262Z",
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
            "priceKm": "string"
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
          "ptjourney": "string"
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
JSON;

            $deserializer = new Deserializer();
            $Matching = $deserializer->deserialize(Matching::class, json_decode($jsonMatching, true));
            expect($Matching)->toBeAnInstanceOf(Matching::class);
        });
    });
});
