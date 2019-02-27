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
      "fromDate": "2019-01-08T14:41:59.572Z",
      "fromTime": "2019-01-08T14:41:59.572Z",
      "toDate": "2019-01-08T14:41:59.572Z",
      "monCheck": true,
      "tueCheck": true,
      "wedCheck": true,
      "thuCheck": true,
      "friCheck": true,
      "satCheck": true,
      "sunCheck": true,
      "monTime": "2019-01-08T14:41:59.572Z",
      "tueTime": "2019-01-08T14:41:59.572Z",
      "wedTime": "2019-01-08T14:41:59.572Z",
      "thuTime": "2019-01-08T14:41:59.572Z",
      "friTime": "2019-01-08T14:41:59.572Z",
      "satTime": "2019-01-08T14:41:59.572Z",
      "sunTime": "2019-01-08T14:41:59.572Z",
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
  }
JSON;

            $deserializer = new Deserializer();
            $Proposal = $deserializer->deserialize(Proposal::class, json_decode($jsonProposal, true));
            expect($Proposal)->toBeAnInstanceOf(Proposal::class);
        });
    });
});
