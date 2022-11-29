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

use App\Auth\Service\AuthManager;
use App\Stats\Admin\Resource\Analytic;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class AnalyticManager
{
    private $paramId;
    private $darkTheme;
    private $uri;
    private $organization;
    private $secret;
    private $dashboards;
    private $authManager;

    public function __construct(RequestStack $requestStack, AuthManager $authManager, array $params)
    {
        $this->uri = $params['url'];
        $this->organization = $params['organization'];
        $this->secret = $params['secret'];
        $this->dashboards = $params['dashboards'];
        $this->authManager = $authManager;

        $request = $requestStack->getCurrentRequest();
        $this->paramId = $request->get('id');
        $this->darkTheme = $request->query->get('darkTheme', false);
    }

    public function getAnalytics(): array
    {
        return [];
    }

    public function getAnalytic(int $id): Analytic
    {
        $analytic = new Analytic();
        $analytic->setId($id);
        $dashboard = $this->getDashboard();
        $payload = [
            'resource' => ['dashboard' => $dashboard['dashboardId']],
            'params' => [
                'idterritoryoperational' => $this->getTerritories($dashboard['auth_item']),
                'organization' => $this->organization,
            ],
        ];

        $url = $this->uri.$this->build_jwt_token($payload).'#bordered=false&titled=false';
        if ($this->darkTheme) {
            $url .= '&theme=night';
        }

        $analytic->setUrl($url);

        return $analytic;
    }

    private function getDashboard(): ?array
    {
        foreach ($this->dashboards as $dashboard) {
            if ($dashboard['paramId'] == $this->paramId) {
                return $dashboard;
            }
        }

        throw new ResourceNotFoundException('Unknown dashboard');
    }

    private function getTerritories(string $auth_item): array
    {
        $territories = $this->authManager->getTerritoriesForItem($auth_item);

        if (0 == count($territories)) {
            return [strtolower($this->organization)];
        }

        foreach ($territories as $key => $territory) {
            $territories[$key] = strtolower($this->organization).'_'.$territories[$key];
        }

        return $territories;
    }

    private function build_jwt_token($payload): string
    {
        // build the headers
        $headers = ['alg' => 'HS256', 'typ' => 'JWT'];
        $headers_encoded = self::base64url_encode(json_encode($headers));

        // build the payload
        $payload_encoded = self::base64url_encode(json_encode($payload));

        // build the signature
        $signature = hash_hmac('sha256', "{$headers_encoded}.{$payload_encoded}", $this->secret, true);
        $signature_encoded = self::base64url_encode($signature);

        // build and return the token
        return "{$headers_encoded}.{$payload_encoded}.{$signature_encoded}";
    }

    private function base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
