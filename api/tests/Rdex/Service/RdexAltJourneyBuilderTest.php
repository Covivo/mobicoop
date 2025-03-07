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

    public function setUp(): void {}

    /**
     * @test
     *
     * @dataProvider buildDataProvider
     *
     * @param mixed $query
     */
    public function testBuildReturnsAnArray($query)
    {
        $this->_rdexAltJourneyBuilder = new RdexAltJourneyBuilder(json_decode($query, true), new RdexOperator(self::OPERATOR['name'], self::OPERATOR['origin'], self::OPERATOR['url'], self::OPERATOR['resultRoute']));
        $this->assertIsArray($this->_rdexAltJourneyBuilder->build());
    }

    /**
     * @test
     *
     * @dataProvider buildDataProvider
     *
     * @param mixed $query
     * @param mixed $expected
     */
    public function testBuildPunctualReturnsTheRightResult($query, $expected)
    {
        $this->_rdexAltJourneyBuilder = new RdexAltJourneyBuilder(json_decode($query, true), new RdexOperator(self::OPERATOR['name'], self::OPERATOR['origin'], self::OPERATOR['url'], self::OPERATOR['resultRoute']));
        $this->assertEquals($expected, json_encode($this->_rdexAltJourneyBuilder->build()));
    }

    public function buildDataProvider()
    {
        $resultPunctualJourney = '{"id":"634","proposal_id":"15246","user_id":"13","user_name":"Umberto P.","origin":"Metz","latitude_origin":"49.108385","longitude_origin":"6.194897","destination":"Nancy","latitude_destination":"48.688135","longitude_destination":"6.171263","frequency":"1","from_date":"2025-05-17","to_date":null,"time":"18:00:00","days":null,"created_date":"2025-03-06 01:04:44","updated_date":null,"age":"22","type":"2","role":"1","outward_times":null,"return_times":null,"gender":"2","seats_driver":"3","price_km":"0.059649","distance":"55000","duration":"2479","distance_origin":"0","distance_destination":"0"}';
        $rdexPunctualResult = '{"journeys":{"uuid":"15246","operator":"mobicoop","origin":"mobicoop.io","url":"https:\/\/mobicoop.io\/covoiturage\/Metz\/Nancy\/1\/1\/10","driver":{"uuid":"13","alias":"Umberto P.","image":null,"gender":"male","seats":"3","state":1},"passenger":{"uuid":"13","alias":"Umberto P.","image":null,"gender":"male","persons":0,"state":0},"from":{"address":null,"city":"Metz","postalcode":null,"country":null,"latitude":"49.108385","longitude":"6.194897"},"to":{"address":null,"city":"Nancy","postalcode":null,"country":null,"latitude":"48.688135","longitude":"6.171263"},"distance":"55000","duration":"2479","route":null,"number_of_waypoints":null,"waypoints":{},"cost":{"variable":"0.059649"},"details":null,"vehicle":null,"frequency":"punctual","type":"one-way","real_time":null,"stopped":null,"days":{"monday":0,"tuesday":0,"wednesday":0,"thursday":0,"friday":0,"saturday":1,"sunday":0},"outward":{"mindate":"2025-05-17","maxdate":"2025-05-17","monday":null,"tuesday":null,"wednesday":null,"thursday":null,"friday":null,"saturday":{"mintime":"17:45:00","maxtime":"18:15:00"},"sunday":null},"return":null}}';
        $resultRegularJourney = '{"id":"608","proposal_id":"14931","user_id":"354","user_name":"Do B.","origin":"Metz","latitude_origin":"49.108385","longitude_origin":"6.194897","destination":"Nancy","latitude_destination":"48.688135","longitude_destination":"6.171263","frequency":"2","from_date":"2024-11-26","to_date":"2025-11-26","time":null,"days":"{\"mon\":\"1\",\"tue\":\"1\",\"wed\":\"1\",\"thu\":\"0\",\"fri\":\"0\",\"sat\":\"0\",\"sun\":\"0\"}","created_date":"2025-03-07 01:04:20","updated_date":null,"age":"18","type":"2","role":"1","outward_times":"{\"mon\":\"12:05:00\",\"tue\":\"12:05:00\",\"wed\":\"12:05:00\",\"thu\":null,\"fri\":null,\"sat\":null,\"sun\":null}","return_times":"{\"mon\":\"11:02:00\",\"tue\":\"11:02:00\",\"wed\":\"11:02:00\",\"thu\":null,\"fri\":null,\"sat\":null,\"sun\":null}","gender":"2","seats_driver":"3","price_km":"0.060000","distance":"55000","duration":"2479","distance_origin":"0","distance_destination":"0"}';
        $rdexRegularResult = '{"journeys":{"uuid":"14931","operator":"mobicoop","origin":"mobicoop.io","url":"https:\/\/mobicoop.io\/covoiturage\/Metz\/Nancy\/2\/1\/10","driver":{"uuid":"354","alias":"Do B.","image":null,"gender":"male","seats":"3","state":1},"passenger":{"uuid":"354","alias":"Do B.","image":null,"gender":"male","persons":0,"state":0},"from":{"address":null,"city":"Metz","postalcode":null,"country":null,"latitude":"49.108385","longitude":"6.194897"},"to":{"address":null,"city":"Nancy","postalcode":null,"country":null,"latitude":"48.688135","longitude":"6.171263"},"distance":"55000","duration":"2479","route":null,"number_of_waypoints":null,"waypoints":{},"cost":{"variable":"0.060000"},"details":null,"vehicle":null,"frequency":"regular","type":"round-trip","real_time":null,"stopped":null,"days":{"monday":1,"tuesday":1,"wednesday":1,"thursday":0,"friday":0,"saturday":0,"sunday":0},"outward":{"mindate":"2024-11-26","maxdate":"2025-11-26","monday":{"mintime":"11:50:00","maxtime":"12:20:00"},"tuesday":{"mintime":"11:50:00","maxtime":"12:20:00"},"wednesday":{"mintime":"11:50:00","maxtime":"12:20:00"},"thursday":null,"friday":null,"saturday":null,"sunday":null},"return":null}}';

        return [
            [$resultPunctualJourney, $rdexPunctualResult],
            [$resultRegularJourney, $rdexRegularResult],
        ];
    }
}
