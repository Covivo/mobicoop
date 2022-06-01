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

namespace App\User\Admin\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\PaginationExtension;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Auth\Service\AuthManager;
use App\MassCommunication\Admin\Service\CampaignManager;
use App\MassCommunication\Entity\Campaign;
use App\MassCommunication\Exception\CampaignException;
use App\MassCommunication\Repository\CampaignRepository;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collection data provider used to associate Users as deliveries for a campaign (depending on the filter type).
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
final class UserCampaignAssociateCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public const MAX_RESULTS = 999999;
    private $collectionExtensions;
    private $managerRegistry;
    private $request;
    private $userRepository;
    private $campaignRepository;
    private $authManager;
    private $campaignManager;

    public function __construct(RequestStack $requestStack, UserRepository $userRepository, CampaignRepository $campaignRepository, AuthManager $authManager, CampaignManager $campaignManager, ManagerRegistry $managerRegistry, iterable $collectionExtensions)
    {
        $this->collectionExtensions = $collectionExtensions;
        $this->managerRegistry = $managerRegistry;
        $this->request = $requestStack->getCurrentRequest();
        $this->userRepository = $userRepository;
        $this->campaignRepository = $campaignRepository;
        $this->authManager = $authManager;
        $this->campaignManager = $campaignManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return User::class === $resourceClass && 'ADMIN_associate_campaign' === $operationName;
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
        if (!$this->authManager->isAuthorized('campaign_update', ['campaign' => $campaign])) {
            return new Response('Unauthorized', Response::HTTP_UNAUTHORIZED);
        }

        // check if filterType is sent
        if (!$this->request->get('filterType')) {
            throw new CampaignException('Filter type is mandatory');
        }

        // check if filterType is valid
        if (!in_array($this->request->get('filterType'), Campaign::FILTER_TYPES)) {
            throw new CampaignException('FilterType is invalid');
        }

        // source is always 1 here
        $campaign->setSource(Campaign::SOURCE_USER);

        // filter type
        $campaign->setFilterType($this->request->get('filterType'));

        $users = [];
        $filters = [];

        switch ($campaign->getFilterType()) {
            case Campaign::FILTER_TYPE_SELECTION:
                // check if user ids are sent
                if (!$this->request->get('user')) {
                    throw new CampaignException('At least one user id is mandatory');
                }
                $users = new \ArrayIterator($this->userRepository->findDeliveriesByIds($this->request->get('user')));

                break;

            case Campaign::FILTER_TYPE_FILTER:
                $manager = $this->managerRegistry->getManagerForClass($resourceClass);
                $repository = $manager->getRepository($resourceClass);

                /**
                 * @var EntityRepository $repository
                 */
                $queryBuilder = $repository->createQueryBuilder('u');
                $queryNameGenerator = new QueryNameGenerator();

                // we force the selection to the users that have accepted the news subscription
                $rootAlias = $queryBuilder->getRootAliases()[0];
                $queryBuilder->andWhere("{$rootAlias}.newsSubscription = 1");

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

                $exclude = ['source', 'filterType', 'campaignId'];
                foreach ($this->request->query->all() as $param => $value) {
                    if (!in_array($param, $exclude)) {
                        $filters[$param] = $value;
                    }
                }

                break;
        }

        // "associate" the users to the campaign (just complete the informations of the campaign, and create deliveries if selection)
        $this->campaignManager->associateUsers($campaign, $users, $filters);

        return [iterator_count($users)];
    }
}
