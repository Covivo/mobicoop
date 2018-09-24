<?php 

namespace App\Spec\Service;

use App\Service\Deserializer;
use App\Entity\User;
use App\Entity\Address;

describe('DeserializerService', function () {
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
            $user = $deserializer->deserialize(User::class,json_decode($jsonUser,true));
            
            expect($user)->toBeAnInstanceOf(User::class);
            expect($user->getGivenName())->toBe('Jean');
            expect($user->getFamilyName())->not->toBe('dupont');
            expect($user->getGender())->toBeNull();
        });
    });
    
    describe('deserializeComplexUser', function () {
        it('deserializeComplexUser should return a complex user object with nested UserAddress', function () {
            
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
  "userAddresses": [
    {
      "@id": "/api/user_addresses/1",
      "@type": "UserAddress",
      "id": 1,
      "name": "domicile",
      "user": "/api/users/1",
      "address": {
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
        "userAddresses": [
          "/api/user_addresses/1"
        ]
      }
    }
  ]
}
JSON;
                
            $deserializer = new Deserializer();
            $user = $deserializer->deserialize(User::class,json_decode($jsonUser,true));
            
            expect($user)->toBeAnInstanceOf(User::class);
            expect($user->getGivenName())->toBe('Jean');
            expect($user->getFamilyName())->toBe('Dupont');
            expect($user->getFamilyName())->not->toBe('jean.dupont@covivo.eu');
            expect($user->getUserAddresses())->toBeA('array');
            expect($user->getUserAddresses()[0]->getAddress())->toBeAnInstanceOf(Address::class);
            expect($user->getUserAddresses()[0]->getAddress()->getPostalCode())->toBe('54000');
            expect($user->getUserAddresses()[0]->getAddress()->getLatitude())->toBeNull();
        });
    });
    
});

?>