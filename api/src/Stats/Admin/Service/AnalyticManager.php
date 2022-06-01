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

namespace App\Stats\Admin\Service;

use App\Stats\Admin\Resource\Analytic;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class AnalyticManager
{
    public function getAnalytics(): array
    {
        return [];
    }

    public function getAnalytic(int $id): Analytic
    {
        $analytic = new Analytic();
        $analytic->setId($id);

        $url_analytic = 'http://localhost:3000';

        $payload = [
            'resource' => ['dashboard' => 35],
            'params' => [
                'idterritoryoperational' => [226],
            ],
        ];

        $analytic->setUrl($url_analytic.'/embed/dashboard/'.self::build_jwt_token($payload).'#bordered=false&titled=false');

        return $analytic;
    }

    private function build_jwt_token($payload): string
    {
        $secret = '4c228ad22521a64982b9da7d560fafeda2e5c0f26f04f84d957bd10e0eddcc09';

        // build the headers
        $headers = ['alg' => 'HS256', 'typ' => 'JWT'];
        $headers_encoded = self::base64url_encode(json_encode($headers));

        // build the payload
        $payload_encoded = self::base64url_encode(json_encode($payload));

        // build the signature
        $signature = hash_hmac('sha256', "{$headers_encoded}.{$payload_encoded}", $secret, true);
        $signature_encoded = self::base64url_encode($signature);

        // build and return the token
        return "{$headers_encoded}.{$payload_encoded}.{$signature_encoded}";
    }

    private function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
