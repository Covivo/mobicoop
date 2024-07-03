<?php

namespace App\Service\Pipe;

abstract class AddressLocalityPipe
{
    public const SAINT = ['pattern' => '/^St./', 'replacement' => 'Saint-'];

    public static function prefixSaint(?string $localityName): string
    {
        return preg_replace(static::SAINT['pattern'], static::SAINT['replacement'], $localityName);
    }
}
