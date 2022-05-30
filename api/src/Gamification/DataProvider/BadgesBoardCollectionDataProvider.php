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
 **************************/

namespace App\Gamification\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\Gamification\Resource\BadgesBoard;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Security;
use App\Gamification\Service\BadgesBoardManager;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class BadgesBoardCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $security;
    private $reviewManager;
    
    public function __construct(Security $security, BadgesBoardManager $badgesBoardManager)
    {
        $this->security = $security;
        $this->badgesBoardManager = $badgesBoardManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return BadgesBoard::class === $resourceClass && $operationName === "get";
    }

    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        if (!($this->security->getUser() instanceof User)) {
            throw new \LogicException("Only a User can get its board");
        }
        return $this->badgesBoardManager->getBadgesBoard($this->security->getUser());
    }
}
