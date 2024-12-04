<?php

namespace App\OAuth\Service;

use Symfony\Component\HttpFoundation\Response;

class ServiceDefinition
{
    public const PROPERTY_URI = 'uri';
    public const PROPERTY_FILE_PATH = 'file_path';
    public const PROPERTY_ACCESS_KEY = 'access_key';
    public const PROPERTY_SECRET_KEY = 'secret_key';

    public const SERVICE_PROPERTIES = [
        self::PROPERTY_URI,
        self::PROPERTY_FILE_PATH,
        self::PROPERTY_ACCESS_KEY,
        self::PROPERTY_SECRET_KEY,
    ];

    public static function isServiceAvailable(array $servicesDefinition, string $service): bool
    {
        return array_key_exists($service, $servicesDefinition);
    }

    public static function _checkServiceConfiguration(\stdClass $service)
    {
        foreach (self::SERVICE_PROPERTIES as $property) {
            if (
                !property_exists($service, $property)
            ) {
                throw new \Exception('The OAuth service for property '.$property.' is miss configured', Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        }
    }
}
