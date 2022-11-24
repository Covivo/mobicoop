<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
 * This project is dual licensed under AGPL and proprietary licence.
 ***************************
 *    This program is free software: you can redistribute it and/or modify
 *    it under the terms of the GNU Affero General Public License as
 *    published by the Free Software Foundation, either version 3 of the
 *    License, or (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with this program.  If not, see <gnu.org/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 */

declare(strict_types=1);

namespace App\ExternalJourney\Service;

use Symfony\Component\HttpFoundation\Request;

class JourneySearcher
{
    private $providers;
    private $_providers = [];

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

    public function search(Request $request): array
    {
        // todo remove providers if only one selected...  $providerName = $request->get('provider');
        $journeys = [];
        foreach ($this->_providers as $provider) {
            // @var JourneyProvider $provider
            $journeys[] = $provider->getJourneys($provider, $request);
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
