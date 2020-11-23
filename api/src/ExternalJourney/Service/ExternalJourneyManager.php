<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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
 **************************/

namespace App\ExternalJourney\Service;

use App\ExternalJourney\Entity\ExternalJourneyProvider;

/**
 * External journey service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ExternalJourneyManager
{
    private $operator;
    private $clients;
    private $providers;

    public function __construct(?array $operator = [], ?array $clients = [], ?array $providers = [])
    {
        $this->operator = $operator;
        $this->clients = $clients;
        foreach ($providers as $providerName=>$details) {
            $provider = new ExternalJourneyProvider();
            $provider->setName($providerName);
            $provider->setUrl($details['url']);
            $provider->setResource($details['resource']);
            $provider->setApiKey($details['api_key']);
            $provider->setPrivateKey($details['private_key']);
            $this->providers[] = $provider;
        }
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function getClients()
    {
        return $this->clients;
    }

    public function getProviders()
    {
        return $this->providers;
    }
}
