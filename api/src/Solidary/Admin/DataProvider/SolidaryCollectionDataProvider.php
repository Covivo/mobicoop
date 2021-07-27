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

namespace App\Solidary\Admin\DataProvider;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryResultCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Solidary\Admin\Service\SolidaryManager;
use App\Solidary\Entity\Solidary;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * Get the solidary records in admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
final class SolidaryCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $collectionExtensions;
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry, iterable $collectionExtensions, SolidaryManager $solidaryManager)
    {
        $this->collectionExtensions = $collectionExtensions;
        $this->managerRegistry = $managerRegistry;
        $this->solidaryManager = $solidaryManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Solidary::class === $resourceClass && $operationName === 'ADMIN_get';
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        // overload filters to avoid using complicated linked relations !
        $newContext = $context;
        if (isset($context['filters'])) {
            foreach ($context['filters'] as $fkey=>$filter) {
                switch ($fkey) {
                    case 'order':
                        foreach ($filter as $key=>$value) {
                            switch ($key) {
                                case 'givenName':
                                    $newContext['filters']['order']['solidaryUserStructure.solidaryUser.user.givenName'] = $value;
                                    unset($newContext['filters']['order']['givenName']);
                                    break;
                                case 'familyName':
                                    $newContext['filters']['order']['solidaryUserStructure.solidaryUser.user.familyName'] = $value;
                                    unset($newContext['filters']['order']['familyName']);
                                    break;
                                case 'telephone':
                                    $newContext['filters']['order']['solidaryUserStructure.solidaryUser.user.telephone'] = $value;
                                    unset($newContext['filters']['order']['telephone']);
                                    break;
                                case 'subject':
                                    $newContext['filters']['order']['subject.label']= $value;
                                    unset($newContext['filters']['order']['subject']);
                                    break;
                                case 'fromDate':
                                    $newContext['filters']['order']['proposal.criteria.fromDate']= $value;
                                    unset($newContext['filters']['order']['fromDate']);
                                    break;
                            }
                        }
                        break;
                    case 'givenName':
                        $newContext['filters']['solidaryUserStructure.solidaryUser.user.givenName'] = $filter;
                        unset($newContext['filters']['givenName']);
                        break;
                    case 'familyName':
                        $newContext['filters']['solidaryUserStructure.solidaryUser.user.familyName'] = $filter;
                        unset($newContext['filters']['familyName']);
                        break;
                    case 'progression':
                        // progression filter is rewritten from "progression equals xx" to "progression lower than xx"
                        $newContext['filters']['progression'] = ['lt'=>$filter];
                        // no break
                    default:
                        break;
                }
            }
        }

        $solidaries = [];
        $manager = $this->managerRegistry->getManagerForClass($resourceClass);
        /**
         * @var EntityRepository $repository
         */
        $repository = $manager->getRepository($resourceClass);
        $queryBuilder = $repository->createQueryBuilder('s');
        $queryNameGenerator = new QueryNameGenerator();

        // we limit to the solidary records that have not been closed for edition
        $rootAlias = $queryBuilder->getRootAliases()[0];
        $queryBuilder
            ->andWhere("$rootAlias.status != :status")
            ->setParameter('status', Solidary::STATUS_CLOSED_FOR_EDITION);
        
        foreach ($this->collectionExtensions as $extension) {
            $extension->applyToCollection($queryBuilder, $queryNameGenerator, $resourceClass, $operationName, $newContext);
            if ($extension instanceof QueryResultCollectionExtensionInterface && $extension->supportsResult($resourceClass, $operationName)) {
                $solidaries = $extension->getResult($queryBuilder, $resourceClass, $operationName);
            }
        }

        return $this->solidaryManager->getSolidaries($solidaries);
    }
}
