<?php

namespace App\ExternalJourney\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\ExternalJourney\Entity\ExternalJourney;

final class ExternalJourneyCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return ExternalJourney::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null): array
    {
        $apiUrl = 'http://api.test.ouestgo.fr';
        $apiKey= 'rdex_mobicoop';//public apikey
        $privateKey = 'rdex_mobicoop_uijdhdh4822444;jhduudd854128AJSjhhh-42';

        /*$apiUrl = 'http://www.covivo.eu';
        $apiKey= 'rdex_itinisere';//public apikey
        $privateKey = 'rdex_itinisere_&aer-açàuhb2-/!.1a51a-541?!auigyzur-42';*/

        $searchParameters  = [
            'driver'  => [
                'state'   => 1
            ],
            'passenger' => [
                'state'   => 1
            ],
            'from'    => [
                'latitude'  =>48.69278,//Nancy
                'longitude' => 6.18361
            ],
            'to'    => [
                'latitude'  => 49.11972,//Metz
                'longitude' => 6.17694
            ],
            //optional
            //'frequency' => 'regular',
            'outward' => []
        ];

        $data = array(
           'timestamp' => time(),
           'apikey'    => $apiKey,
           'p'         => $searchParameters //optional if POST
        );

        // Construct the requested url
        $url = $apiUrl.'/restapi/journeys.json?'.http_build_query($data);
        $signature = hash_hmac('sha256', $url, $privateKey);
        $signedUrl = $url.'&signature='.$signature;

        //Request the url
        $data = file_get_contents($signedUrl);

        //echo(gettype(json_decode($data)));
        return json_decode($data, true);

    }
}
