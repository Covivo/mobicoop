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
use Mobicoop\Bundle\MobicoopBundle\Event\Entity\Event;

/**
 * DeserializerEventSpec.php
 * Tests for Deserializer - Event
 * @author Celine Jacquet and Remi Wortemann <celine.jacquet@mobicoop.org>
 * Date: 24/04/2019
 * Time: 14:50
 *
 */

describe('deserializeEvent', function () {
  it('deserialize Event should return an Event object', function () {
    $jsonEvent = <<<JSON
{
  "id": 0,
  "name": "string",
  "status": 0,
  "description": "string",
  "fullDescription": "string",
  "fromDate": "2019-04-29T15:34:47.496Z",
  "toDate": "2019-04-29T15:34:47.496Z",
  "useTime": true,
  "url": "string",
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
      "versions": [
        "string"
      ]
    }
  ]
}
JSON;

    $deserializer = new Deserializer();
    $Event = $deserializer->deserialize(Event::class, json_decode($jsonEvent, true));
    expect($Event)->toBeAnInstanceOf(Event::class);
  });
});
