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
 */

namespace App\MassCommunication\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\MassCommunication\Admin\Service\CampaignManager;
use App\MassCommunication\Entity\Campaign;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Data provider for Campaign unsubscribe hook
 * Used to manage unsubscribe webhook sent by campaign providers.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
final class UnsubscribeHookCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $request;
    private $campaignManager;

    public function __construct(RequestStack $requestStack, CampaignManager $campaignManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->campaignManager = $campaignManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Campaign::class === $resourceClass && isset($context['collection_operation_name']) && 'unsubscribeHook' == $context['collection_operation_name'];
    }

    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        return $this->campaignManager->handleUnsubscribeHook($this->request);
    }
}
