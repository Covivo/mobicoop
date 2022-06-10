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

namespace App\DataProvider\Entity;

use App\DataProvider\Service\DataProvider;
use App\Geography\Entity\RezoPouceTerritory;
use App\Geography\Entity\RezoPouceTerritoryStatus;

/**
 * Rezopouce API data provider.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class RezopouceProvider
{
    private const ROUTE_AUTH = '/auth-tokens';
    private const ROUTE_COMMUNE_IS_MEMBER = '/api/communes/{id}/isMember';

    private $uri;
    private $login;
    private $password;
    private $token;

    public function __construct()
    {
        $this->uri = 'https://api.rezopouce.fr';
        $this->login = 'corentin.keroual@rezopouce.fr';
        $this->password = 'Corentin@1234';
    }

    private function __getToken()
    {
        $dataProvider = new DataProvider($this->uri, self::ROUTE_AUTH);

        $body = [
            'login' => $this->login,
            'password' => $this->password,
        ];

        $response = $dataProvider->postCollection($body);
        if (201 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);

            $this->token = $data['value'];
        }
    }

    private function __deserializeTerritory($data): RezoPouceTerritory
    {
        $territory = new RezoPouceTerritory();
        $territory->setSlug($data['slug']);
        $territoryStatus = new RezoPouceTerritoryStatus();
        $territoryStatus->setId($data['status']['id']);
        $territoryStatus->setLabel($data['status']['label']);
        $territory->setStatus($territoryStatus);

        return $territory;
    }

    private function __buildHeaders(): array
    {
        return [
            'X-Auth-Token' => $this->token,
        ];
    }

    public function getCommuneTerritory(int $communeCode): ?RezoPouceTerritory
    {
        $token = $this->__getToken();

        $dataProvider = new DataProvider($this->uri, str_replace('{id}', $communeCode, self::ROUTE_COMMUNE_IS_MEMBER));
        $response = $dataProvider->getItem([], $this->__buildHeaders());

        if (200 == $response->getCode()) {
            $data = json_decode($response->getValue(), true);
            $territory = null;
            if (!is_null($data['territory'])) {
                $territory = $this->__deserializeTerritory($data['territory']);
            }

            return $territory;
        }

        return null;
    }
}
