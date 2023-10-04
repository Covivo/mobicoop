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
    private $_paramType;
    private $_paramPeriodicity;
    private $_territoryIdParam;
    private $_communityIdParam;
    private $_darkTheme;
    private $_uri;
    private $_organization;
    private $_database;
    private $_secret;
    private $_dashboards;
    private $_authManager;
    private $_communityRepository;
    private $_tokenStorage;

    private $_defaultCommunityId;
    private $_defaultTerritoryId;
    private $_forceDefaultCommunityId;
    private $_forceDefaultTerritoryId;

    public function __construct(
        RequestStack $requestStack,
        AuthManager $authManager,
        CommunityRepository $communityRepository,
        TokenStorageInterface $tokenStorage,
        array $params
    ) {
        $this->_uri = $params['url'];
        $this->_organization = $params['organization'];
        $this->_database = $params['database'];
        $this->_secret = $params['secret'];
        $this->_dashboards = $params['dashboards'];
        $this->_authManager = $authManager;

        $request = $requestStack->getCurrentRequest();
        $this->_paramType = $request->get('id');
        $this->_paramPeriodicity = $request->query->get('periodicity', null);
        $communityIdParam = $request->query->get('communityId', null);
        $this->_communityIdParam = is_null($communityIdParam) ? null : intval($communityIdParam);
        $territoryIdParam = $request->query->get('territoryId', null);
        $this->_territoryIdParam = is_null($territoryIdParam) ? null : intval($territoryIdParam);
        $this->_forceDefaultCommunityId = $request->query->get('forceDefaultCommunityId', null);
        $this->_forceDefaultTerritoryId = $request->query->get('forceDefaultTerritoryId', null);
        $this->_darkTheme = $request->query->get('darkTheme', false);
        $this->_communityRepository = $communityRepository;
        $this->_tokenStorage = $tokenStorage;
    }

    public function getAnalytics(): array
    {
        return [];
    }

    public function getAnalytic(string $type): Analytic
    {
        echo 'getAnalytic'.PHP_EOL;
        echo $type.PHP_EOL;

        $analytic = new Analytic();
        $analytic->setType($type);
        $dashboard = $this->_getDashboard();
        var_dump($dashboard);

        exit;

        list($territories, $community) = $this->_defineFilters($analytic);

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
                'organization' => $this->_organization,
            ],
        ];

        $url = $this->_uri.$this->_build_jwt_token($payload).'#bordered=false&titled=false';
        if ($this->_darkTheme) {
            $url .= '&theme=night';
        }

        $analytic->setUrl($url);
        $analytic->setCommunityId($this->_defaultCommunityId);
        $analytic->setTerritoryId($this->_defaultTerritoryId);
        $analytic->setForceDefaultCommunityId($this->_forceDefaultCommunityId);
        $analytic->setForceDefaultTerritoryId($this->_forceDefaultTerritoryId);

        return $analytic;
    }

    private function _defineFilters($analytic): array
    {
        $territories = [$this->_getOperationalValue(null)];
        $community = $this->_getOperationalValue(null);

        if ($this->_authManager->isAuthorized(self::ROLE_ADMIN)) {
            return $this->_defineFiltersForAdmin();
        }

        if ($this->_authManager->isAuthorized(self::ROLE_COMMUNITY_MANAGER_PUBLIC)) {
            return $this->_defineFiltersForCommunityModerator();
        }

        return [$territories, $community];
    }

    private function _defineFiltersForAdmin(): array
    {
        $community = $this->_getOperationalValue(null);

        $territories = $this->_treatTerritoryParams();
        $community = $this->_treatCommunityParamsWithoutCheck();

        return [$territories, $community];
    }

    private function _treatTerritoryParams(): array
    {
        $dashboard = $this->_getDashboard();
        if (!$this->_territoryIdParam && !$this->_forceDefaultTerritoryId) {
            $territories = $this->_getTerritoriesFromAuthItem($dashboard['auth_item']);
        } else {
            if (!$this->_forceDefaultTerritoryId) {
                $territories = [$this->_getOperationalValue($this->_territoryIdParam)];
                $this->_defaultTerritoryId = $this->_territoryIdParam;
            } else {
                $territories = $this->_getTerritoriesFromAuthItem($dashboard['auth_item']);
            }
        }

        return $territories;
    }

    private function _treatCommunityParams(): ?string
    {
        $community = $this->_getOperationalValue($this->_defaultCommunityId);
        if ($this->_communityIdParam && !$this->_forceDefaultCommunityId) {
            $community = $this->_getOperationalValue($this->_communityIdParam);
            $this->_defaultCommunityId = $this->_communityIdParam;
        }

        return $community;
    }

    private function _treatCommunityParamsWithoutCheck(): ?string
    {
        return $this->_treatCommunityParams();
    }

    private function _treatCommunityParamsWithCheckIfAuthorized(): ?string
    {
        $community = $this->_treatCommunityParams();

        if (!$this->_canGetCommunity()) {
            throw new \LogicException('Can get data for this Community. You need to be its referer or a moderator.');
        }

        return $community;
    }

    private function _canGetCommunity(): bool
    {
        if (!$communityEntity = $this->_communityRepository->find($this->_defaultCommunityId)) {
            throw new \LogicException('Unknown Community');
        }
        $user = $this->_tokenStorage->getToken()->getUser();
        if ($communityEntity->getUser()->getId() == $user->getId() || $this->_communityRepository->isModerator($communityEntity, $user)) {
            return true;
        }

        return false;
    }

    private function _defineFiltersForCommunityModerator(): array
    {
        $community = $this->_getOperationalValue(null);

        if ($this->_forceDefaultCommunityId) {
            $this->_getDefaultCommunityId();
            $community = $this->_getOperationalValue($this->_defaultCommunityId);
        } elseif ($this->_communityIdParam && !$this->_forceDefaultCommunityId) {
            $community = $this->_treatCommunityParamsWithCheckIfAuthorized();
        }

        $territories = $this->_treatTerritoryParams();

        return [$territories, $community];
    }

    private function _getDashboard(): ?array
    {
        foreach ($this->_dashboards as $typeDashboard) {
            if (isset($typeDashboard[$this->_paramType])) {
                foreach ($typeDashboard[$this->_paramType] as $dashboard) {
                    if ($dashboard['periodicity'] == $this->_paramPeriodicity) {
                        return $dashboard;
                    }
                }

                break;
            }
        }

        throw new ResourceNotFoundException('Unknown dashboard');
    }

    private function _getDefaultCommunityId(): void
    {
        $this->_defaultCommunityId = null;
        $communityIds = $this->_communityRepository->findCommunitiesForRefererOrModerator($this->_tokenStorage->getToken()->getUser());
        if (is_array($communityIds) && count($communityIds) > 0) {
            $this->_defaultCommunityId = $communityIds[0]['id'];
        }
    }

    private function _getOperationalValue(?int $id): string
    {
        if (null === $id) {
            return $this->_database;
        }

        return $this->_database.'_'.strval($id);
    }

    private function _getTerritoriesFromAuthItem(string $auth_item): array
    {
        $territories = $this->_authManager->getTerritoryListForItem($auth_item);

        if (0 == count($territories)) {
            return [$this->_getOperationalValue(null)];
        }

        foreach ($territories as $key => $territory) {
            if (null == $this->_defaultTerritoryId) {
                $this->_defaultTerritoryId = $territories[$key];
            }
            $territories[$key] = $this->_getOperationalValue($territories[$key]);
        }

        return $territories;
    }

    private function _build_jwt_token($payload): string
    {
        // build the headers
        $headers = ['alg' => 'HS256', 'typ' => 'JWT'];
        $headers_encoded = self::_base64url_encode(json_encode($headers));

        // build the payload
        $payload_encoded = self::_base64url_encode(json_encode($payload));

        // build the signature
        $signature = hash_hmac('sha256', "{$headers_encoded}.{$payload_encoded}", $this->_secret, true);
        $signature_encoded = self::_base64url_encode($signature);

        // build and return the token
        return "{$headers_encoded}.{$payload_encoded}.{$signature_encoded}";
    }

    private function _base64url_encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
