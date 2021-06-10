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

namespace App\Community\Admin\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\OrderExtension;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\PaginationExtension;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Community\Entity\CommunityUser;
use Doctrine\Common\Persistence\ManagerRegistry;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use App\Auth\Service\AuthManager;
use App\Community\Repository\CommunityRepository;
use App\Community\Repository\CommunityUserRepository;
use App\MassCommunication\Admin\Service\CampaignManager;
use App\MassCommunication\Entity\Campaign;
use App\MassCommunication\Exception\CampaignException;
use App\MassCommunication\Repository\CampaignRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Collection data provider used to associate Community Users as deliveries for a campaign (depending on the filter type).
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 */
final class CommunityUserCampaignAssociateCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $collectionExtensions;
    private $managerRegistry;
    private $request;
    private $communityUserRepository;
    private $campaignRepository;
    private $communityRepository;
    private $authManager;
    private $campaignManager;

    const MAX_RESULTS = 999999;
    
    public function __construct(RequestStack $requestStack, CommunityUserRepository $communityUserRepository, CampaignRepository $campaignRepository, CommunityRepository $communityRepository, AuthManager $authManager, CampaignManager $campaignManager, ManagerRegistry $managerRegistry, iterable $collectionExtensions)
    {
        $this->collectionExtensions = $collectionExtensions;
        $this->managerRegistry = $managerRegistry;
        $this->request = $requestStack->getCurrentRequest();
        $this->communityUserRepository = $communityUserRepository;
        $this->communityRepository = $communityRepository;
        $this->campaignRepository = $campaignRepository;
        $this->authManager = $authManager;
        $this->campaignManager = $campaignManager;
    }
    
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return CommunityUser::class === $resourceClass && $operationName === "ADMIN_associate_campaign";
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

        // check if communityId is sent
        if (!$this->request->get('communityId')) {
            throw new CampaignException('Community id is mandatory');
        }

        // check if community exists
        if (!$community = $this->communityRepository->find($this->request->get('communityId'))) {
            throw new CampaignException('Community not found');
        }

        // check if filterType is sent
        if (!$this->request->get('filterType')) {
            throw new CampaignException('Filter type is mandatory');
        }

        // check if filterType is valid
        if (!in_array($this->request->get('filterType'), Campaign::FILTER_TYPES)) {
            throw new CampaignException('FilterType is invalid');
        }

        // source is always 2 here
        $campaign->setSource(Campaign::SOURCE_COMMUNITY);
        $campaign->setSourceId($community->getId());
        $campaign->setSourceName($community->getName());
        
        // filter type
        $campaign->setFilterType($this->request->get('filterType'));

        $members = [];
        $filters= [];

        switch ($campaign->getFilterType()) {
            case Campaign::FILTER_TYPE_SELECTION:
                // check if member ids are sent
                if (!$this->request->get('member')) {
                    throw new CampaignException('At least one member id is mandatory');
                }
                $members = $this->communityUserRepository->findAcceptedDeliveriesByIds($this->request->get('member'));
                break;
            case Campaign::FILTER_TYPE_FILTER:
                $manager = $this->managerRegistry->getManagerForClass($resourceClass);
                $repository = $manager->getRepository($resourceClass);
                /**
                 * @var EntityRepository $repository
                 */
                $queryBuilder = $repository->createQueryBuilder('cu');
                $queryNameGenerator = new QueryNameGenerator();

                // we force the selection to the accepted members that have accepted the news subscription
                $rootAlias = $queryBuilder->getRootAliases()[0];
                $queryBuilder->join("$rootAlias.user", 'u');
                $queryBuilder->andWhere("$rootAlias.community = :community and u.newsSubscription = 1 and $rootAlias.status IN (:statuses)")
                ->setParameter('statuses', [CommunityUser::STATUS_ACCEPTED_AS_MEMBER,CommunityUser::STATUS_ACCEPTED_AS_MODERATOR])
                ->setParameter('community', $community);
                
                foreach ($this->collectionExtensions as $extension) {
                    $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $context);
                    // remove pagination
                    if ($extension instanceof PaginationExtension) {
                        $queryBuilder->setMaxResults(self::MAX_RESULTS);
                    }
                    if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult($resourceClass, $operationName)) {
                        $members = $extension->getResult($queryBuilder, $resourceClass, $operationName);
                    }
                }
                $exclude = ['source','filterType','campaignId','communityId'];
                foreach ($this->request->query->all() as $param=>$value) {
                    if (!in_array($param, $exclude)) {
                        $filters[$param] = $value;
                    }
                }
                break;
        }

        // "associate" the users to the campaign (just complete the informations of the campaign, and create deliveries if selection)
        $this->campaignManager->associateCommunityUsers($campaign, $members, $filters);

        return [count($members)];
    }
}
