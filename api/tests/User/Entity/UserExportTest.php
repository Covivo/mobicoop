<?php

namespace App\User\Entity;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class UserExportTest extends TestCase
{
    /**
     * @var UserExport
     */
    private $_export;

    public function setUp(): void
    {
        $this->_export = new UserExport();
    }

    /**
     * @dataProvider dataGender
     *
     * @test
     *
     * @param mixed $gender
     */
    public function setGenderMale($gender)
    {
        $this->_export->setGender($gender);

        $this->assertSame($gender, $this->_export->getGender());
    }

    public function dataGender(): array
    {
        return [
            ['Indéfini', 'Femme', 'Homme', 'Autre'],
        ];
    }
}
