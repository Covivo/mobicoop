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

namespace App\Community\EventListener;

use App\Auth\Entity\AuthItem;
use App\Auth\Entity\UserAuthAssignment;
use App\Auth\Repository\AuthItemRepository;
use App\Community\Entity\CommunityUser;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * CommunityUser Event save listener.
 */
class CommunityUserSaveListener
{
    /**
     * @var RequestStack
     */
    private $requestStack;
    private $userManager;
    private $authItemRepository;
    private $entityManager;

    /**
     * CommunityUserSaveListener constructor.
     */
    public function __construct(RequestStack $requestStack, UserManager $userManager, AuthItemRepository $authItemRepository, EntityManagerInterface $entityManager)
    {
        $this->requestStack = $requestStack;
        $this->userManager = $userManager;
        $this->authItemRepository = $authItemRepository;
        $this->entityManager = $entityManager;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $communityUser = $args->getEntity();
        if ($communityUser instanceof CommunityUser) {
            $this->checkRole($communityUser);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $communityUser = $args->getEntity();
        if ($communityUser instanceof CommunityUser) {
            $this->checkRole($communityUser);
        }
    }

    /**
     * Check if a community user has the appropriate role
     *
     * @param CommunityUser $communityUser  The community user
     * @return void
     */
    private function checkRole(CommunityUser $communityUser)
    {
        if (CommunityUser::STATUS_ACCEPTED_AS_MODERATOR === $communityUser->getStatus()) {
            // check if user has the appropriate role
            $authItem = $this->authItemRepository->find(AuthItem::ROLE_COMMUNITY_MANAGER_PUBLIC);
            if (!$this->userManager->checkUserHaveAuthItem($communityUser->getUser(), $authItem)) {
                $userAuthAssignment = new UserAuthAssignment();
                $userAuthAssignment->setAuthItem($authItem);
                $communityUser->getUser()->addUserAuthAssignment($userAuthAssignment);
                $this->entityManager->persist($communityUser->getUser());
                $this->entityManager->flush();
            }
        }
    }
}
