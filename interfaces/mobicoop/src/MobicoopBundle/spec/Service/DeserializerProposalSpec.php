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
    describe('deserialize Proposal', function () {
        it('deserialize Proposal should return a Proposal object', function () {
            $jsonProposal = <<<JSON
  {
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
      {
        "id": 0,
        "criteria": {
          "id": 0,
          "driver": true,
          "passenger": true,
          "frequency": 0,
          "seats": 0,
          "fromDate": "2019-04-08T09:27:25.351Z",
          "fromTime": "2019-04-08T09:27:25.351Z",
          "minTime": "2019-04-08T09:27:25.351Z",
          "maxTime": "2019-04-08T09:27:25.351Z",
          "marginDuration": 0,
          "strictDate": true,
          "toDate": "2019-04-08T09:27:25.351Z",
          "monCheck": true,
          "tueCheck": true,
          "wedCheck": true,
          "thuCheck": true,
          "friCheck": true,
          "satCheck": true,
          "sunCheck": true,
          "monTime": "2019-04-08T09:27:25.351Z",
          "monMinTime": "2019-04-08T09:27:25.351Z",
          "monMaxTime": "2019-04-08T09:27:25.351Z",
          "tueTime": "2019-04-08T09:27:25.351Z",
          "tueMinTime": "2019-04-08T09:27:25.351Z",
          "tueMaxTime": "2019-04-08T09:27:25.351Z",
          "wedTime": "2019-04-08T09:27:25.351Z",
          "wedMinTime": "2019-04-08T09:27:25.351Z",
          "wedMaxTime": "2019-04-08T09:27:25.351Z",
          "thuTime": "2019-04-08T09:27:25.351Z",
          "thuMinTime": "2019-04-08T09:27:25.351Z",
          "thuMaxTime": "2019-04-08T09:27:25.351Z",
          "friTime": "2019-04-08T09:27:25.351Z",
          "friMinTime": "2019-04-08T09:27:25.351Z",
          "friMaxTime": "2019-04-08T09:27:25.351Z",
          "satTime": "2019-04-08T09:27:25.351Z",
          "satMinTime": "2019-04-08T09:27:25.351Z",
          "satMaxTime": "2019-04-08T09:27:25.351Z",
          "sunTime": "2019-04-08T09:27:25.351Z",
          "sunMinTime": "2019-04-08T09:27:25.351Z",
          "sunMaxTime": "2019-04-08T09:27:25.351Z",
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
              "fromDate": "2019-04-08T09:27:25.351Z",
              "fromTime": "2019-04-08T09:27:25.351Z",
              "minTime": "2019-04-08T09:27:25.351Z",
              "maxTime": "2019-04-08T09:27:25.351Z",
              "marginDuration": 0,
              "strictDate": true,
              "toDate": "2019-04-08T09:27:25.351Z",
              "monCheck": true,
              "tueCheck": true,
              "wedCheck": true,
              "thuCheck": true,
              "friCheck": true,
              "satCheck": true,
              "sunCheck": true,
              "monTime": "2019-04-08T09:27:25.351Z",
              "monMinTime": "2019-04-08T09:27:25.351Z",
              "monMaxTime": "2019-04-08T09:27:25.351Z",
              "tueTime": "2019-04-08T09:27:25.351Z",
              "tueMinTime": "2019-04-08T09:27:25.351Z",
              "tueMaxTime": "2019-04-08T09:27:25.351Z",
              "wedTime": "2019-04-08T09:27:25.351Z",
              "wedMinTime": "2019-04-08T09:27:25.351Z",
              "wedMaxTime": "2019-04-08T09:27:25.351Z",
              "thuTime": "2019-04-08T09:27:25.351Z",
              "thuMinTime": "2019-04-08T09:27:25.351Z",
              "thuMaxTime": "2019-04-08T09:27:25.351Z",
              "friTime": "2019-04-08T09:27:25.351Z",
              "friMinTime": "2019-04-08T09:27:25.351Z",
              "friMaxTime": "2019-04-08T09:27:25.351Z",
              "satTime": "2019-04-08T09:27:25.351Z",
              "satMinTime": "2019-04-08T09:27:25.351Z",
              "satMaxTime": "2019-04-08T09:27:25.351Z",
              "sunTime": "2019-04-08T09:27:25.351Z",
              "sunMinTime": "2019-04-08T09:27:25.351Z",
              "sunMaxTime": "2019-04-08T09:27:25.351Z",
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
        ],
        "origin": "string"
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
          "fromDate": "2019-04-08T09:27:25.352Z",
          "fromTime": "2019-04-08T09:27:25.352Z",
          "minTime": "2019-04-08T09:27:25.352Z",
          "maxTime": "2019-04-08T09:27:25.352Z",
          "marginDuration": 0,
          "strictDate": true,
          "toDate": "2019-04-08T09:27:25.352Z",
          "monCheck": true,
          "tueCheck": true,
          "wedCheck": true,
          "thuCheck": true,
          "friCheck": true,
          "satCheck": true,
          "sunCheck": true,
          "monTime": "2019-04-08T09:27:25.352Z",
          "monMinTime": "2019-04-08T09:27:25.352Z",
          "monMaxTime": "2019-04-08T09:27:25.352Z",
          "tueTime": "2019-04-08T09:27:25.352Z",
          "tueMinTime": "2019-04-08T09:27:25.352Z",
          "tueMaxTime": "2019-04-08T09:27:25.352Z",
          "wedTime": "2019-04-08T09:27:25.352Z",
          "wedMinTime": "2019-04-08T09:27:25.352Z",
          "wedMaxTime": "2019-04-08T09:27:25.352Z",
          "thuTime": "2019-04-08T09:27:25.352Z",
          "thuMinTime": "2019-04-08T09:27:25.352Z",
          "thuMaxTime": "2019-04-08T09:27:25.352Z",
          "friTime": "2019-04-08T09:27:25.352Z",
          "friMinTime": "2019-04-08T09:27:25.352Z",
          "friMaxTime": "2019-04-08T09:27:25.352Z",
          "satTime": "2019-04-08T09:27:25.352Z",
          "satMinTime": "2019-04-08T09:27:25.352Z",
          "satMaxTime": "2019-04-08T09:27:25.352Z",
          "sunTime": "2019-04-08T09:27:25.352Z",
          "sunMinTime": "2019-04-08T09:27:25.352Z",
          "sunMaxTime": "2019-04-08T09:27:25.352Z",
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
              "fromDate": "2019-04-08T09:27:25.352Z",
              "fromTime": "2019-04-08T09:27:25.352Z",
              "minTime": "2019-04-08T09:27:25.352Z",
              "maxTime": "2019-04-08T09:27:25.352Z",
              "marginDuration": 0,
              "strictDate": true,
              "toDate": "2019-04-08T09:27:25.352Z",
              "monCheck": true,
              "tueCheck": true,
              "wedCheck": true,
              "thuCheck": true,
              "friCheck": true,
              "satCheck": true,
              "sunCheck": true,
              "monTime": "2019-04-08T09:27:25.352Z",
              "monMinTime": "2019-04-08T09:27:25.352Z",
              "monMaxTime": "2019-04-08T09:27:25.352Z",
              "tueTime": "2019-04-08T09:27:25.352Z",
              "tueMinTime": "2019-04-08T09:27:25.352Z",
              "tueMaxTime": "2019-04-08T09:27:25.352Z",
              "wedTime": "2019-04-08T09:27:25.352Z",
              "wedMinTime": "2019-04-08T09:27:25.352Z",
              "wedMaxTime": "2019-04-08T09:27:25.352Z",
              "thuTime": "2019-04-08T09:27:25.352Z",
              "thuMinTime": "2019-04-08T09:27:25.352Z",
              "thuMaxTime": "2019-04-08T09:27:25.352Z",
              "friTime": "2019-04-08T09:27:25.352Z",
              "friMinTime": "2019-04-08T09:27:25.352Z",
              "friMaxTime": "2019-04-08T09:27:25.352Z",
              "satTime": "2019-04-08T09:27:25.352Z",
              "satMinTime": "2019-04-08T09:27:25.352Z",
              "satMaxTime": "2019-04-08T09:27:25.352Z",
              "sunTime": "2019-04-08T09:27:25.352Z",
              "sunMinTime": "2019-04-08T09:27:25.352Z",
              "sunMaxTime": "2019-04-08T09:27:25.352Z",
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
        ],
        "origin": "string"
      }
    ],
    "criteria": {
      "id": 0,
      "driver": true,
      "passenger": true,
      "frequency": 0,
      "seats": 0,
      "fromDate": "2019-04-08T09:27:25.352Z",
      "fromTime": "2019-04-08T09:27:25.352Z",
      "minTime": "2019-04-08T09:27:25.352Z",
      "maxTime": "2019-04-08T09:27:25.352Z",
      "marginDuration": 0,
      "strictDate": true,
      "toDate": "2019-04-08T09:27:25.352Z",
      "monCheck": true,
      "tueCheck": true,
      "wedCheck": true,
      "thuCheck": true,
      "friCheck": true,
      "satCheck": true,
      "sunCheck": true,
      "monTime": "2019-04-08T09:27:25.352Z",
      "monMinTime": "2019-04-08T09:27:25.352Z",
      "monMaxTime": "2019-04-08T09:27:25.352Z",
      "tueTime": "2019-04-08T09:27:25.352Z",
      "tueMinTime": "2019-04-08T09:27:25.352Z",
      "tueMaxTime": "2019-04-08T09:27:25.352Z",
      "wedTime": "2019-04-08T09:27:25.352Z",
      "wedMinTime": "2019-04-08T09:27:25.352Z",
      "wedMaxTime": "2019-04-08T09:27:25.352Z",
      "thuTime": "2019-04-08T09:27:25.352Z",
      "thuMinTime": "2019-04-08T09:27:25.352Z",
      "thuMaxTime": "2019-04-08T09:27:25.352Z",
      "friTime": "2019-04-08T09:27:25.352Z",
      "friMinTime": "2019-04-08T09:27:25.352Z",
      "friMaxTime": "2019-04-08T09:27:25.352Z",
      "satTime": "2019-04-08T09:27:25.352Z",
      "satMinTime": "2019-04-08T09:27:25.352Z",
      "satMaxTime": "2019-04-08T09:27:25.352Z",
      "sunTime": "2019-04-08T09:27:25.352Z",
      "sunMinTime": "2019-04-08T09:27:25.352Z",
      "sunMaxTime": "2019-04-08T09:27:25.352Z",
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
  }

JSON;

            $deserializer = new Deserializer();
            $Proposal = $deserializer->deserialize(Proposal::class, json_decode($jsonProposal, true));
            expect($Proposal)->toBeAnInstanceOf(Proposal::class);
        });
    });
});
