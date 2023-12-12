<?php

namespace App\Incentive\Resource;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversDefaultClass
 */
class EecInstanceTest extends TestCase
{
    public function setUp(): void {}

    /**
     * @test
     */
    public function getAvailableFalsy()
    {
        $eecInstance = new EecInstance([
            'ld' => 'my_ld_key',
            'sd' => 'my_sd_key',
        ], [
            'expirationDate' => '2023-10-01',
            'mobConnect' => [
                'client_id' => null,
                'app_id' => null,
                'app_secret' => null,
                'api_uri' => 'https://api-fabmob.stg.hub.flowbird.io',
            ],
            'subscriptions' => [
                'ld' => [
                    'key' => 'my_ld_key',
                    'expirationDate' => null,
                ],
                'sd' => [
                    'key' => 'my_sd_key',
                    'expirationDate' => null,
                ],
            ],
        ]);

        $this->assertFalse($eecInstance->getAvailable());
        $this->assertFalse($eecInstance->isAvailable());

        $eecInstance = new EecInstance([
            'ld' => 'my_ld_key',
            'sd' => 'my_sd_key',
        ], [
            'expirationDate' => null,
            'mobConnect' => [
                'client_id' => null,
                'app_id' => null,
                'app_secret' => null,
                'api_uri' => 'https://api-fabmob.stg.hub.flowbird.io',
            ],
            'subscriptions' => [
                'ld' => [
                    'key' => 'my_ld_key',
                    'expirationDate' => '2023-10-01',
                ],
                'sd' => [
                    'key' => 'my_sd_key',
                    'expirationDate' => '2023-10-01',
                ],
            ],
        ]);

        $this->assertFalse($eecInstance->getAvailable());
        $this->assertFalse($eecInstance->isAvailable());
    }

    /**
     * @test
     */
    public function getAvailableTruly()
    {
        $eecInstance = new EecInstance([
            'ld' => 'my_ld_key',
            'sd' => 'my_sd_key',
        ], [
            'expirationDate' => null,
            'mobConnect' => [
                'client_id' => null,
                'app_id' => null,
                'app_secret' => null,
                'api_uri' => 'https://api-fabmob.stg.hub.flowbird.io',
            ],
            'subscriptions' => [
                'ld' => [
                    'key' => 'my_ld_key',
                    'expirationDate' => null,
                ],
                'sd' => [
                    'key' => 'my_sd_key',
                    'expirationDate' => null,
                ],
            ],
        ]);

        $this->assertTrue($eecInstance->getAvailable());
        $this->assertTrue($eecInstance->isAvailable());

        $eecInstance = new EecInstance([
            'ld' => 'my_ld_key',
            'sd' => 'my_sd_key',
        ], [
            'expirationDate' => '2027-01-01',
            'mobConnect' => [
                'client_id' => null,
                'app_id' => null,
                'app_secret' => null,
                'api_uri' => 'https://api-fabmob.stg.hub.flowbird.io',
            ],
            'subscriptions' => [
                'ld' => [
                    'key' => 'my_ld_key',
                    'expirationDate' => null,
                ],
                'sd' => [
                    'key' => 'my_sd_key',
                    'expirationDate' => null,
                ],
            ],
        ]);

        $this->assertTrue($eecInstance->getAvailable());
        $this->assertTrue($eecInstance->isAvailable());

        $eecInstance = new EecInstance([
            'ld' => 'my_ld_key',
            'sd' => 'my_sd_key',
        ], [
            'expirationDate' => null,
            'mobConnect' => [
                'client_id' => null,
                'app_id' => null,
                'app_secret' => null,
                'api_uri' => 'https://api-fabmob.stg.hub.flowbird.io',
            ],
            'subscriptions' => [
                'ld' => [
                    'key' => 'my_ld_key',
                    'expirationDate' => '2027-01-01',
                ],
                'sd' => [
                    'key' => 'my_sd_key',
                    'expirationDate' => null,
                ],
            ],
        ]);

        $this->assertTrue($eecInstance->getAvailable());
        $this->assertTrue($eecInstance->isAvailable());

        $eecInstance = new EecInstance([
            'ld' => 'my_ld_key',
            'sd' => 'my_sd_key',
        ], [
            'expirationDate' => null,
            'mobConnect' => [
                'client_id' => null,
                'app_id' => null,
                'app_secret' => null,
                'api_uri' => 'https://api-fabmob.stg.hub.flowbird.io',
            ],
            'subscriptions' => [
                'ld' => [
                    'key' => 'my_ld_key',
                    'expirationDate' => null,
                ],
                'sd' => [
                    'key' => 'my_sd_key',
                    'expirationDate' => '2027-01-01',
                ],
            ],
        ]);

        $this->assertTrue($eecInstance->getAvailable());
        $this->assertTrue($eecInstance->isAvailable());
    }
}
