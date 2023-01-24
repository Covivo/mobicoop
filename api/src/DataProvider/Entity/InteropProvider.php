<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\DataProvider\Entity;

use App\CarpoolStandard\Interfaces\CarpoolStandardProviderInterface;
use App\CarpoolStandard\Ressource\Message;
use App\DataProvider\Service\DataProvider;

class InteropProvider implements CarpoolStandardProviderInterface
{
    private const RESSOURCE_MESSAGE = '/messages';

    private $baseUri;
    private $apiKey;

    public function __construct(string $baseUri, string $apiKey)
    {
        $this->baseUri = $baseUri;
        $this->apiKey = $apiKey;
    }

    public function postMessage(Message $message)
    {
        $dataProvider = new DataProvider($this->baseUri.self::RESSOURCE_MESSAGE);

        $headers = [
            'X-API-KEY' => $this->apiKey,
        ];
        // Build the body
        $body['from'] = [];
        $body['to'] = [];
        $body['message'] = '';
        $body['recipientCarpoolerType'] = '';
        $body['driverJourneyId'] = '';
        $body['passengerJourneyId'] = '';
        $body['bookingId'] = '';

        return $dataProvider->postCollection($body, $headers);
    }
}
