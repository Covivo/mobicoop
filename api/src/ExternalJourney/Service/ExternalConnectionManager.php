<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\ExternalJourney\Service;

use App\Carpool\Ressource\Ad;
use App\ExternalJourney\Entity\ExternalJourneyProvider;
use App\ExternalJourney\Event\ExternalConnectionConfirmedEvent;
use App\ExternalJourney\Ressource\ExternalConnection;
use GuzzleHttp\Client;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * External connection service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ExternalConnectionManager
{
    private const EXTERNAL_CONNECTION_HASH = 'sha256';         // hash algorithm

    private $providers;
    private $operator;
    private $eventDispatcher;

    public function __construct(array $providers, array $operator, EventDispatcherInterface $eventDispatcher)
    {
        $this->providers = $providers;
        $this->operator = $operator;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Send an external connection.
     */
    public function sendConnection(ExternalConnection $externalConnection): ExternalConnection
    {
        // Check if the provider is valid
        if (!isset($this->providers[$externalConnection->getProvider()])) {
            throw new \LogicException('Not a valid provider in providers.json');
        }

        $provider = new ExternalJourneyProvider();
        $provider->setName($externalConnection->getProvider());
        $provider->setUrl($this->providers[$externalConnection->getProvider()]['url']);
        $provider->setResource($this->providers[$externalConnection->getProvider()]['resourceConnection']);
        $provider->setApiKey($this->providers[$externalConnection->getProvider()]['api_key']);
        $provider->setPrivateKey($this->providers[$externalConnection->getProvider()]['private_key']);

        // Determine if the current User is the driver or the passenger

        // By default, if not specified, the sender is the passenger
        $role = Ad::ROLE_PASSENGER;
        if (!is_null($externalConnection->getRole())) {
            $role = $externalConnection->getRole();
        }

        if (!is_numeric($role)
            || (Ad::ROLE_DRIVER !== $role && Ad::ROLE_PASSENGER !== $role && Ad::ROLE_DRIVER_OR_PASSENGER !== $role)
        ) {
            throw new \LogicException('Invalid role');
        }
        if (Ad::ROLE_DRIVER_OR_PASSENGER == $role) {
            // Force "driver_or_passenger" to passenger
            $role = Ad::ROLE_PASSENGER;
        }

        // initialize client API for any request
        $client = new Client();

        $params = [
            'origin' => $this->operator['origin'],
            'operator' => $this->operator['name'],
            'details' => $externalConnection->getContent(),
            'journeys' => [
                'uuid' => $externalConnection->getJourneysUuid(),
            ],
            'driver' => [
                'uuid' => (Ad::ROLE_PASSENGER == $role) ? $externalConnection->getCarpoolerUuid() : null,
                'state' => (Ad::ROLE_PASSENGER == $role) ? ExternalConnection::STATUS_RECIPIENT : ExternalConnection::STATUS_SENDER,
            ],
            'passenger' => [
                'uuid' => (Ad::ROLE_PASSENGER == $role) ? null : $externalConnection->getCarpoolerUuid(),
                'state' => (Ad::ROLE_PASSENGER == $role) ? ExternalConnection::STATUS_SENDER : ExternalConnection::STATUS_RECIPIENT,
            ],
        ];

        $query = [
            'timestamp' => time(),
            'apikey' => $provider->getApiKey(),
        ];

        // construct the requested url
        $url = $provider->getUrl().'/'.$provider->getResource().'?'.http_build_query($query);
        $signature = hash_hmac(self::EXTERNAL_CONNECTION_HASH, $url, $provider->getPrivateKey());
        $signedUrl = $url.'&signature='.$signature;

        // Type of the body
        $options['form_params'] = $params;

        $data = $client->post($signedUrl, $options);
        $data = $data->getBody()->getContents();

        if (false == strpos($data, 'error')) {
            $event = new ExternalConnectionConfirmedEvent($externalConnection);
            $this->eventDispatcher->dispatch(ExternalConnectionConfirmedEvent::NAME, $event);
        }

        return $externalConnection;
    }
}
