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
    private $_carpoolDisplayFieldsOrder;
    private $_carpoolDisplayFieldsOrderEmpty;
    private $_carpoolDisplayFieldsOrderWithInvalidField;

    public function setUp(): void
    {
        $this->_carpoolDisplayFieldsOrder = json_decode('{"0":{"0":"street","1":"postalCode"},"1":{"0":"addressLocality"}}', true);
        $this->_carpoolDisplayFieldsOrderEmpty = json_decode('{}', true);
        $this->_carpoolDisplayFieldsOrderWithInvalidField = json_decode('{"0":{"0":"street","1":"postalCode"},"1":{"0":"addressLocality", "1":"blahblah"}}', true);
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
        $displayLabelBuilder = new DisplayLabelBuilder($this->_carpoolDisplayFieldsOrder);
        $this->assertIsArray($displayLabelBuilder->buildDisplayLabelFromWaypoint($waypoint));
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
        $displayLabelBuilder = new DisplayLabelBuilder($this->_carpoolDisplayFieldsOrder);
        $this->assertEquals($expectedResult, $displayLabelBuilder->buildDisplayLabelFromWaypoint($waypoint));
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
        $displayLabelBuilderEmptyOrder = new DisplayLabelBuilder($this->_carpoolDisplayFieldsOrderEmpty);
        $this->assertEquals([], $displayLabelBuilderEmptyOrder->buildDisplayLabelFromWaypoint($waypoint));
    }

    /**
     * @test
     *
     * @dataProvider dataWaypoints
     *
     * @param mixed $waypoint
     * @param mixed $expectedResult
     */
    public function testBuildDisplayLabelFromWaypointWithInvalidGetter($waypoint, $expectedResult)
    {
        $displayLabelBuilderEmptyOrder = new DisplayLabelBuilder($this->_carpoolDisplayFieldsOrderWithInvalidField);
        $this->assertEquals($expectedResult, $displayLabelBuilderEmptyOrder->buildDisplayLabelFromWaypoint($waypoint));
    }

    public function dataWaypoints(): array
    {
        $waypoint1 = new Waypoint();
        $waypoint1_result = [];

        $waypoint2 = new Waypoint();
        $address2 = new Address();
        $address2->setAddressLocality('Saint-Martin');
        $address2->setStreet('Grand Case');
        $address2->setPostalCode('97801');
        $waypoint2->setAddress($address2);
        $waypoint1_result2 = [
            ['Grand Case', '97801'],
            ['Saint-Martin'],
        ];

        return [
            [$waypoint1, $waypoint1_result],
            [$waypoint2, $waypoint1_result2],
        ];
    }
}
