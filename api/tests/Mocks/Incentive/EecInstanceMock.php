<?php

namespace App\Tests\Mocks\Incentive;

use App\Incentive\Resource\EecInstance;

class EecInstanceMock
{
    public static function getEecInstanceUnavailable1(): EecInstance
    {
        return new EecInstance([
            'expirationDate' => '2023-10-01',
            'features' => [
                'available' => true,
                'ldAvailable' => true,
                'sdAvailable' => true,
            ],
            'provider' => [
                'api_uri' => '',
                'app_id' => null,
                'app_secret' => null,
                'authentication_uri' => '',
                'auto_create_account' => false,
                'client_id' => null,
                'client_secret' => null,
                'code_verifier' => null,
                'logout_redirect_uri' => '',
                'name' => 'mobConnect',
            ],
            'subscriptions' => [
                'ld' => [
                    'key' => 'my_ld_key',
                    'expirationDate' => null,
                    'minimalDistance' => 80000,
                    'progressVisualization' => true,
                ],
                'sd' => [
                    'key' => 'my_sd_key',
                    'expirationDate' => null,
                    'progressVisualization' => true,
                ],
            ],
            'previousPeriodWithoutTravel' => 3,
            'tabView' => true,
        ], md5(rand()));
    }

    public static function getEecInstanceUnavailable2(): EecInstance
    {
        return new EecInstance([
            'expirationDate' => null,
            'features' => [
                'available' => true,
                'ldAvailable' => true,
                'sdAvailable' => true,
            ],
            'provider' => [
                'api_uri' => '',
                'app_id' => null,
                'app_secret' => null,
                'authentication_uri' => '',
                'auto_create_account' => false,
                'client_id' => null,
                'client_secret' => null,
                'code_verifier' => null,
                'logout_redirect_uri' => '',
                'name' => 'mobConnect',
            ],
            'subscriptions' => [
                'ld' => [
                    'key' => 'my_ld_key',
                    'expirationDate' => '2023-10-01',
                    'minimalDistance' => 80000,
                    'progressVisualization' => true,
                ],
                'sd' => [
                    'key' => 'my_sd_key',
                    'expirationDate' => '2023-10-01',
                    'progressVisualization' => true,
                ],
            ],
            'previousPeriodWithoutTravel' => 3,
            'tabView' => true,
        ], md5(rand()));
    }

    public static function getEecInstanceAvailable1(): EecInstance
    {
        return new EecInstance([
            'expirationDate' => null,
            'features' => [
                'available' => true,
                'ldAvailable' => true,
                'sdAvailable' => true,
            ],
            'provider' => [
                'api_uri' => '',
                'app_id' => null,
                'app_secret' => null,
                'authentication_uri' => '',
                'auto_create_account' => false,
                'client_id' => null,
                'client_secret' => null,
                'code_verifier' => null,
                'logout_redirect_uri' => '',
                'name' => 'mobConnect',
            ],
            'subscriptions' => [
                'ld' => [
                    'key' => 'my_ld_key',
                    'expirationDate' => null,
                    'minimalDistance' => 80000,
                    'progressVisualization' => true,
                ],
                'sd' => [
                    'key' => 'my_sd_key',
                    'expirationDate' => null,
                    'progressVisualization' => true,
                ],
            ],
            'previousPeriodWithoutTravel' => 3,
            'tabView' => true,
        ], md5(rand()));
    }

    public static function getEecInstanceAvailable2(): EecInstance
    {
        return new EecInstance([
            'expirationDate' => '2027-01-01',
            'features' => [
                'available' => true,
                'ldAvailable' => true,
                'sdAvailable' => true,
            ],
            'provider' => [
                'api_uri' => '',
                'app_id' => null,
                'app_secret' => null,
                'authentication_uri' => '',
                'auto_create_account' => false,
                'client_id' => null,
                'client_secret' => null,
                'code_verifier' => null,
                'logout_redirect_uri' => '',
                'name' => 'mobConnect',
            ],
            'subscriptions' => [
                'ld' => [
                    'key' => 'my_ld_key',
                    'expirationDate' => null,
                    'minimalDistance' => 80000,
                    'progressVisualization' => true,
                ],
                'sd' => [
                    'key' => 'my_sd_key',
                    'expirationDate' => null,
                    'progressVisualization' => true,
                ],
            ],
            'previousPeriodWithoutTravel' => 3,
            'tabView' => true,
        ], md5(rand()));
    }

    public static function getEecInstanceAvailable3(): EecInstance
    {
        return new EecInstance([
            'expirationDate' => null,
            'features' => [
                'available' => true,
                'ldAvailable' => true,
                'sdAvailable' => true,
            ],
            'provider' => [
                'api_uri' => '',
                'app_id' => null,
                'app_secret' => null,
                'authentication_uri' => '',
                'auto_create_account' => false,
                'client_id' => null,
                'client_secret' => null,
                'code_verifier' => null,
                'logout_redirect_uri' => '',
                'name' => 'mobConnect',
            ],
            'subscriptions' => [
                'ld' => [
                    'key' => 'my_ld_key',
                    'expirationDate' => '2027-01-01',
                    'minimalDistance' => 80000,
                    'progressVisualization' => true,
                ],
                'sd' => [
                    'key' => 'my_sd_key',
                    'expirationDate' => null,
                    'progressVisualization' => true,
                ],
            ],
            'previousPeriodWithoutTravel' => 3,
            'tabView' => true,
        ], md5(rand()));
    }

    public static function getEecInstanceAvailable4(): EecInstance
    {
        return new EecInstance([
            'expirationDate' => null,
            'features' => [
                'available' => true,
                'ldAvailable' => true,
                'sdAvailable' => true,
            ],
            'provider' => [
                'api_uri' => '',
                'app_id' => null,
                'app_secret' => null,
                'authentication_uri' => '',
                'auto_create_account' => false,
                'client_id' => null,
                'client_secret' => null,
                'code_verifier' => null,
                'logout_redirect_uri' => '',
                'name' => 'mobConnect',
            ],
            'subscriptions' => [
                'ld' => [
                    'key' => 'my_ld_key',
                    'expirationDate' => null,
                    'minimalDistance' => 80000,
                    'progressVisualization' => true,
                ],
                'sd' => [
                    'key' => 'my_sd_key',
                    'expirationDate' => '2027-01-01',
                    'progressVisualization' => true,
                ],
            ],
            'previousPeriodWithoutTravel' => 3,
            'tabView' => true,
        ], md5(rand()));
    }
}
