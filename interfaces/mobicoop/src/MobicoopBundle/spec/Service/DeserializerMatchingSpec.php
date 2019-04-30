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
    "createdDate": "2019-04-29T15:35:42.998Z",
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
      ],
      "masses": [
        {
          "id": 0,
          "status": 0,
          "fileName": "string",
          "originalName": "string",
          "size": 0,
          "mimeType": "string",
          "calculationDate": "2019-04-29T15:35:42.998Z",
          "errors": [
            "string"
          ]
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
      "fromDate": "2019-04-29T15:35:42.998Z",
      "fromTime": "2019-04-29T15:35:42.998Z",
      "minTime": "2019-04-29T15:35:42.998Z",
      "maxTime": "2019-04-29T15:35:42.998Z",
      "marginDuration": 0,
      "strictDate": true,
      "toDate": "2019-04-29T15:35:42.998Z",
      "monCheck": true,
      "tueCheck": true,
      "wedCheck": true,
      "thuCheck": true,
      "friCheck": true,
      "satCheck": true,
      "sunCheck": true,
      "monTime": "2019-04-29T15:35:42.998Z",
      "monMinTime": "2019-04-29T15:35:42.998Z",
      "monMaxTime": "2019-04-29T15:35:42.998Z",
      "tueTime": "2019-04-29T15:35:42.998Z",
      "tueMinTime": "2019-04-29T15:35:42.998Z",
      "tueMaxTime": "2019-04-29T15:35:42.998Z",
      "wedTime": "2019-04-29T15:35:42.998Z",
      "wedMinTime": "2019-04-29T15:35:42.998Z",
      "wedMaxTime": "2019-04-29T15:35:42.998Z",
      "thuTime": "2019-04-29T15:35:42.998Z",
      "thuMinTime": "2019-04-29T15:35:42.998Z",
      "thuMaxTime": "2019-04-29T15:35:42.998Z",
      "friTime": "2019-04-29T15:35:42.998Z",
      "friMinTime": "2019-04-29T15:35:42.998Z",
      "friMaxTime": "2019-04-29T15:35:42.998Z",
      "satTime": "2019-04-29T15:35:42.998Z",
      "satMinTime": "2019-04-29T15:35:42.998Z",
      "satMaxTime": "2019-04-29T15:35:42.998Z",
      "sunTime": "2019-04-29T15:35:42.998Z",
      "sunMinTime": "2019-04-29T15:35:42.998Z",
      "sunMaxTime": "2019-04-29T15:35:42.998Z",
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
    "createdDate": "2019-04-29T15:35:42.998Z",
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
      ],
      "masses": [
        {
          "id": 0,
          "status": 0,
          "fileName": "string",
          "originalName": "string",
          "size": 0,
          "mimeType": "string",
          "calculationDate": "2019-04-29T15:35:42.998Z",
          "errors": [
            "string"
          ]
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
      "fromDate": "2019-04-29T15:35:42.998Z",
      "fromTime": "2019-04-29T15:35:42.998Z",
      "minTime": "2019-04-29T15:35:42.998Z",
      "maxTime": "2019-04-29T15:35:42.998Z",
      "marginDuration": 0,
      "strictDate": true,
      "toDate": "2019-04-29T15:35:42.998Z",
      "monCheck": true,
      "tueCheck": true,
      "wedCheck": true,
      "thuCheck": true,
      "friCheck": true,
      "satCheck": true,
      "sunCheck": true,
      "monTime": "2019-04-29T15:35:42.998Z",
      "monMinTime": "2019-04-29T15:35:42.998Z",
      "monMaxTime": "2019-04-29T15:35:42.998Z",
      "tueTime": "2019-04-29T15:35:42.998Z",
      "tueMinTime": "2019-04-29T15:35:42.998Z",
      "tueMaxTime": "2019-04-29T15:35:42.998Z",
      "wedTime": "2019-04-29T15:35:42.998Z",
      "wedMinTime": "2019-04-29T15:35:42.998Z",
      "wedMaxTime": "2019-04-29T15:35:42.998Z",
      "thuTime": "2019-04-29T15:35:42.998Z",
      "thuMinTime": "2019-04-29T15:35:42.998Z",
      "thuMaxTime": "2019-04-29T15:35:42.998Z",
      "friTime": "2019-04-29T15:35:42.998Z",
      "friMinTime": "2019-04-29T15:35:42.998Z",
      "friMaxTime": "2019-04-29T15:35:42.998Z",
      "satTime": "2019-04-29T15:35:42.998Z",
      "satMinTime": "2019-04-29T15:35:42.998Z",
      "satMaxTime": "2019-04-29T15:35:42.998Z",
      "sunTime": "2019-04-29T15:35:42.998Z",
      "sunMinTime": "2019-04-29T15:35:42.998Z",
      "sunMaxTime": "2019-04-29T15:35:42.998Z",
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
    "fromDate": "2019-04-29T15:35:42.999Z",
    "fromTime": "2019-04-29T15:35:42.999Z",
    "minTime": "2019-04-29T15:35:42.999Z",
    "maxTime": "2019-04-29T15:35:42.999Z",
    "marginDuration": 0,
    "strictDate": true,
    "toDate": "2019-04-29T15:35:42.999Z",
    "monCheck": true,
    "tueCheck": true,
    "wedCheck": true,
    "thuCheck": true,
    "friCheck": true,
    "satCheck": true,
    "sunCheck": true,
    "monTime": "2019-04-29T15:35:42.999Z",
    "monMinTime": "2019-04-29T15:35:42.999Z",
    "monMaxTime": "2019-04-29T15:35:42.999Z",
    "tueTime": "2019-04-29T15:35:42.999Z",
    "tueMinTime": "2019-04-29T15:35:42.999Z",
    "tueMaxTime": "2019-04-29T15:35:42.999Z",
    "wedTime": "2019-04-29T15:35:42.999Z",
    "wedMinTime": "2019-04-29T15:35:42.999Z",
    "wedMaxTime": "2019-04-29T15:35:42.999Z",
    "thuTime": "2019-04-29T15:35:42.999Z",
    "thuMinTime": "2019-04-29T15:35:42.999Z",
    "thuMaxTime": "2019-04-29T15:35:42.999Z",
    "friTime": "2019-04-29T15:35:42.999Z",
    "friMinTime": "2019-04-29T15:35:42.999Z",
    "friMaxTime": "2019-04-29T15:35:42.999Z",
    "satTime": "2019-04-29T15:35:42.999Z",
    "satMinTime": "2019-04-29T15:35:42.999Z",
    "satMaxTime": "2019-04-29T15:35:42.999Z",
    "sunTime": "2019-04-29T15:35:42.999Z",
    "sunMinTime": "2019-04-29T15:35:42.999Z",
    "sunMaxTime": "2019-04-29T15:35:42.999Z",
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
        ],
        "masses": [
          {
            "id": 0,
            "status": 0,
            "fileName": "string",
            "originalName": "string",
            "size": 0,
            "mimeType": "string",
            "calculationDate": "2019-04-29T15:35:42.999Z",
            "errors": [
              "string"
            ]
          }
        ]
      },
      "criteria": {
        "id": 0,
        "driver": true,
        "passenger": true,
        "frequency": 0,
        "seats": 0,
        "fromDate": "2019-04-29T15:35:42.999Z",
        "fromTime": "2019-04-29T15:35:42.999Z",
        "minTime": "2019-04-29T15:35:42.999Z",
        "maxTime": "2019-04-29T15:35:42.999Z",
        "marginDuration": 0,
        "strictDate": true,
        "toDate": "2019-04-29T15:35:42.999Z",
        "monCheck": true,
        "tueCheck": true,
        "wedCheck": true,
        "thuCheck": true,
        "friCheck": true,
        "satCheck": true,
        "sunCheck": true,
        "monTime": "2019-04-29T15:35:42.999Z",
        "monMinTime": "2019-04-29T15:35:42.999Z",
        "monMaxTime": "2019-04-29T15:35:42.999Z",
        "tueTime": "2019-04-29T15:35:42.999Z",
        "tueMinTime": "2019-04-29T15:35:42.999Z",
        "tueMaxTime": "2019-04-29T15:35:42.999Z",
        "wedTime": "2019-04-29T15:35:42.999Z",
        "wedMinTime": "2019-04-29T15:35:42.999Z",
        "wedMaxTime": "2019-04-29T15:35:42.999Z",
        "thuTime": "2019-04-29T15:35:42.999Z",
        "thuMinTime": "2019-04-29T15:35:42.999Z",
        "thuMaxTime": "2019-04-29T15:35:42.999Z",
        "friTime": "2019-04-29T15:35:42.999Z",
        "friMinTime": "2019-04-29T15:35:42.999Z",
        "friMaxTime": "2019-04-29T15:35:42.999Z",
        "satTime": "2019-04-29T15:35:42.999Z",
        "satMinTime": "2019-04-29T15:35:42.999Z",
        "satMaxTime": "2019-04-29T15:35:42.999Z",
        "sunTime": "2019-04-29T15:35:42.999Z",
        "sunMinTime": "2019-04-29T15:35:42.999Z",
        "sunMaxTime": "2019-04-29T15:35:42.999Z",
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
