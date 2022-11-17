<?php

declare(strict_types=1);

namespace App\ExternalJourney\Service;

class JourneySearcher
{
    private $_providers;

    public function __construct(?array $providers = [])
    {
        foreach ($providers as $name => $detail) {
            switch ($detail['protocol']) {
                case 'RDEX': $this->_providers[] = $this->createProviderRdex($name, $detail);

                    break;

                case 'STANDARD_RDEX': $this->_providers[] = $this->createProviderStandardRdex($name, $detail);

                    break;
            }
        }
    }

    public function search(): array
    {
        $journeys = [];
        foreach ($this->_providers as $provider) {
            /**
             * @var JourneyProvider $provider
             */
            $journeys = array_merge($journeys, $provider->getJourneys([]));
        }

        return $journeys;
    }

    private function createProviderRdex(string $name, array $detail)
    {
        $provider = new JourneyProviderRdex();
        $provider->setName($name);
        $provider->setUrl($detail['url']);
        $provider->setResource($detail['resource']);
        $provider->setApiKey($detail['api_key']);
        $provider->setPrivateKey($detail['private_key']);

        return $provider;
    }

    private function createProviderStandardRdex(string $name, array $detail)
    {
        $provider = new JourneyProviderStandardRdex();
        $provider->setName($name);
        $provider->setUrl($detail['url']);
        $provider->setResource($detail['resource']);
        $provider->setApiKey($detail['api_key']);
        $provider->setPrivateKey($detail['private_key']);

        return $provider;
    }
}
