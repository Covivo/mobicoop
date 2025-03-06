<?php

namespace App\Rdex\Service;

use App\Rdex\Entity\RdexOperator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class RdexAltJourneyBuilderTest extends TestCase
{
    private const OPERATOR = [
        'name' => 'mobicoop',
        'origin' => 'mobicoop.io',
        'url' => 'https://www.mobicoop.io/',
        'resultRoute' => [
            'fr' => 'covoiturage/rdex/{externalId}',
            'en' => 'carpool/rdex/{externalId}',
        ],
    ];
    private $_rdexAltJourneyBuilder;

    private $_resultJourney = '{"id":"634","proposal_id":"15246","user_id":"13","user_name":"Umberto P.","origin":"Metz","latitude_origin":"49.108385","longitude_origin":"6.194897","destination":"Nancy","latitude_destination":"48.688135","longitude_destination":"6.171263","frequency":"1","from_date":"2025-05-17","to_date":null,"time":"18:00:00","days":null,"created_date":"2025-03-06 01:04:44","updated_date":null,"age":"22","type":"2","role":"1","outward_times":null,"return_times":null,"gender":"2","seats_driver":"3","price_km":"0.059649","distance":"55000","duration":"2479","distance_origin":"0","distance_destination":"0"}';

    private $_rdexResult = '[{
        "journeys": {
            "uuid": "15246",
            "operator": "mobicoop",
            "origin": "mobicoop.io",
            "url": "https://mobicoop.io/covoiturage/Metz/Nancy/1/1/10",
            "driver": {
                "uuid": "13",
                "alias": "Umberto P.",
                "image": null,
                "gender": "male",
                "seats": "3",
                "state": 1
            },
            "passenger": {
                "uuid": "13",
                "alias": "Umberto P.",
                "image": null,
                "gender": "male",
                "persons": 0,
                "state": 0
            },
            "from": {
                "address": null,
                "city": "Metz",
                "postalcode": null,
                "country": null,
                "latitude": "49.108385",
                "longitude": "6.194897"
            },
            "to": {
                "address": null,
                "city": "Nancy",
                "postalcode": null,
                "country": null,
                "latitude": "48.688135",
                "longitude": "6.171263"
            },
            "distance": "55000",
            "duration": "2479",
            "route": null,
            "number_of_waypoints": null,
            "waypoints": {},
            "cost": {
                "variable": "0.059649"
            },
            "details": null,
            "vehicle": null,
            "frequency": "punctual",
            "type": "one-way",
            "real_time": null,
            "stopped": null,
            "days": {
                "monday": 0,
                "tuesday": 0,
                "wednesday": 0,
                "thursday": 0,
                "friday": 0,
                "saturday": 1,
                "sunday": 0
            },
            "outward": {
                "mindate": "2025-05-17",
                "maxdate": "2025-05-17",
                "monday": null,
                "tuesday": null,
                "wednesday": null,
                "thursday": null,
                "friday": null,
                "saturday": {
                    "mintime": "17:45:00",
                    "maxtime": "18:15:00"
                },
                "sunday": null
            },
            "return": null
        }
    }]';

    public function setUp(): void
    {
        $this->_rdexAltJourneyBuilder = new RdexAltJourneyBuilder(json_decode($this->_resultJourney, true), new RdexOperator(self::OPERATOR['name'], self::OPERATOR['origin'], self::OPERATOR['url'], self::OPERATOR['resultRoute']));
    }

    /**
     * @test
     */
    public function testBuildReturnsAnArray()
    {
        $this->assertIsArray($this->_rdexAltJourneyBuilder->build());
    }

    /**
     * @test
     */
    public function testBuildReturnsTheRightResult()
    {
        $this->assertEquals(json_encode($this->_rdexResult), json_encode($this->_rdexAltJourneyBuilder->build()));
    }
}
