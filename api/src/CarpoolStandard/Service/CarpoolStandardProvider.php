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

namespace App\CarpoolStandard\Service;

use App\CarpoolStandard\Ressource\Message;
use App\DataProvider\Entity\InteropProvider;

/**
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class CarpoolStandardProvider
{
    private const SUPPORTED_PROVIDERS = [
        'Interop' => InteropProvider::class,
    ];
    private $carpoolStandardProvider;
    private $providerInstance;
    private $interopBaseUri;
    private $interopApiKey;

    public function __construct(
        string $carpoolStandardProvider,
        string $interopBaseUri,
        string $interopApiKey
    ) {
        $this->carpoolStandardProvider = $carpoolStandardProvider;
        $this->interopBaseUri = $interopBaseUri;
        $this->interopApiKey = $interopApiKey;
    }

    /**
     * Check if the payment is correcty configured.
     */
    public function checkCarpoolStandardConfiguration()
    {
        if ('' !== $this->carpoolStandardProvider) {
            if (isset(self::SUPPORTED_PROVIDERS[$this->carpoolStandardProvider])) {
                $providerClass = self::SUPPORTED_PROVIDERS[$this->carpoolStandardProvider];
                $this->providerInstance = new $providerClass(
                    $this->interopBaseUri,
                    $this->interopApiKey
                );
            }
        } else {
            return;
        }
    }

    public function postMessage(Message $message)
    {
        $this->checkCarpoolStandardConfiguration();

        var_dump(get_class($this->providerInstance));

        exit;

        return $this->providerInstance->postMessage($message);
    }
}
