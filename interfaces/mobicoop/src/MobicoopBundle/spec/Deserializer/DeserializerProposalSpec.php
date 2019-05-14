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
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;

/**
 * DeserializerProposalSpec.php
 * Tests for Deserializer - Proposal
 * @author Sofiane Belaribi <sofiane.belaribi@mobicoop.org>
 * Date: 24/12/2018
 * Time: 14:19
 *
 */

describe('deserializeProposal', function () {
    it('deserialize Proposal should return a Proposal object', function () {
        $jsonProposal = <<<JSON
{
  "id": 0,
  "type": 0,
  "comment": "string",
  "createdDate": "2019-04-29T15:36:10.623Z",
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
        "calculationDate": "2019-04-29T15:36:10.623Z",
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
    {
      "id": 0,
      "criteria": {
        "id": 0,
        "driver": true,
        "passenger": true,
        "frequency": 0,
        "seats": 0,
        "fromDate": "2019-04-29T15:36:10.623Z",
        "fromTime": "2019-04-29T15:36:10.623Z",
        "minTime": "2019-04-29T15:36:10.623Z",
        "maxTime": "2019-04-29T15:36:10.623Z",
        "marginDuration": 0,
        "strictDate": true,
        "toDate": "2019-04-29T15:36:10.623Z",
        "monCheck": true,
        "tueCheck": true,
        "wedCheck": true,
        "thuCheck": true,
        "friCheck": true,
        "satCheck": true,
        "sunCheck": true,
        "monTime": "2019-04-29T15:36:10.623Z",
        "monMinTime": "2019-04-29T15:36:10.623Z",
        "monMaxTime": "2019-04-29T15:36:10.623Z",
        "tueTime": "2019-04-29T15:36:10.623Z",
        "tueMinTime": "2019-04-29T15:36:10.623Z",
        "tueMaxTime": "2019-04-29T15:36:10.623Z",
        "wedTime": "2019-04-29T15:36:10.623Z",
        "wedMinTime": "2019-04-29T15:36:10.623Z",
        "wedMaxTime": "2019-04-29T15:36:10.623Z",
        "thuTime": "2019-04-29T15:36:10.623Z",
        "thuMinTime": "2019-04-29T15:36:10.623Z",
        "thuMaxTime": "2019-04-29T15:36:10.623Z",
        "friTime": "2019-04-29T15:36:10.623Z",
        "friMinTime": "2019-04-29T15:36:10.623Z",
        "friMaxTime": "2019-04-29T15:36:10.623Z",
        "satTime": "2019-04-29T15:36:10.623Z",
        "satMinTime": "2019-04-29T15:36:10.623Z",
        "satMaxTime": "2019-04-29T15:36:10.623Z",
        "sunTime": "2019-04-29T15:36:10.623Z",
        "sunMinTime": "2019-04-29T15:36:10.623Z",
        "sunMaxTime": "2019-04-29T15:36:10.623Z",
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
                "calculationDate": "2019-04-29T15:36:10.624Z",
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
            "fromDate": "2019-04-29T15:36:10.624Z",
            "fromTime": "2019-04-29T15:36:10.624Z",
            "minTime": "2019-04-29T15:36:10.624Z",
            "maxTime": "2019-04-29T15:36:10.624Z",
            "marginDuration": 0,
            "strictDate": true,
            "toDate": "2019-04-29T15:36:10.624Z",
            "monCheck": true,
            "tueCheck": true,
            "wedCheck": true,
            "thuCheck": true,
            "friCheck": true,
            "satCheck": true,
            "sunCheck": true,
            "monTime": "2019-04-29T15:36:10.624Z",
            "monMinTime": "2019-04-29T15:36:10.624Z",
            "monMaxTime": "2019-04-29T15:36:10.624Z",
            "tueTime": "2019-04-29T15:36:10.624Z",
            "tueMinTime": "2019-04-29T15:36:10.624Z",
            "tueMaxTime": "2019-04-29T15:36:10.624Z",
            "wedTime": "2019-04-29T15:36:10.624Z",
            "wedMinTime": "2019-04-29T15:36:10.624Z",
            "wedMaxTime": "2019-04-29T15:36:10.624Z",
            "thuTime": "2019-04-29T15:36:10.624Z",
            "thuMinTime": "2019-04-29T15:36:10.624Z",
            "thuMaxTime": "2019-04-29T15:36:10.624Z",
            "friTime": "2019-04-29T15:36:10.624Z",
            "friMinTime": "2019-04-29T15:36:10.624Z",
            "friMaxTime": "2019-04-29T15:36:10.624Z",
            "satTime": "2019-04-29T15:36:10.624Z",
            "satMinTime": "2019-04-29T15:36:10.624Z",
            "satMaxTime": "2019-04-29T15:36:10.624Z",
            "sunTime": "2019-04-29T15:36:10.624Z",
            "sunMinTime": "2019-04-29T15:36:10.624Z",
            "sunMaxTime": "2019-04-29T15:36:10.624Z",
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
  ],
  "matchingRequests": [
    {
      "id": 0,
      "criteria": {
        "id": 0,
        "driver": true,
        "passenger": true,
        "frequency": 0,
        "seats": 0,
        "fromDate": "2019-04-29T15:36:10.624Z",
        "fromTime": "2019-04-29T15:36:10.624Z",
        "minTime": "2019-04-29T15:36:10.624Z",
        "maxTime": "2019-04-29T15:36:10.624Z",
        "marginDuration": 0,
        "strictDate": true,
        "toDate": "2019-04-29T15:36:10.624Z",
        "monCheck": true,
        "tueCheck": true,
        "wedCheck": true,
        "thuCheck": true,
        "friCheck": true,
        "satCheck": true,
        "sunCheck": true,
        "monTime": "2019-04-29T15:36:10.624Z",
        "monMinTime": "2019-04-29T15:36:10.624Z",
        "monMaxTime": "2019-04-29T15:36:10.624Z",
        "tueTime": "2019-04-29T15:36:10.624Z",
        "tueMinTime": "2019-04-29T15:36:10.624Z",
        "tueMaxTime": "2019-04-29T15:36:10.624Z",
        "wedTime": "2019-04-29T15:36:10.624Z",
        "wedMinTime": "2019-04-29T15:36:10.624Z",
        "wedMaxTime": "2019-04-29T15:36:10.624Z",
        "thuTime": "2019-04-29T15:36:10.624Z",
        "thuMinTime": "2019-04-29T15:36:10.624Z",
        "thuMaxTime": "2019-04-29T15:36:10.624Z",
        "friTime": "2019-04-29T15:36:10.624Z",
        "friMinTime": "2019-04-29T15:36:10.624Z",
        "friMaxTime": "2019-04-29T15:36:10.624Z",
        "satTime": "2019-04-29T15:36:10.624Z",
        "satMinTime": "2019-04-29T15:36:10.624Z",
        "satMaxTime": "2019-04-29T15:36:10.624Z",
        "sunTime": "2019-04-29T15:36:10.624Z",
        "sunMinTime": "2019-04-29T15:36:10.624Z",
        "sunMaxTime": "2019-04-29T15:36:10.624Z",
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
                "calculationDate": "2019-04-29T15:36:10.624Z",
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
            "fromDate": "2019-04-29T15:36:10.624Z",
            "fromTime": "2019-04-29T15:36:10.624Z",
            "minTime": "2019-04-29T15:36:10.624Z",
            "maxTime": "2019-04-29T15:36:10.624Z",
            "marginDuration": 0,
            "strictDate": true,
            "toDate": "2019-04-29T15:36:10.624Z",
            "monCheck": true,
            "tueCheck": true,
            "wedCheck": true,
            "thuCheck": true,
            "friCheck": true,
            "satCheck": true,
            "sunCheck": true,
            "monTime": "2019-04-29T15:36:10.624Z",
            "monMinTime": "2019-04-29T15:36:10.624Z",
            "monMaxTime": "2019-04-29T15:36:10.624Z",
            "tueTime": "2019-04-29T15:36:10.624Z",
            "tueMinTime": "2019-04-29T15:36:10.624Z",
            "tueMaxTime": "2019-04-29T15:36:10.624Z",
            "wedTime": "2019-04-29T15:36:10.624Z",
            "wedMinTime": "2019-04-29T15:36:10.624Z",
            "wedMaxTime": "2019-04-29T15:36:10.624Z",
            "thuTime": "2019-04-29T15:36:10.624Z",
            "thuMinTime": "2019-04-29T15:36:10.624Z",
            "thuMaxTime": "2019-04-29T15:36:10.624Z",
            "friTime": "2019-04-29T15:36:10.624Z",
            "friMinTime": "2019-04-29T15:36:10.624Z",
            "friMaxTime": "2019-04-29T15:36:10.624Z",
            "satTime": "2019-04-29T15:36:10.624Z",
            "satMinTime": "2019-04-29T15:36:10.624Z",
            "satMaxTime": "2019-04-29T15:36:10.624Z",
            "sunTime": "2019-04-29T15:36:10.624Z",
            "sunMinTime": "2019-04-29T15:36:10.624Z",
            "sunMaxTime": "2019-04-29T15:36:10.624Z",
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
  ],
  "criteria": {
    "id": 0,
    "driver": true,
    "passenger": true,
    "frequency": 0,
    "seats": 0,
    "fromDate": "2019-04-29T15:36:10.624Z",
    "fromTime": "2019-04-29T15:36:10.624Z",
    "minTime": "2019-04-29T15:36:10.624Z",
    "maxTime": "2019-04-29T15:36:10.624Z",
    "marginDuration": 0,
    "strictDate": true,
    "toDate": "2019-04-29T15:36:10.624Z",
    "monCheck": true,
    "tueCheck": true,
    "wedCheck": true,
    "thuCheck": true,
    "friCheck": true,
    "satCheck": true,
    "sunCheck": true,
    "monTime": "2019-04-29T15:36:10.624Z",
    "monMinTime": "2019-04-29T15:36:10.624Z",
    "monMaxTime": "2019-04-29T15:36:10.624Z",
    "tueTime": "2019-04-29T15:36:10.624Z",
    "tueMinTime": "2019-04-29T15:36:10.624Z",
    "tueMaxTime": "2019-04-29T15:36:10.624Z",
    "wedTime": "2019-04-29T15:36:10.624Z",
    "wedMinTime": "2019-04-29T15:36:10.624Z",
    "wedMaxTime": "2019-04-29T15:36:10.624Z",
    "thuTime": "2019-04-29T15:36:10.624Z",
    "thuMinTime": "2019-04-29T15:36:10.624Z",
    "thuMaxTime": "2019-04-29T15:36:10.624Z",
    "friTime": "2019-04-29T15:36:10.624Z",
    "friMinTime": "2019-04-29T15:36:10.624Z",
    "friMaxTime": "2019-04-29T15:36:10.624Z",
    "satTime": "2019-04-29T15:36:10.624Z",
    "satMinTime": "2019-04-29T15:36:10.624Z",
    "satMaxTime": "2019-04-29T15:36:10.624Z",
    "sunTime": "2019-04-29T15:36:10.624Z",
    "sunMinTime": "2019-04-29T15:36:10.624Z",
    "sunMaxTime": "2019-04-29T15:36:10.624Z",
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
}
JSON;

        $deserializer = new Deserializer();
        $Proposal = $deserializer->deserialize(Proposal::class, json_decode($jsonProposal, true));
        expect($Proposal)->toBeAnInstanceOf(Proposal::class);
    });
});
