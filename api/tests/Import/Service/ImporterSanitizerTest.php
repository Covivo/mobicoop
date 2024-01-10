<?php

namespace App\Geography\Service;

use App\Import\Admin\Service\ImporterSanitizer;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class DisplayLabelBuilderTest extends TestCase
{
    private $_sanitizer;

    public function setUp(): void
    {
        $this->_sanitizer = new ImporterSanitizer();
    }

    /**
     * @test
     *
     * @dataProvider getLine
     */
    public function testSanizeReturnAnArray(array $line)
    {
        $this->assertIsArray($this->_sanitizer->sanitize($line));
    }

    public function getLine()
    {
        return [
            [[]],
            [['Point relais trop cool', '1', '48.69395065307617', '6.1790642738342285', '154', '10', '1', '1', '1', '0', 'XXX-XXX', 'Source of the point', '1', 'short desc', 'full description']],
            [['max.testimport@yopmail.com', 'Max', 'TestImport', '1', '1982-02-03', '0666666666', 'communityId', '54000', 'Nancy', '1']],
        ];
    }
}
