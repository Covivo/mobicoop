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

namespace App\User\Admin\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\PaginationExtension;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\User\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Auth\Service\AuthManager;
use App\MassCommunication\Exception\CampaignException;
use App\MassCommunication\Repository\CampaignRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collection data provider used to associate Users as deliveries for a campaign.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 */
final class UserCampaignAssociateCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $collectionExtensions;
    private $managerRegistry;
    private $request;
    private $campaignRepository;
    private $authManager;

    const MAX_RESULTS = 999999;
    
    public function __construct(RequestStack $requestStack, CampaignRepository $campaignRepository, AuthManager $authManager, ManagerRegistry $managerRegistry, iterable $collectionExtensions)
    {
        $this->collectionExtensions = $collectionExtensions;
        $this->managerRegistry = $managerRegistry;
        $this->request = $requestStack->getCurrentRequest();
        $this->campaignRepository = $campaignRepository;
        $this->authManager = $authManager;
    }
    
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return User::class === $resourceClass && $operationName === "ADMIN_associate_campaign";
    }
    
    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        // check if campaignId is sent
        if (!$this->request->get('campaignId')) {
            throw new CampaignException('Campaign id is mandatory');
        }

        // check if campaign exists
        if (!$campaign = $this->campaignRepository->find($this->request->get('campaignId'))) {
            throw new CampaignException('Campaign not found');
        }

        // check if user is allowed to manage the campaign
        if (!$this->authManager->isAuthorized('campaign_update', ['campaign'=>$campaign])) {
            return new Response('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        $manager = $this->managerRegistry->getManagerForClass($resourceClass);
        $repository = $manager->getRepository($resourceClass);
        $queryBuilder = $repository->createQueryBuilder('u');
        $queryNameGenerator = new QueryNameGenerator();

        $users = [];
        
        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);
            // remove pagination
            if ($extension instanceof PaginationExtension) {
                $queryBuilder->setMaxResults(self::MAX_RESULTS);
            }
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult($resourceClass, $operationName)) {
                $users = $extension->getResult($queryBuilder, $resourceClass, $operationName);
            }
        }

        return [count($users)];
    }
}
