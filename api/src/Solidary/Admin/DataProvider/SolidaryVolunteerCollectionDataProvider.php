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

namespace App\Solidary\Admin\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Solidary\Admin\Service\SolidaryVolunteerManager;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Entity\SolidaryVolunteer;
use App\User\Entity\User;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Get the solidary volunteers in admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
final class SolidaryVolunteerCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $collectionExtensions;
    private $managerRegistry;
    private $solidaryVolunteerManager;

    public function __construct(ManagerRegistry $managerRegistry, iterable $collectionExtensions, SolidaryVolunteerManager $solidaryVolunteerManager)
    {
        $this->collectionExtensions = $collectionExtensions;
        $this->managerRegistry = $managerRegistry;
        $this->solidaryVolunteerManager = $solidaryVolunteerManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return SolidaryVolunteer::class === $resourceClass && 'ADMIN_get' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        // overload filters to avoid using complicated linked relations !
        $newContext = $context;
        if (isset($context['filters'])) {
            foreach ($context['filters'] as $fkey => $filter) {
                switch ($fkey) {
                    case 'order':
                        foreach ($filter as $key => $value) {
                            switch ($key) {
                                case 'givenName':
                                    $newContext['filters']['order']['user.givenName'] = $value;
                                    unset($newContext['filters']['order']['givenName']);

                                    break;

                                case 'familyName':
                                    $newContext['filters']['order']['user.familyName'] = $value;
                                    unset($newContext['filters']['order']['familyName']);

                                    break;
                            }
                        }

                        break;

                    case 'givenName':
                        $newContext['filters']['user.givenName'] = $filter;
                        unset($newContext['filters']['givenName']);

                        break;

                    case 'familyName':
                        $newContext['filters']['user.familyName'] = $filter;
                        unset($newContext['filters']['familyName']);

                        break;

                    default:
                        break;
                }
            }
        }

        // we get SolidaryUsers => we need to keep only volunteers and return an array of SolidayVolunteer objects
        $solidaryUsers = [];

        // we use the doctrine built-in filtering and pagination system
        // (on the parent SolidaryUser class, as we need to perform queries on an ORM table and not a resource only like SolidayVolunteer)
        $manager = $this->managerRegistry->getManagerForClass(SolidaryUser::class);

        /**
         * @var EntityRepository $repository
         */
        $repository = $manager->getRepository(SolidaryUser::class);
        $queryBuilder = $repository->createQueryBuilder('s');
        $queryNameGenerator = new QueryNameGenerator();

        // we add the volunteer flag to keep only volunteers (and not beneficiaries)
        $rootAlias = $queryBuilder->getRootAliases()[0];

        $queryBuilder
            ->join("{$rootAlias}.user", 'u')
            ->andWhere('u.status != :pseudonymisedStatus')
            ->andWhere("{$rootAlias}.volunteer = 1")
            ->setParameters([
                'pseudonymisedStatus' => User::STATUS_PSEUDONYMIZED,
            ])
        ;

        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($queryBuilder, $queryNameGenerator, SolidaryUser::class, $operationName, $newContext);
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult(SolidaryUser::class, $operationName)) {
                $solidaryUsers = $extension->getResult($queryBuilder, SolidaryUser::class, $operationName);
            }
        }

        // we now have the SolidayUser array, transform to a SolidaryVolunteer array and return it
        return $this->solidaryVolunteerManager->getSolidaryVolunteers($solidaryUsers);
    }
}
