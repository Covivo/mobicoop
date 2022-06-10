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

use App\DataProvider\Interfaces\ProviderInterface;
use App\DataProvider\Service\DataProvider;

/**
 * Rezopouce API data provider.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class RezopouceProvider implements ProviderInterface
{
    private const ROUTE_AUTH = '/auth-tokens';

    private $uri;
    private $login;
    private $password;
    private $token;

    public function __construct()
    {
        $this->uri = 'https://api.rezopouce.fr';
        $this->login = 'corentin.keroual@rezopouce.fr';
        $this->password = 'Corentin@1234';
        $this->token = $this->__getToken();
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

    /**
     * {@inheritdoc}
     */
    public function getCollection(string $class, string $apikey, array $params)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getItem(string $class, string $apikey, array $params)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function deserialize(string $class, array $data)
    {
    }
}
