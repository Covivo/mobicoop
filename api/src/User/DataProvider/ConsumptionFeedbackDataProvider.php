<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\User\DataProvider;

use App\DataProvider\Entity\WorldlineProvider;
use App\Payment\Entity\CarpoolItem;

/**
 * Consumption Feedback DataProvider
 *
 * This service contains methods related to the consumption feedback.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ConsumptionFeedbackDataProvider
{
    private const SUPPORTED_PROVIDERS = [
        "PassMobilite" => WorldlineProvider::class
    ];
    
    private $providerInstance;
    private $active;

    public function __construct(bool $active, string $provider, string $baseUrl, string $clientId, string $clientSecret)
    {
        $this->active = $active;
        if ($active && $provider!=="") {
            if (isset(self::SUPPORTED_PROVIDERS[$provider])) {
                $providerClass = self::SUPPORTED_PROVIDERS[$provider];
                $this->providerInstance = new $providerClass($clientId, $clientSecret, $baseUrl);
            }
        } else {
            return;
        }
    }

    /**
     * Get the auth token
     *
     * @return string The auth token
     */
    public function auth(): string
    {
        return $this->providerInstance->auth();
    }

    /**
     * Send a consumption feedback
     *
     * @return CarpoolItem The CarpoolItem related to this consumption
     */
    public function sendConsumptionFeedback(CarpoolItem $carpoolItem)
    {
        $this->providerInstance->sendConsumptionFeedback($carpoolItem);
    }

    /**
     * Return if the consumption feedback provider is defined and active
     */
    public function isActive(): bool
    {
        return $this->active && !is_null($this->providerInstance);
    }
}
