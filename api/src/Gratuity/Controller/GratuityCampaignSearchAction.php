<?php

/**
 * Copyright (c) 2026, MOBICOOP. All rights reserved.
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

namespace App\Gratuity\Controller;

use App\Gratuity\Service\GratuityCampaignActionManager;
use App\Gratuity\Service\GratuityCampaignManager;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

final class GratuityCampaignSearchAction
{
    private $_gratuityCampaignActionManager;
    private $_gratuityCampaignManager;
    private $_request;
    private $_security;
    private $_serializer;

    public function __construct(
        GratuityCampaignActionManager $gratuityCampaignActionManager,
        GratuityCampaignManager $gratuityCampaignManager,
        RequestStack $requestStack,
        Security $security,
        SerializerInterface $serializer
    ) {
        $this->_gratuityCampaignActionManager = $gratuityCampaignActionManager;
        $this->_gratuityCampaignManager = $gratuityCampaignManager;
        $this->_request = $requestStack->getCurrentRequest();
        $this->_security = $security;
        $this->_serializer = $serializer;
    }

    public function __invoke(): JsonResponse
    {
        $content = json_decode($this->_request->getContent(), true);

        if (!isset($content['addresses']) || !is_array($content['addresses'])) {
            return new JsonResponse(['gratuityCampaigns' => []]);
        }

        $entities = $this->_gratuityCampaignActionManager->findCampaignsByAddresses(
            $content['addresses'],
            $this->_security->getUser()
        );

        $campaigns = [];
        foreach ($entities as $entity) {
            $campaigns[] = $this->_gratuityCampaignManager->buildGratuityCampaignFromEntity($entity);
        }

        $json = $this->_serializer->serialize(
            ['gratuityCampaigns' => $campaigns],
            'json',
            ['groups' => ['readGratuity', 'readGratuityNotified']]
        );

        return new JsonResponse($json, 200, [], true);
    }
}
