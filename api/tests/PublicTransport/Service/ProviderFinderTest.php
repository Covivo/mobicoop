<?php

namespace App\PublicTransport\Service;

use App\Geography\Repository\TerritoryRepository;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class ProviderFinderTest extends TestCase
{
    private const DEFAULT_PROVIDER = [
        'dataprovider' => 'default provider',
        'url' => 'url of the api with end /',
        'username' => '',
        'apikey' => '',
        'params' => [],
        'ptProviderName' => '',
        'ptProviderUrl' => '',
        'threshold' => 0,
        'threshold_granularity' => 'day',
    ];

    private const TERRITORY_ID = 18;
    private const TERRITORY_PROVIDER = [
        'dataprovider' => 'territory '.self::TERRITORY_ID.' provider',
        'url' => 'url of the api with end /',
        'username' => '',
        'apikey' => '',
        'params' => [],
        'ptProviderName' => '',
        'ptProviderUrl' => '',
        'threshold' => 0,
        'threshold_granularity' => 'day',
    ];

    private const PT_PROVIDERS = ['default' => self::DEFAULT_PROVIDER, self::TERRITORY_ID => self::TERRITORY_PROVIDER];

    private $_providerFinder;
    private $_territoryRepository;

    public function setUp(): void
    {
        $this->_territoryRepository = $this->getMockBuilder(TerritoryRepository::class)
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->_providerFinder = new ProviderFinder($this->_territoryRepository, self::PT_PROVIDERS, 0, 0);
    }

    // @test
    public function testfindProviderReturnsArray()
    {
        $this->_territoryRepository->method('findPointTerritories')->willReturn([]);
        $this->assertIsArray($this->_providerFinder->findProvider());
    }

    public function testfindProviderForCoveredTerritoryReturnsRightProvider()
    {
        $this->_territoryRepository->method('findPointTerritories')->willReturn([['id' => self::TERRITORY_ID]]);
        $this->assertEquals(self::TERRITORY_PROVIDER, $this->_providerFinder->findProvider());
    }

    public function testfindProviderForUncoveredTerritoryReturnsDefaultProvider()
    {
        $this->_territoryRepository->method('findPointTerritories')->willReturn([['id' => 25]]);
        $this->assertEquals(self::DEFAULT_PROVIDER, $this->_providerFinder->findProvider());
    }
}
