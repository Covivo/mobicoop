<?php
/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\Gratuity\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Gratuity\Resource\GratuityCampaign;
use App\Gratuity\Service\GratuityCampaignManager;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class GratuityCampaignCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $_gratuityCampaignManager;

    public function __construct(GratuityCampaignManager $gratuityCampaignManager)
    {
        $this->_gratuityCampaignManager = $gratuityCampaignManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return GratuityCampaign::class === $resourceClass && 'get' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        return $this->_gratuityCampaignManager->getGratuityCampaigns();
    }
}
