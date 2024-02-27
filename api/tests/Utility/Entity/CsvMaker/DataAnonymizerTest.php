<?php

namespace App\Utility\Entity\CsvMaker;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class DataAnonymizerTest extends TestCase
{
    private const USER_ID = 'userId';
    private const GENDER = 'gender';
    private const GIVEN_NAME = 'given_name';
    private const FAMILY_NAME = 'family_name';
    private const EMAIL = 'email';
    private const TELEPHONE = 'telephone';
    private const CREATED_DATE = 'created_date';
    private const LAST_ACTIVITY_DATE = 'last_activity_date';
    private const OPTIN = 'optin';
    private $_dataAnonymizer;
    private $_testData;

    public function setUp(): void
    {
        $this->_dataAnonymizer = new DataAnonymizer();

        $this->_testData['userId'] = '1';
        $this->_testData['given_name'] = 'Max';
        $this->_testData['family_name'] = 'Test';
        $this->_testData['gender'] = '1';
        $this->_testData['email'] = 'max.test@cestuntestalorscherchepas.com';
        $this->_testData['telephone'] = null;
        $this->_testData['created_date'] = '2024-01-31 10:47:11';
        $this->_testData['last_activity_date'] = null;
        $this->_testData['optin'] = '1';
    }

    /**
     * @test
     */
    public function testAnonymizeReturnsArray()
    {
        $this->assertIsArray($this->_dataAnonymizer->anonymize($this->_testData));
    }

    /**
     * @test
     */
    public function testAnonymizeReturnsADifferentArray()
    {
        $this->assertNotEquals($this->_testData, $this->_dataAnonymizer->anonymize($this->_testData));
    }

    /**
     * @test
     */
    public function testGivenNameHasBeenAnonimyzed()
    {
        $this->assertNotEquals($this->_testData[self::GIVEN_NAME], $this->_dataAnonymizer->anonymize($this->_testData)[self::GIVEN_NAME]);
    }

    /**
     * @test
     */
    public function testFamilyNameHasBeenAnonimyzed()
    {
        $this->assertNotEquals($this->_testData[self::FAMILY_NAME], $this->_dataAnonymizer->anonymize($this->_testData)[self::FAMILY_NAME]);
    }

    /**
     * @test
     */
    public function testEmailHasBeenAnonimyzed()
    {
        $this->assertNotEquals($this->_testData[self::EMAIL], $this->_dataAnonymizer->anonymize($this->_testData)[self::EMAIL]);
    }

    /**
     * @test
     */
    public function testTelephoneHasBeenAnonimyzed()
    {
        $this->assertNotEquals($this->_testData[self::TELEPHONE], $this->_dataAnonymizer->anonymize($this->_testData)[self::TELEPHONE]);
    }

    /**
     * @test
     */
    public function testUserIdHasNotBeenAnonimyzed()
    {
        $this->assertEquals($this->_testData[self::USER_ID], $this->_dataAnonymizer->anonymize($this->_testData)[self::USER_ID]);
    }

    /**
     * @test
     */
    public function testGenderHasNotBeenAnonimyzed()
    {
        $this->assertEquals($this->_testData[self::GENDER], $this->_dataAnonymizer->anonymize($this->_testData)[self::GENDER]);
    }

    /**
     * @test
     */
    public function testCreatedDateHasNotBeenAnonimyzed()
    {
        $this->assertEquals($this->_testData[self::CREATED_DATE], $this->_dataAnonymizer->anonymize($this->_testData)[self::CREATED_DATE]);
    }

    /**
     * @test
     */
    public function testLastActivityDateHasNotBeenAnonimyzed()
    {
        $this->assertEquals($this->_testData[self::LAST_ACTIVITY_DATE], $this->_dataAnonymizer->anonymize($this->_testData)[self::LAST_ACTIVITY_DATE]);
    }

    /**
     * @test
     */
    public function testOptInHasNotBeenAnonimyzed()
    {
        $this->assertEquals($this->_testData[self::OPTIN], $this->_dataAnonymizer->anonymize($this->_testData)[self::OPTIN]);
    }
}
