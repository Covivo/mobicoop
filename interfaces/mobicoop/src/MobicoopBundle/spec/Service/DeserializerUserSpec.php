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
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;

/**
 * DeserializerUserSpec.php
 * Tests for Deserializer - User | Address
 * @author Sofiane Belaribi <sofiane.belaribi@mobicoop.org>
 * Date: 24/12/2018
 * Time: 10:34
 *
 */

describe('deserializeSimpleUser', function () {
    it('deserializeSimpleUser should return a simple user object', function () {
        $jsonUser = <<<JSON
{
  "@context": "/api/contexts/User",
  "@id": "/api/users/1",
  "@type": "User",
  "id": 1,
  "givenName": "Jean",
  "familyName": "Dupont"
}
JSON;

        $deserializer = new Deserializer();
        $user = $deserializer->deserialize(User::class, json_decode($jsonUser, true));

        expect($user)->toBeAnInstanceOf(User::class);
        expect($user->getGivenName())->toBe('Jean');
        expect($user->getFamilyName())->not->toBe('dupont');
        expect($user->getGender())->toBeNull();
    });
});

describe('deserializeComplexUser', function () {
    it('deserializeComplexUser should return a complex user object with nested address', function () {
        $jsonUser = <<<JSON
{
  "@context": "/api/contexts/User",
  "@id": "/api/users/1",
  "@type": "User",
  "id": 1,
  "givenName": "Jean",
  "familyName": "Dupont",
  "email": "Jean.Dupont@covivo.eu",
  "gender": "male",
  "nationality": "Française",
  "birthDate": "1976-11-26T00:00:00+01:00",
  "telephone": null,
  "maxDeviationTime": 300,
  "maxDeviationDistance": 5000,
  "addresses": [
    {
      "@id": "/api/addresses/1",
      "@type": "Address",
      "id": 1,
      "streetAddress": "5 rue de la monnaie",
      "postalCode": "54000",
      "addressLocality": "Nancy",
      "addressCountry": "France",
      "latitude": null,
      "longitude": null,
      "elevation": null,
      "name": "domicile",
      "user": "/api/users/1"
    }
  ]
}
JSON;

        $deserializer = new Deserializer();
        $user = $deserializer->deserialize(User::class, json_decode($jsonUser, true));

        expect($user)->toBeAnInstanceOf(User::class);
        expect($user->getGivenName())->toBe('Jean');
        expect($user->getFamilyName())->toBe('Dupont');
        expect($user->getFamilyName())->not->toBe('jean.dupont@covivo.eu');
        expect($user->getAddresses())->toBeA('object');
        expect($user->getAddresses()[0])->toBeAnInstanceOf(Address::class);
        expect($user->getAddresses()[0]->getPostalCode())->toBe('54000');
        expect($user->getAddresses()[0]->getLatitude())->toBeNull();
    });
});

describe('deserializeAddress', function () {
    describe('deserialize Address', function () {
        it('deserializeAddress should return an Address object', function () {
            $jsonAddress = <<<JSON
{
  "id": 0,
  "streetAddress": "string",
  "postalCode": "string",
  "addressLocality": "string",
  "addressCountry": "string",
  "latitude": "string",
  "longitude": "string",
  "elevation": 0
}
JSON;

            $deserializer = new Deserializer();
            $Address = $deserializer->deserialize(Address::class, json_decode($jsonAddress, true));
            expect($Address)->toBeAnInstanceOf(Address::class);
        });
    });
});
