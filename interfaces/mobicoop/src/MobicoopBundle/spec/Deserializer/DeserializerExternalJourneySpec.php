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
use Mobicoop\Bundle\MobicoopBundle\ExternalJourney\Entity\ExternalJourney;

/**
 * DeserializerAdressSpec.php
 * Tests for Deserializer - ExternalJourney
 * @author Sofiane Belaribi <sofiane.belaribi@mobicoop.org>
 * Date: 24/12/2018
 * Time: 10:34
 *
 */
describe('deserializeExternalJourney', function () {
  it('deserializeExternalJourney should return data given', function () {
    $jsonExternalJourney = <<<JSON
  {
    "@id": 0
  }
JSON;

    $deserializer = new Deserializer();
    $externalJourney = $deserializer->deserialize(ExternalJourney::class, json_decode($jsonExternalJourney, true));
    expect($externalJourney)->toBe($externalJourney);
  });
});
