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
use Mobicoop\Bundle\MobicoopBundle\Community\Entity\Community;

/**
 * DeserializerCommunitySpec.php
 * Tests for Deserializer - Community
 * @author Sylvain briat <sylvain.briat@mobicoop.org>
 * Date: 24/06/2019
 * Time: 14:00
 *
 */

describe('deserializeCommunity', function () {
    it('deserialize Community should return a Community object', function () {
        $jsonCommunity = <<<JSON
{
  "id": 0,
  "name": "string",
  "membersHidden": true,
  "proposalsHidden": true,
  "description": "string",
  "fullDescription": "string",
  "createdDate": "2019-06-25T12:18:54.164Z",
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
        "geoJson": "string",
        "name": "string",
        "displayLabel": "string"
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
    "images": [
      {
        "id": 0,
        "name": "string",
        "title": "string",
        "alt": "string",
        "cropX1": 0,
        "cropY1": 0,
        "cropX2": 0,
        "cropY2": 0,
        "fileName": "string",
        "originalName": "string",
        "width": 0,
        "height": 0,
        "size": 0,
        "mimeType": "string",
        "position": 0,
        "eventId": 0,
        "communityId": 0,
        "relayPointId": 0,
        "relayPointTypeId": 0,
        "versions": [
          "string"
        ]
      }
    ],
    "createdDate": "2019-06-25T12:18:54.164Z"
  },
  "images": [
    {
      "id": 0,
      "name": "string",
      "title": "string",
      "alt": "string",
      "cropX1": 0,
      "cropY1": 0,
      "cropX2": 0,
      "cropY2": 0,
      "fileName": "string",
      "originalName": "string",
      "width": 0,
      "height": 0,
      "size": 0,
      "mimeType": "string",
      "position": 0,
      "eventId": 0,
      "communityId": 0,
      "relayPointId": 0,
      "relayPointTypeId": 0,
      "versions": [
        "string"
      ]
    }
  ],
  "proposals": [
    {
      "id": 0,
      "type": 0,
      "comment": "string",
      "createdDate": "2019-06-25T12:18:54.164Z",
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
            "geoJson": "string",
            "name": "string",
            "displayLabel": "string"
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
        "images": [
          {
            "id": 0,
            "name": "string",
            "title": "string",
            "alt": "string",
            "cropX1": 0,
            "cropY1": 0,
            "cropX2": 0,
            "cropY2": 0,
            "fileName": "string",
            "originalName": "string",
            "width": 0,
            "height": 0,
            "size": 0,
            "mimeType": "string",
            "position": 0,
            "eventId": 0,
            "communityId": 0,
            "relayPointId": 0,
            "relayPointTypeId": 0,
            "versions": [
              "string"
            ]
          }
        ],
        "createdDate": "2019-06-25T12:18:54.164Z"
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
            "geoJson": "string",
            "name": "string",
            "displayLabel": "string"
          }
        }
      ],
      "travelModes": [
        {
          "id": 0,
          "name": "string"
        }
      ],
      "communities": [
        null
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
            "fromDate": "2019-06-25T12:18:54.164Z",
            "fromTime": "2019-06-25T12:18:54.164Z",
            "minTime": "2019-06-25T12:18:54.164Z",
            "maxTime": "2019-06-25T12:18:54.164Z",
            "marginDuration": 0,
            "strictDate": true,
            "toDate": "2019-06-25T12:18:54.164Z",
            "monCheck": true,
            "tueCheck": true,
            "wedCheck": true,
            "thuCheck": true,
            "friCheck": true,
            "satCheck": true,
            "sunCheck": true,
            "monTime": "2019-06-25T12:18:54.164Z",
            "monMinTime": "2019-06-25T12:18:54.164Z",
            "monMaxTime": "2019-06-25T12:18:54.164Z",
            "tueTime": "2019-06-25T12:18:54.164Z",
            "tueMinTime": "2019-06-25T12:18:54.164Z",
            "tueMaxTime": "2019-06-25T12:18:54.164Z",
            "wedTime": "2019-06-25T12:18:54.164Z",
            "wedMinTime": "2019-06-25T12:18:54.164Z",
            "wedMaxTime": "2019-06-25T12:18:54.164Z",
            "thuTime": "2019-06-25T12:18:54.164Z",
            "thuMinTime": "2019-06-25T12:18:54.164Z",
            "thuMaxTime": "2019-06-25T12:18:54.164Z",
            "friTime": "2019-06-25T12:18:54.164Z",
            "friMinTime": "2019-06-25T12:18:54.164Z",
            "friMaxTime": "2019-06-25T12:18:54.164Z",
            "satTime": "2019-06-25T12:18:54.164Z",
            "satMinTime": "2019-06-25T12:18:54.164Z",
            "satMaxTime": "2019-06-25T12:18:54.164Z",
            "sunTime": "2019-06-25T12:18:54.164Z",
            "sunMinTime": "2019-06-25T12:18:54.164Z",
            "sunMaxTime": "2019-06-25T12:18:54.164Z",
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
              "geoJsonBbox": "string",
              "bearing": 0,
              "detail": "string",
              "geoJsonDetail": "string",
              "snapped": "string",
              "format": "string",
              "co2": 0,
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
                  "geoJson": "string",
                  "name": "string",
                  "displayLabel": "string"
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
              "geoJsonBbox": "string",
              "bearing": 0,
              "detail": "string",
              "geoJsonDetail": "string",
              "snapped": "string",
              "format": "string",
              "co2": 0,
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
                  "geoJson": "string",
                  "name": "string",
                  "displayLabel": "string"
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
                    "geoJson": "string",
                    "name": "string",
                    "displayLabel": "string"
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
                "images": [
                  {
                    "id": 0,
                    "name": "string",
                    "title": "string",
                    "alt": "string",
                    "cropX1": 0,
                    "cropY1": 0,
                    "cropX2": 0,
                    "cropY2": 0,
                    "fileName": "string",
                    "originalName": "string",
                    "width": 0,
                    "height": 0,
                    "size": 0,
                    "mimeType": "string",
                    "position": 0,
                    "eventId": 0,
                    "communityId": 0,
                    "relayPointId": 0,
                    "relayPointTypeId": 0,
                    "versions": [
                      "string"
                    ]
                  }
                ],
                "createdDate": "2019-06-25T12:18:54.164Z"
              },
              "criteria": {
                "id": 0,
                "driver": true,
                "passenger": true,
                "frequency": 0,
                "seats": 0,
                "fromDate": "2019-06-25T12:18:54.164Z",
                "fromTime": "2019-06-25T12:18:54.164Z",
                "minTime": "2019-06-25T12:18:54.164Z",
                "maxTime": "2019-06-25T12:18:54.164Z",
                "marginDuration": 0,
                "strictDate": true,
                "toDate": "2019-06-25T12:18:54.164Z",
                "monCheck": true,
                "tueCheck": true,
                "wedCheck": true,
                "thuCheck": true,
                "friCheck": true,
                "satCheck": true,
                "sunCheck": true,
                "monTime": "2019-06-25T12:18:54.164Z",
                "monMinTime": "2019-06-25T12:18:54.164Z",
                "monMaxTime": "2019-06-25T12:18:54.164Z",
                "tueTime": "2019-06-25T12:18:54.164Z",
                "tueMinTime": "2019-06-25T12:18:54.164Z",
                "tueMaxTime": "2019-06-25T12:18:54.164Z",
                "wedTime": "2019-06-25T12:18:54.164Z",
                "wedMinTime": "2019-06-25T12:18:54.164Z",
                "wedMaxTime": "2019-06-25T12:18:54.164Z",
                "thuTime": "2019-06-25T12:18:54.164Z",
                "thuMinTime": "2019-06-25T12:18:54.164Z",
                "thuMaxTime": "2019-06-25T12:18:54.164Z",
                "friTime": "2019-06-25T12:18:54.164Z",
                "friMinTime": "2019-06-25T12:18:54.164Z",
                "friMaxTime": "2019-06-25T12:18:54.164Z",
                "satTime": "2019-06-25T12:18:54.164Z",
                "satMinTime": "2019-06-25T12:18:54.164Z",
                "satMaxTime": "2019-06-25T12:18:54.164Z",
                "sunTime": "2019-06-25T12:18:54.164Z",
                "sunMinTime": "2019-06-25T12:18:54.164Z",
                "sunMaxTime": "2019-06-25T12:18:54.164Z",
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
                  "geoJsonBbox": "string",
                  "bearing": 0,
                  "detail": "string",
                  "geoJsonDetail": "string",
                  "snapped": "string",
                  "format": "string",
                  "co2": 0,
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
                      "geoJson": "string",
                      "name": "string",
                      "displayLabel": "string"
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
                  "geoJsonBbox": "string",
                  "bearing": 0,
                  "detail": "string",
                  "geoJsonDetail": "string",
                  "snapped": "string",
                  "format": "string",
                  "co2": 0,
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
                      "geoJson": "string",
                      "name": "string",
                      "displayLabel": "string"
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
                    "geoJson": "string",
                    "name": "string",
                    "displayLabel": "string"
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
                "geoJson": "string",
                "name": "string",
                "displayLabel": "string"
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
            "fromDate": "2019-06-25T12:18:54.164Z",
            "fromTime": "2019-06-25T12:18:54.164Z",
            "minTime": "2019-06-25T12:18:54.164Z",
            "maxTime": "2019-06-25T12:18:54.164Z",
            "marginDuration": 0,
            "strictDate": true,
            "toDate": "2019-06-25T12:18:54.164Z",
            "monCheck": true,
            "tueCheck": true,
            "wedCheck": true,
            "thuCheck": true,
            "friCheck": true,
            "satCheck": true,
            "sunCheck": true,
            "monTime": "2019-06-25T12:18:54.164Z",
            "monMinTime": "2019-06-25T12:18:54.164Z",
            "monMaxTime": "2019-06-25T12:18:54.164Z",
            "tueTime": "2019-06-25T12:18:54.164Z",
            "tueMinTime": "2019-06-25T12:18:54.164Z",
            "tueMaxTime": "2019-06-25T12:18:54.164Z",
            "wedTime": "2019-06-25T12:18:54.164Z",
            "wedMinTime": "2019-06-25T12:18:54.164Z",
            "wedMaxTime": "2019-06-25T12:18:54.164Z",
            "thuTime": "2019-06-25T12:18:54.164Z",
            "thuMinTime": "2019-06-25T12:18:54.164Z",
            "thuMaxTime": "2019-06-25T12:18:54.164Z",
            "friTime": "2019-06-25T12:18:54.164Z",
            "friMinTime": "2019-06-25T12:18:54.164Z",
            "friMaxTime": "2019-06-25T12:18:54.164Z",
            "satTime": "2019-06-25T12:18:54.164Z",
            "satMinTime": "2019-06-25T12:18:54.164Z",
            "satMaxTime": "2019-06-25T12:18:54.164Z",
            "sunTime": "2019-06-25T12:18:54.164Z",
            "sunMinTime": "2019-06-25T12:18:54.164Z",
            "sunMaxTime": "2019-06-25T12:18:54.164Z",
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
              "geoJsonBbox": "string",
              "bearing": 0,
              "detail": "string",
              "geoJsonDetail": "string",
              "snapped": "string",
              "format": "string",
              "co2": 0,
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
                  "geoJson": "string",
                  "name": "string",
                  "displayLabel": "string"
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
              "geoJsonBbox": "string",
              "bearing": 0,
              "detail": "string",
              "geoJsonDetail": "string",
              "snapped": "string",
              "format": "string",
              "co2": 0,
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
                  "geoJson": "string",
                  "name": "string",
                  "displayLabel": "string"
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
                    "geoJson": "string",
                    "name": "string",
                    "displayLabel": "string"
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
                "images": [
                  {
                    "id": 0,
                    "name": "string",
                    "title": "string",
                    "alt": "string",
                    "cropX1": 0,
                    "cropY1": 0,
                    "cropX2": 0,
                    "cropY2": 0,
                    "fileName": "string",
                    "originalName": "string",
                    "width": 0,
                    "height": 0,
                    "size": 0,
                    "mimeType": "string",
                    "position": 0,
                    "eventId": 0,
                    "communityId": 0,
                    "relayPointId": 0,
                    "relayPointTypeId": 0,
                    "versions": [
                      "string"
                    ]
                  }
                ],
                "createdDate": "2019-06-25T12:18:54.164Z"
              },
              "criteria": {
                "id": 0,
                "driver": true,
                "passenger": true,
                "frequency": 0,
                "seats": 0,
                "fromDate": "2019-06-25T12:18:54.164Z",
                "fromTime": "2019-06-25T12:18:54.164Z",
                "minTime": "2019-06-25T12:18:54.164Z",
                "maxTime": "2019-06-25T12:18:54.164Z",
                "marginDuration": 0,
                "strictDate": true,
                "toDate": "2019-06-25T12:18:54.164Z",
                "monCheck": true,
                "tueCheck": true,
                "wedCheck": true,
                "thuCheck": true,
                "friCheck": true,
                "satCheck": true,
                "sunCheck": true,
                "monTime": "2019-06-25T12:18:54.164Z",
                "monMinTime": "2019-06-25T12:18:54.164Z",
                "monMaxTime": "2019-06-25T12:18:54.164Z",
                "tueTime": "2019-06-25T12:18:54.164Z",
                "tueMinTime": "2019-06-25T12:18:54.164Z",
                "tueMaxTime": "2019-06-25T12:18:54.164Z",
                "wedTime": "2019-06-25T12:18:54.164Z",
                "wedMinTime": "2019-06-25T12:18:54.164Z",
                "wedMaxTime": "2019-06-25T12:18:54.164Z",
                "thuTime": "2019-06-25T12:18:54.164Z",
                "thuMinTime": "2019-06-25T12:18:54.164Z",
                "thuMaxTime": "2019-06-25T12:18:54.164Z",
                "friTime": "2019-06-25T12:18:54.164Z",
                "friMinTime": "2019-06-25T12:18:54.164Z",
                "friMaxTime": "2019-06-25T12:18:54.164Z",
                "satTime": "2019-06-25T12:18:54.164Z",
                "satMinTime": "2019-06-25T12:18:54.164Z",
                "satMaxTime": "2019-06-25T12:18:54.164Z",
                "sunTime": "2019-06-25T12:18:54.164Z",
                "sunMinTime": "2019-06-25T12:18:54.164Z",
                "sunMaxTime": "2019-06-25T12:18:54.164Z",
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
                  "geoJsonBbox": "string",
                  "bearing": 0,
                  "detail": "string",
                  "geoJsonDetail": "string",
                  "snapped": "string",
                  "format": "string",
                  "co2": 0,
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
                      "geoJson": "string",
                      "name": "string",
                      "displayLabel": "string"
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
                  "geoJsonBbox": "string",
                  "bearing": 0,
                  "detail": "string",
                  "geoJsonDetail": "string",
                  "snapped": "string",
                  "format": "string",
                  "co2": 0,
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
                      "geoJson": "string",
                      "name": "string",
                      "displayLabel": "string"
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
                    "geoJson": "string",
                    "name": "string",
                    "displayLabel": "string"
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
                "geoJson": "string",
                "name": "string",
                "displayLabel": "string"
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
        "fromDate": "2019-06-25T12:18:54.164Z",
        "fromTime": "2019-06-25T12:18:54.164Z",
        "minTime": "2019-06-25T12:18:54.164Z",
        "maxTime": "2019-06-25T12:18:54.164Z",
        "marginDuration": 0,
        "strictDate": true,
        "toDate": "2019-06-25T12:18:54.164Z",
        "monCheck": true,
        "tueCheck": true,
        "wedCheck": true,
        "thuCheck": true,
        "friCheck": true,
        "satCheck": true,
        "sunCheck": true,
        "monTime": "2019-06-25T12:18:54.164Z",
        "monMinTime": "2019-06-25T12:18:54.164Z",
        "monMaxTime": "2019-06-25T12:18:54.164Z",
        "tueTime": "2019-06-25T12:18:54.164Z",
        "tueMinTime": "2019-06-25T12:18:54.164Z",
        "tueMaxTime": "2019-06-25T12:18:54.164Z",
        "wedTime": "2019-06-25T12:18:54.164Z",
        "wedMinTime": "2019-06-25T12:18:54.164Z",
        "wedMaxTime": "2019-06-25T12:18:54.164Z",
        "thuTime": "2019-06-25T12:18:54.164Z",
        "thuMinTime": "2019-06-25T12:18:54.164Z",
        "thuMaxTime": "2019-06-25T12:18:54.164Z",
        "friTime": "2019-06-25T12:18:54.164Z",
        "friMinTime": "2019-06-25T12:18:54.164Z",
        "friMaxTime": "2019-06-25T12:18:54.164Z",
        "satTime": "2019-06-25T12:18:54.164Z",
        "satMinTime": "2019-06-25T12:18:54.164Z",
        "satMaxTime": "2019-06-25T12:18:54.164Z",
        "sunTime": "2019-06-25T12:18:54.164Z",
        "sunMinTime": "2019-06-25T12:18:54.164Z",
        "sunMaxTime": "2019-06-25T12:18:54.164Z",
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
          "geoJsonBbox": "string",
          "bearing": 0,
          "detail": "string",
          "geoJsonDetail": "string",
          "snapped": "string",
          "format": "string",
          "co2": 0,
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
              "geoJson": "string",
              "name": "string",
              "displayLabel": "string"
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
          "geoJsonBbox": "string",
          "bearing": 0,
          "detail": "string",
          "geoJsonDetail": "string",
          "snapped": "string",
          "format": "string",
          "co2": 0,
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
              "geoJson": "string",
              "name": "string",
              "displayLabel": "string"
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
            "geoJson": "string",
            "name": "string",
            "displayLabel": "string"
          }
        }
      ]
    }
  ],
  "communityUsers": [
    {
      "id": 0,
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
            "geoJson": "string",
            "name": "string",
            "displayLabel": "string"
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
        "images": [
          {
            "id": 0,
            "name": "string",
            "title": "string",
            "alt": "string",
            "cropX1": 0,
            "cropY1": 0,
            "cropX2": 0,
            "cropY2": 0,
            "fileName": "string",
            "originalName": "string",
            "width": 0,
            "height": 0,
            "size": 0,
            "mimeType": "string",
            "position": 0,
            "eventId": 0,
            "communityId": 0,
            "relayPointId": 0,
            "relayPointTypeId": 0,
            "versions": [
              "string"
            ]
          }
        ],
        "createdDate": "2019-06-25T12:18:54.165Z"
      },
      "status": 0,
      "admin": {
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
            "geoJson": "string",
            "name": "string",
            "displayLabel": "string"
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
        "images": [
          {
            "id": 0,
            "name": "string",
            "title": "string",
            "alt": "string",
            "cropX1": 0,
            "cropY1": 0,
            "cropX2": 0,
            "cropY2": 0,
            "fileName": "string",
            "originalName": "string",
            "width": 0,
            "height": 0,
            "size": 0,
            "mimeType": "string",
            "position": 0,
            "eventId": 0,
            "communityId": 0,
            "relayPointId": 0,
            "relayPointTypeId": 0,
            "versions": [
              "string"
            ]
          }
        ],
        "createdDate": "2019-06-25T12:18:54.165Z"
      }
    }
  ],
  "communitySecurities": [
    {
      "id": 0,
      "filename": "string"
    }
  ]
}
JSON;

        $deserializer = new Deserializer();
        $community = $deserializer->deserialize(Community::class, json_decode($jsonCommunity, true));
        expect($community)->toBeAnInstanceOf(Community::class);
    });
});
