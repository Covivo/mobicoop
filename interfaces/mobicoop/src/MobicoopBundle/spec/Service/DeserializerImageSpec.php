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
use Mobicoop\Bundle\MobicoopBundle\Image\Entity\Image;

/**
 * DeserializerImageSpec.php
 * Tests for Deserializer - Image
 * @author Celine Jacquet and Remi Wortemann <celine.jacquet@mobicoop.org>
 * Date: 24/04/2019
 * Time: 14:50
 *
 */

describe('deserializeImage', function () {
    describe('deserialize Image', function () {
        it('deserialize Image should return an Image object', function () {
            $jsonImage = <<<JSON
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
JSON;

            $deserializer = new Deserializer();
            $Image = $deserializer->deserialize(Image::class, json_decode($jsonImage, true));
            expect($Image)->toBeAnInstanceOf(Image::class);
        });
    });
});
 