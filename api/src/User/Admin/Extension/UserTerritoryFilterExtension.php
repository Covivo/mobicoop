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

namespace App\User\Admin\Extension;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\App\Entity\App;
use App\Auth\Service\AuthManager;
use App\User\Entity\User;
use App\User\Repository\UserRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\Security;

/**
 *  Extension used to add an automatic filter to the admin get collection request
 *  In admin, one can only manage and see the users that belong to its territories
 *  We check the user's home address, and the origin / destination of its proposals.
 */
final class UserTerritoryFilterExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    private $security;
    private $authManager;
    private $_userRepository;

    public function __construct(Security $security, AuthManager $authManager, UserRepository $userRepository)
    {
        $this->security = $security;
        $this->authManager = $authManager;
        $this->_userRepository = $userRepository;
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?string $operationName = null)
    {
        if (User::class == $resourceClass && in_array($operationName, ['ADMIN_get', 'ADMIN_associate_campaign', 'ADMIN_send_campaign'])) {
            $this->addWhere($queryBuilder, $resourceClass, false, $operationName);
        }
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?string $operationName = null, array $context = [])
    {
        if (User::class == $resourceClass && 'ADMIN_get' == $operationName) {
            $this->addWhere($queryBuilder, $resourceClass, true, $operationName, $identifiers, $context);
        }
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass, bool $isItem, ?string $operationName = null, array $identifiers = [], array $context = []): void
    {
        // concerns only User resource, and User users (not Apps)
        if ((null === $this->security->getUser()) || $this->security->getUser() instanceof App) {
            return;
        }

        $territories = [];

        // we check if the user has limited territories
        if ($isItem) {
        } else {
            switch ($operationName) {
                case 'ADMIN_get':
                case 'ADMIN_associate_campaign':
                case 'ADMIN_send_campaign':
                    $territories = $this->authManager->getTerritoriesForItem('user_list');
            }
        }

        if (count($territories) > 0) {
            $users = [];
            foreach ($territories as $territory) {
                $homeAddresses = $this->_userRepository->findByHomeAddress($territory);
                if (!is_null($homeAddresses) && count($homeAddresses) > 0) {
                    foreach ($homeAddresses as $homeAddresse) {
                        if (!in_array($homeAddresse['user_id'], $users)) {
                            $users[] = $homeAddresse['user_id'];
                        }
                    }
                }

                $proposalOriginAddresses = $this->_userRepository->findByProposalOriginTerritory($territory);
                if (!is_null($proposalOriginAddresses) && count($proposalOriginAddresses) > 0) {
                    foreach ($proposalOriginAddresses as $proposalOriginAddresse) {
                        if (!in_array($proposalOriginAddresse['user_id'], $users)) {
                            $users[] = $proposalOriginAddresse['user_id'];
                        }
                    }
                }

                $proposalDestinationAddresses = $this->_userRepository->findByProposalDestinationTerritory($territory);
                if (!is_null($proposalDestinationAddresses) && count($proposalDestinationAddresses) > 0) {
                    foreach ($proposalDestinationAddresses as $proposalDestinationAddresse) {
                        if (!in_array($proposalDestinationAddresse['user_id'], $users)) {
                            $users[] = $proposalDestinationAddresse['user_id'];
                        }
                    }
                }
            }

            $rootAlias = $queryBuilder->getRootAliases()[0];

            $queryBuilder
                ->andWhere($rootAlias.'.id in (:usersFilteredByTerritory)')
                ->setParameter('usersFilteredByTerritory', $users)
            ;
        }
    }
}
