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

use Mobicoop\Bundle\MobicoopBundle\Service\Deserializer;
use Mobicoop\Bundle\MobicoopBundle\Entity\Matching;

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
        it('deserialize Matching should return an Matching object', function () {
            $jsonMatching = <<<JSON
{   
  "@id": 0,
  "id": 0,
  "distanceReal": 0,
  "distanceFly": 0,
  "duration": 0,
  "proposalOffer": {
    "@id": 0,
    "id": 0,
    "proposalType": 0,
    "journeyType": 0,
    "distanceReal": 0,
    "distanceFly": 0,
    "duration": 0,
    "cape": "string",
    "user": {
      "@id": 0,
      "id": 0,
      "givenName": "string",
      "familyName": "string",
      "email": "string",
      "gender": "female",
      "nationality": "string",
      "birthDate": "string",
      "telephone": "string",
      "maxDeviationTime": 0,
      "maxDeviationDistance": 0,
      "userAddresses": [
        {
          "@id": 0,
          "id": 0,
          "name": "string",
          "address": {
            "id": 0,
            "streetAddress": "string",
            "postalCode": "string",
            "addressLocality": "string",
            "addressCountry": "string",
            "latitude": "string",
            "longitude": "string",
            "elevation": 0
          }
        }
      ]
    },
    "points": [
      {
        "@id": 0,
        "id": 0,
        "position": 0,
        "lastPoint": true,
        "distanceNextReal": 0,
        "distanceNextFly": 0,
        "durationNext": 0,
        "pathStart": {
          "@id": 0,
          "id": 0,
          "position": 0,
          "detail": "string",
          "encodeFormat": 0,
          "travelMode": {
            "@id": 0,
            "id": 0,
            "name": "string"
          }
        },
        "pathDestination": {
          "@id": 0,
          "id": 0,
          "position": 0,
          "detail": "string",
          "encodeFormat": 0,
          "travelMode": {
            "@id": 0,
            "id": 0,
            "name": "string"
          }
        },
        "address": {
          "@id": 0,
          "id": 0,
          "streetAddress": "string",
          "postalCode": "string",
          "addressLocality": "string",
          "addressCountry": "string",
          "latitude": "string",
          "longitude": "string",
          "elevation": 0
        },
        "travelMode": {
          "@id": 0,
          "id": 0,
          "name": "string"
        }
      }
    ],
    "travelModes": [
      {
        "@id": 0,
        "id": 0,
        "name": "string"
      }
    ],
    "criteria": {
      "@id": 0,
      "id": 0,
      "frequency": 0,
      "seats": 0,
      "fromDate": "2018-12-24T13:11:36.587Z",
      "fromTime": "2018-12-24T13:11:36.587Z",
      "toDate": "2018-12-24T13:11:36.587Z",
      "monCheck": true,
      "tueCheck": true,
      "wedCheck": true,
      "thuCheck": true,
      "friCheck": true,
      "satCheck": true,
      "sunCheck": true,
      "monTime": "2018-12-24T13:11:36.587Z",
      "tueTime": "2018-12-24T13:11:36.587Z",
      "wedTime": "2018-12-24T13:11:36.587Z",
      "thuTime": "2018-12-24T13:11:36.587Z",
      "friTime": "2018-12-24T13:11:36.587Z",
      "satTime": "2018-12-24T13:11:36.587Z",
      "sunTime": "2018-12-24T13:11:36.587Z",
      "marginTime": 0
    }
  },
  "proposalRequest": {
    "@id": 0,
    "id": 0,
    "proposalType": 0,
    "journeyType": 0,
    "distanceReal": 0,
    "distanceFly": 0,
    "duration": 0,
    "cape": "string",
    "user": {
      "@id": 0,
      "id": 0,
      "givenName": "string",
      "familyName": "string",
      "email": "string",
      "gender": "female",
      "nationality": "string",
      "birthDate": "string",
      "telephone": "string",
      "maxDeviationTime": 0,
      "maxDeviationDistance": 0,
      "userAddresses": [
        {
          "@id": 0,
          "id": 0,
          "name": "string",
          "address": {
            "@id": 0,
            "id": 0,
            "streetAddress": "string",
            "postalCode": "string",
            "addressLocality": "string",
            "addressCountry": "string",
            "latitude": "string",
            "longitude": "string",
            "elevation": 0
          }
        }
      ]
    },
    "points": [
      {
        "@id": 0,
        "id": 0,
        "position": 0,
        "lastPoint": true,
        "distanceNextReal": 0,
        "distanceNextFly": 0,
        "durationNext": 0,
        "pathStart": {
          "@id": 0,
          "id": 0,
          "position": 0,
          "detail": "string",
          "encodeFormat": 0,
          "travelMode": {
            "@id": 0,
            "id": 0,
            "name": "string"
          }
        },
        "pathDestination": {
          "@id": 0,
          "id": 0,
          "position": 0,
          "detail": "string",
          "encodeFormat": 0,
          "travelMode": {
            "@id": 0,
            "id": 0,
            "name": "string"
          }
        },
        "address": {
          "@id": 0,
          "id": 0,
          "streetAddress": "string",
          "postalCode": "string",
          "addressLocality": "string",
          "addressCountry": "string",
          "latitude": "string",
          "longitude": "string",
          "elevation": 0
        },
        "travelMode": {
          "@id": 0,
          "id": 0,
          "name": "string"
        }
      }
    ],
    "travelModes": [
      {
        "@id": 0,
        "id": 0,
        "name": "string"
      }
    ],
    "criteria": {
      "@id": 0,
      "id": 0,
      "frequency": 0,
      "seats": 0,
      "fromDate": "2018-12-24T13:11:36.587Z",
      "fromTime": "2018-12-24T13:11:36.587Z",
      "toDate": "2018-12-24T13:11:36.587Z",
      "monCheck": true,
      "tueCheck": true,
      "wedCheck": true,
      "thuCheck": true,
      "friCheck": true,
      "satCheck": true,
      "sunCheck": true,
      "monTime": "2018-12-24T13:11:36.587Z",
      "tueTime": "2018-12-24T13:11:36.587Z",
      "wedTime": "2018-12-24T13:11:36.587Z",
      "thuTime": "2018-12-24T13:11:36.587Z",
      "friTime": "2018-12-24T13:11:36.587Z",
      "satTime": "2018-12-24T13:11:36.587Z",
      "sunTime": "2018-12-24T13:11:36.587Z",
      "marginTime": 0
    }
  },
  "pointOfferFrom": {
    "@id": 0,
    "id": 0,
    "position": 0,
    "lastPoint": true,
    "distanceNextReal": 0,
    "distanceNextFly": 0,
    "durationNext": 0,
    "pathStart": {
      "@id": 0,
      "id": 0,
      "position": 0,
      "detail": "string",
      "encodeFormat": 0,
      "travelMode": {
        "@id": 0,
        "id": 0,
        "name": "string"
      }
    },
    "pathDestination": {
      "@id": 0,
      "id": 0,
      "position": 0,
      "detail": "string",
      "encodeFormat": 0,
      "travelMode": {
        "@id": 0,
        "id": 0,
        "name": "string"
      }
    },
    "address": {
      "@id": 0,
      "id": 0,
      "streetAddress": "string",
      "postalCode": "string",
      "addressLocality": "string",
      "addressCountry": "string",
      "latitude": "string",
      "longitude": "string",
      "elevation": 0
    },
    "travelMode": {
      "@id": 0,
      "id": 0,
      "name": "string"
    }
  },
  "pointOfferTo": {
    "@id": 0,
    "id": 0,
    "position": 0,
    "lastPoint": true,
    "distanceNextReal": 0,
    "distanceNextFly": 0,
    "durationNext": 0,
    "pathStart": {
      "@id": 0,
      "id": 0,
      "position": 0,
      "detail": "string",
      "encodeFormat": 0,
      "travelMode": {
        "@id": 0,
        "id": 0,
        "name": "string"
      }
    },
    "pathDestination": {
      "@id": 0,
      "id": 0,
      "position": 0,
      "detail": "string",
      "encodeFormat": 0,
      "travelMode": {
        "@id": 0,
        "id": 0,
        "name": "string"
      }
    },
    "address": {
      "@id": 0,
      "id": 0,
      "streetAddress": "string",
      "postalCode": "string",
      "addressLocality": "string",
      "addressCountry": "string",
      "latitude": "string",
      "longitude": "string",
      "elevation": 0
    },
    "travelMode": {
      "@id": 0,
      "id": 0,
      "name": "string"
    }
  },
  "pointRequestFrom": {
    "@id": 0,
    "id": 0,
    "position": 0,
    "lastPoint": true,
    "distanceNextReal": 0,
    "distanceNextFly": 0,
    "durationNext": 0,
    "pathStart": {
      "@id": 0,
      "id": 0,
      "position": 0,
      "detail": "string",
      "encodeFormat": 0,
      "travelMode": {
        "@id": 0,
        "id": 0,
        "name": "string"
      }
    },
    "pathDestination": {
      "@id": 0,
      "id": 0,
      "position": 0,
      "detail": "string",
      "encodeFormat": 0,
      "travelMode": {
        "@id": 0,
        "id": 0,
        "name": "string"
      }
    },
    "address": {
      "@id": 0,
      "id": 0,
      "streetAddress": "string",
      "postalCode": "string",
      "addressLocality": "string",
      "addressCountry": "string",
      "latitude": "string",
      "longitude": "string",
      "elevation": 0
    },
    "travelMode": {
      "@id": 0,
      "id": 0,
      "name": "string"
    }
  },
  "solicitations": [
    {
      "@id": 0,
      "id": 0,
      "status": 0,
      "journeyType": 0,
      "distanceReal": 0,
      "distanceFly": 0,
      "duration": 0,
      "addressFrom": {
        "@id": 0,
        "id": 0,
        "streetAddress": "string",
        "postalCode": "string",
        "addressLocality": "string",
        "addressCountry": "string",
        "latitude": "string",
        "longitude": "string",
        "elevation": 0
      },
      "addressTo": {
        "@id": 0,
        "id": 0,
        "streetAddress": "string",
        "postalCode": "string",
        "addressLocality": "string",
        "addressCountry": "string",
        "latitude": "string",
        "longitude": "string",
        "elevation": 0
      },
      "user": {
        "@id": 0,
        "id": 0,
        "givenName": "string",
        "familyName": "string",
        "email": "string",
        "gender": "female",
        "nationality": "string",
        "birthDate": "string",
        "telephone": "string",
        "maxDeviationTime": 0,
        "maxDeviationDistance": 0,
        "userAddresses": [
          {
            "@id": 0,
            "id": 0,
            "name": "string",
            "address": {
              "@id": 0,
              "id": 0,
              "streetAddress": "string",
              "postalCode": "string",
              "addressLocality": "string",
              "addressCountry": "string",
              "latitude": "string",
              "longitude": "string",
              "elevation": 0
            }
          }
        ]
      },
      "userOffer": {
        "@id": 0,
        "id": 0,
        "givenName": "string",
        "familyName": "string",
        "email": "string",
        "gender": "female",
        "nationality": "string",
        "birthDate": "string",
        "telephone": "string",
        "maxDeviationTime": 0,
        "maxDeviationDistance": 0,
        "userAddresses": [
          {
            "@id": 0,
            "id": 0,
            "name": "string",
            "address": {
              "@id": 0,
              "id": 0,
              "streetAddress": "string",
              "postalCode": "string",
              "addressLocality": "string",
              "addressCountry": "string",
              "latitude": "string",
              "longitude": "string",
              "elevation": 0
            }
          }
        ]
      },
      "userRequest": {
        "@id": 0,
        "id": 0,
        "givenName": "string",
        "familyName": "string",
        "email": "string",
        "gender": "female",
        "nationality": "string",
        "birthDate": "string",
        "telephone": "string",
        "maxDeviationTime": 0,
        "maxDeviationDistance": 0,
        "userAddresses": [
          {
            "@id": 0,
            "id": 0,
            "name": "string",
            "address": {
              "@id": 0,
              "id": 0,
              "streetAddress": "string",
              "postalCode": "string",
              "addressLocality": "string",
              "addressCountry": "string",
              "latitude": "string",
              "longitude": "string",
              "elevation": 0
            }
          }
        ]
      },
      "criteria": {
        "@id": 0,
        "id": 0,
        "frequency": 0,
        "seats": 0,
        "fromDate": "2018-12-24T13:11:36.588Z",
        "fromTime": "2018-12-24T13:11:36.588Z",
        "toDate": "2018-12-24T13:11:36.588Z",
        "monCheck": true,
        "tueCheck": true,
        "wedCheck": true,
        "thuCheck": true,
        "friCheck": true,
        "satCheck": true,
        "sunCheck": true,
        "monTime": "2018-12-24T13:11:36.588Z",
        "tueTime": "2018-12-24T13:11:36.588Z",
        "wedTime": "2018-12-24T13:11:36.588Z",
        "thuTime": "2018-12-24T13:11:36.588Z",
        "friTime": "2018-12-24T13:11:36.588Z",
        "satTime": "2018-12-24T13:11:36.588Z",
        "sunTime": "2018-12-24T13:11:36.588Z",
        "marginTime": 0
      }
    }
  ],
  "criteria": {
    "@id": 0,
    "id": 0,
    "frequency": 0,
    "seats": 0,
    "fromDate": "2018-12-24T13:11:36.588Z",
    "fromTime": "2018-12-24T13:11:36.588Z",
    "toDate": "2018-12-24T13:11:36.588Z",
    "monCheck": true,
    "tueCheck": true,
    "wedCheck": true,
    "thuCheck": true,
    "friCheck": true,
    "satCheck": true,
    "sunCheck": true,
    "monTime": "2018-12-24T13:11:36.588Z",
    "tueTime": "2018-12-24T13:11:36.588Z",
    "wedTime": "2018-12-24T13:11:36.588Z",
    "thuTime": "2018-12-24T13:11:36.588Z",
    "friTime": "2018-12-24T13:11:36.588Z",
    "satTime": "2018-12-24T13:11:36.588Z",
    "sunTime": "2018-12-24T13:11:36.588Z",
    "marginTime": 0
  }
}
JSON;

            $deserializer = new Deserializer();
            $Matching = $deserializer->deserialize(Matching::class, json_decode($jsonMatching, true));
            expect($Matching)->toBeAnInstanceOf(Matching::class);
        });
    });
});
