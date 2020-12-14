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

namespace App\Community\Admin\Service;

use App\Community\Exception\CommunityException;
use Doctrine\ORM\EntityManagerInterface;
use App\Community\Repository\CommunityRepository;
use App\Community\Repository\CommunityUserRepository;

/**
 * Community manager for admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class CommunityManager
{
    private $entityManager;
    private $communityUserRepository;
    private $communityRepository;

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        CommunityRepository $communityRepository,
        CommunityUserRepository $communityUserRepository
    ) {
        $this->entityManager = $entityManager;
        $this->communityUserRepository = $communityUserRepository;
        $this->communityRepository = $communityRepository;
    }

    /**
     * Get the community members
     *
     * @param integer $communityId  The community id
     * @return array    The members
     */
    public function getMembers(int $communityId, array $context = [], string $operationName)
    {
        if ($community = $this->communityRepository->find($communityId)) {
            return $this->communityUserRepository->findForCommunity($community, $context, $operationName);
        }
        throw new CommunityException("Community not found");
    }
}
