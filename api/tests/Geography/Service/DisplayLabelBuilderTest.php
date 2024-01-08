<?php

namespace App\Geography\Service;

use App\Carpool\Entity\Waypoint;
use App\Geography\Entity\Address;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class DisplayLabelBuilderTest extends TestCase
{
    private $_displayLabelBuilder;
    private $_displayLabelBuilderEmptyOrder;

    public function setUp(): void
    {
        $carpoolDisplayFieldsOrder = ['addressLocality'];
        $carpoolDisplayFieldsOrderEmpty = [];
        $this->_displayLabelBuilder = new DisplayLabelBuilder($carpoolDisplayFieldsOrder);
        $this->_displayLabelBuilderEmptyOrder = new DisplayLabelBuilder($carpoolDisplayFieldsOrderEmpty);
    }

    /**
     * @test
     *
     * @dataProvider dataWaypoints
     *
     * @param mixed $waypoint
     */
    public function testBuildDisplayLabelFromWaypointReturnArray($waypoint)
    {
        $this->assertIsArray($this->_displayLabelBuilder->buildDisplayLabelFromWaypoint($waypoint));
    }

    /**
     * @test
     *
     * @dataProvider dataWaypoints
     *
     * @param mixed $waypoint
     * @param mixed $expectedResult
     */
    public function testBuildDisplayLabelFromWaypoint($waypoint, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->_displayLabelBuilder->buildDisplayLabelFromWaypoint($waypoint));
    }

    /**
     * @test
     *
     * @dataProvider dataWaypoints
     *
     * @param mixed $waypoint
     */
    public function testBuildDisplayLabelFromWaypointWithEmptyOrder($waypoint)
    {
        $this->assertEquals([], $this->_displayLabelBuilderEmptyOrder->buildDisplayLabelFromWaypoint($waypoint));
    }

    public function dataWaypoints(): array
    {
        $waypoint1 = new Waypoint();
        $waypoint1_result = [];

        $waypoint2 = new Waypoint();
        $address2 = new Address();
        $address2->setAddressLocality('Saint-Martin');
        $waypoint2->setAddress($address2);
        $waypoint1_result2 = [
            ['Saint-Martin'],
        ];

        return [
            [$waypoint1, $waypoint1_result],
            [$waypoint2, $waypoint1_result2],
        ];
    }
}
