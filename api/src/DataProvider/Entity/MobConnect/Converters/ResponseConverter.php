<?php

namespace App\DataProvider\Entity\MobConnect\Converters;

use App\DataProvider\Entity\Response;
use Symfony\Component\HttpFoundation\Response as HttpFoundationResponse;

class ResponseConverter
{
    /**
     * Converter a DataProvider response to an HttpFoundation response.
     */
    public static function convertResponseToHttpFondationResponse(Response $source): HttpFoundationResponse
    {
        switch (true) {
            case $source instanceof Response:
                return new HttpFoundationResponse($source->getValue(), $source->getCode());
        }
    }
}
