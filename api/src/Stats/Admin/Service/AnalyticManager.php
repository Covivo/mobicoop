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
use App\Community\Repository\CommunityRepository;
use App\Stats\Admin\Resource\Analytic;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class AnalyticManager
{
    private const ROLE_ADMIN = 'ROLE_ADMIN';
    private const ROLE_COMMUNITY_MANAGER_PUBLIC = 'ROLE_COMMUNITY_MANAGER_PUBLIC';
    private $paramId;
    private $territoryIdParam;
    private $communityIdParam;
    private $darkTheme;
    private $uri;
    private $organization;
    private $secret;
    private $dashboards;
    private $authManager;
    private $communityRepository;
    private $tokenStorage;

    private $defaultCommunityId;
    private $defaultTerritoryId;

    public function __construct(
        RequestStack $requestStack,
        AuthManager $authManager,
        CommunityRepository $communityRepository,
        TokenStorageInterface $tokenStorage,
        array $params
    ) {
        $this->uri = $params['url'];
        $this->organization = $params['organization'];
        $this->secret = $params['secret'];
        $this->dashboards = $params['dashboards'];
        $this->authManager = $authManager;

        $request = $requestStack->getCurrentRequest();
        $this->paramId = $request->get('id');
        $communityIdParam = $request->query->get('communityId', null);
        $this->communityIdParam = is_null($communityIdParam) ? null : intval($communityIdParam);
        $territoryIdParam = $request->query->get('territoryId', null);
        $this->territoryIdParam = is_null($territoryIdParam) ? null : intval($territoryIdParam);
        $this->darkTheme = $request->query->get('darkTheme', false);
        $this->communityRepository = $communityRepository;
        $this->tokenStorage = $tokenStorage;
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

        // $community = null;
        // $territories = null;

        // $this->getDefaultCommunityId();
        // if ('undefined' == $this->territoryIdParam) {
        //     $this->territoryIdParam = null;
        // }

        list($territories, $community) = $this->defineFilters($analytic);
        // list($territories, $community) = $this->setUpFilters($analytic);

        if (null == $community) {
            throw new \LogicException('Community should not be null. This code should not be reached!');
        }
        if (null == $territories) {
            throw new \LogicException('Territories should not be null. This code should not be reached!');
        }

        $payload = [
            'resource' => ['dashboard' => $dashboard['dashboardId']],
            'params' => [
                'idterritoryoperational' => $territories,
                'idcommunityoperational' => $community,
                'organization' => $this->organization,
            ],
        ];

        $url = $this->uri.$this->build_jwt_token($payload).'#bordered=false&titled=false';
        if ($this->darkTheme) {
            $url .= '&theme=night';
        }

        $analytic->setUrl($url);
        $analytic->setCommunityId($this->defaultCommunityId);
        $analytic->setTerritoryId($this->defaultTerritoryId);

        return $analytic;
    }

    private function defineFilters($analytic): array
    {
        $territories = [$this->getOperationalValue(null)];
        $community = $this->getOperationalValue(null);

        if ($this->authManager->isAuthorized(self::ROLE_ADMIN)) {
            return $this->defineFiltersForAdmin();
        }

        if ($this->authManager->isAuthorized(self::ROLE_COMMUNITY_MANAGER_PUBLIC)) {
            return $this->defineFiltersForCommunityModerator();
        }

        return [$territories, $community];
    }

    private function defineFiltersForAdmin(): array
    {
        $community = $this->getOperationalValue(null);

        $dashboard = $this->getDashboard();

        echo $this->territoryIdParam;

        $territories = $this->getTerritories($dashboard['auth_item']);

        return [$territories, $community];
    }

    private function defineFiltersForCommunityModerator(): array
    {
        $this->getDefaultCommunityId();
        $community = $this->defaultCommunityId;

        $dashboard = $this->getDashboard();

        $territories = $this->getTerritories($dashboard['auth_item']);

        return [$territories, $community];
    }

    private function setUpFilters($analytic): array
    {
        $dashboard = $this->getDashboard();
        if ($this->authManager->isAuthorized('ROLE_ADMIN')) {
            if (null != $this->territoryIdParam || null != $this->communityIdParam) {
                // the request has parameter(s)
                $territories = [$this->getOperationalValue($this->territoryIdParam)];
                $community = $this->getCommunity($this->communityIdParam);
                $analytic->setCommunityId($this->communityIdParam);
                $analytic->setTerritoryId($this->territoryIdParam);
            } else {
                // no filter
                $territories = [$this->getOperationalValue(null)];
                $community = $this->getOperationalValue(null);
                $analytic->setCommunityId(null);
                $analytic->setTerritoryId(null);
            }
            var_dump($this->getTerritories($dashboard['auth_item']));

            return [$territories, $community];
        }

        // apply filters defalut values
        $territories = $this->getTerritories($dashboard['auth_item']);
        if (null != $this->territoryIdParam && in_array($this->territoryIdParam, $territories)) {
            // set asked territory filter
            $territories = [$this->getOperationalValue($this->territoryIdParam)];
            if (null != $this->communityIdParam) {
                // TODO need check
                $community = $this->getCommunity($this->communityIdParam);
            } else {
                $community = $this->getOperationalValue(null);
            }
            $analytic->setCommunityId($this->communityIdParam);
            $analytic->setTerritoryId($this->territoryIdParam);

            return [$territories, $community];
        }
        if (null != $this->territoryIdParam) {
            // actually not logic exception
            throw new \LogicException('Forbidden territory. This code should not be reached!');
        }

        $territories = [$this->getOperationalValue(null)];
        if (null != $this->communityIdParam) {
            // TODO need check
            $community = $this->getCommunity($this->communityIdParam);
            $analytic->setCommunityId($this->communityIdParam);
        } elseif (null != $this->defaultCommunityId) {
            $community = $this->getOperationalValue($this->defaultCommunityId);
            $analytic->setCommunityId($this->defaultCommunityId);
        } else {
            throw new \LogicException('if we are here, we should have community id. This code should not be reached!');
        }
        $analytic->setTerritoryId(null);

        return [$territories, $community];
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

    private function getDefaultCommunityId(): void
    {
        $this->defaultCommunityId = null;
        $communityIds = $this->communityRepository->findCommunitiesForRefererOrModerator($this->tokenStorage->getToken()->getUser());
        if (is_array($communityIds) && count($communityIds) > 0) {
            $this->defaultCommunityId = $communityIds[0]['id'];
        }
    }

    private function getOperationalValue(?int $id): string
    {
        if (null === $id) {
            return strtolower($this->organization);
        }

        return strtolower($this->organization).'_'.strval($id);
    }

    private function getCommunity(?int $communityId): string
    {
        if ($this->authManager->isAuthorized('ROLE_ADMIN')) {
            if (null === $communityId) {
                return $this->getOperationalValue(null);
            }

            return $this->getOperationalValue($communityId);
        }

        return $this->getOperationalValue($communityId);
    }

    private function getTerritories(string $auth_item): array
    {
        $territories = $this->authManager->getTerritoryListForItem($auth_item);

        if (0 == count($territories)) {
            return [$this->getOperationalValue(null)];
        }

        foreach ($territories as $key => $territory) {
            if (null == $this->defaultTerritoryId) {
                $this->defaultTerritoryId = $territories[$key];
            }
            $territories[$key] = $this->getOperationalValue($territories[$key]);
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
