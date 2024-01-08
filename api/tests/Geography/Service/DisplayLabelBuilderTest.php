<?php

namespace App\Geography\Service;

use App\Carpool\Entity\Waypoint;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class DisplayLabelBuilderTest extends TestCase
{
    private $_displayLabelBuilder;

    public function setUp(): void
    {
        $carpoolDisplayFieldsOrder = [];
        $this->_displayLabelBuilder = new DisplayLabelBuilder($carpoolDisplayFieldsOrder);
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

    public function dataWaypoints(): array
    {
        $waypoint1 = new Waypoint();
        $waypoint1_result = [];

        return [
            [$waypoint1, $waypoint1_result],
        ];
    }
}
