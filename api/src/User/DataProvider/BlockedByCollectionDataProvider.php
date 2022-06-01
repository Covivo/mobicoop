<?php
/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\User\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\User\Entity\User;
use App\User\Ressource\Block;
use App\User\Service\BlockManager;
use Symfony\Component\Security\Core\Security;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class BlockedByCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $security;
    private $blockManager;

    public function __construct(Security $security, BlockManager $blockManager)
    {
        $this->security = $security;
        $this->blockManager = $blockManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Block::class === $resourceClass && 'blockedBy' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        $user = null;
        // If it's a user who make the call, we use it
        if ($this->security->getUser() instanceof User) {
            $user = $this->security->getUser();
        }

        return $this->blockManager->getBlockedByUsers($user);
    }
}
