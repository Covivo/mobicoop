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

namespace App\DataProvider\Entity;

use App\DataProvider\Service\DataProvider;
use App\User\Interfaces\ConsumptionFeedbackInterface;

/**
 * Worldline Provider
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class WorldlineProvider implements ConsumptionFeedbackInterface
{
    const AUTHORIZATION_URL = "auth/realms/Partners/protocol/openid-connect/token";
    const GRANT_TYPE = "client_credentials";

    private $clientId;
    private $clientSecret;
    private $baseUrl;
    private $authChain;
    
    

    public function __construct(string $clientId, string $clientSecret, string $baseUrl)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->baseUrl = $baseUrl;
        $this->authChain = "Basic ".base64_encode($clientId.":".$clientSecret);
    }

    /**
     * Get the auth token
     *
     * @return string The auth token
     */
    public function auth(): string
    {
        $dataProvider = new DataProvider($this->baseUrl."/".self::AUTHORIZATION_URL);

        $body['grant_type'] = self::GRANT_TYPE;

        $headers = [
            "Authorization" => $this->authChain
        ];

        $response = $dataProvider->postCollection($body, $headers, null, DataProvider::BODY_TYPE_FORM_PARAMS);
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
        } else {
            throw new \LogicException("Auth failed");
        }
        
        return $data['access_token'];
    }
}
