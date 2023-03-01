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

namespace App\CarpoolStandard\Service;

use App\CarpoolStandard\Entity\Booking;
use App\CarpoolStandard\Entity\Message;
use App\CarpoolStandard\Exception\CarpoolStandardException;
use App\DataProvider\Entity\InteropProvider;

/**
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class CarpoolStandardProvider
{
    private const SUPPORTED_PROVIDERS = [
        'Interop' => InteropProvider::class,
    ];
    private $provider;
    private $baseUri;
    private $apiKey;
    private $providerInstance;

    public function __construct(
        string $provider,
        string $baseUri,
        string $apiKey
    ) {
        $this->provider = $provider;
        $this->baseUri = $baseUri;
        $this->apiKey = $apiKey;
    }

    public function checkCarpoolStandardConfiguration()
    {
        if ('' !== $this->provider) {
            if (isset(self::SUPPORTED_PROVIDERS[$this->provider])) {
                $providerClass = self::SUPPORTED_PROVIDERS[$this->provider];
                $this->providerInstance = new $providerClass(
                    $this->provider,
                    $this->baseUri,
                    $this->apiKey
                );
            }
        } else {
            return;
        }
        if (empty($this->provider)) {
            throw new CarpoolStandardException(CarpoolStandardException::NO_PROVIDER);
        }

        if (!isset(self::SUPPORTED_PROVIDERS[$this->provider])) {
            throw new CarpoolStandardException(CarpoolStandardException::UNSUPPORTED_PROVIDER);
        }
    }

    public function postMessage(Message $message)
    {
        $this->checkCarpoolStandardConfiguration();

        return $this->providerInstance->postMessage($message);
    }

    public function postBooking(Booking $booking)
    {
        $this->checkCarpoolStandardConfiguration();

        return $this->providerInstance->postBooking($booking);
    }
}
