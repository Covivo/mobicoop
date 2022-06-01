<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Community\EventListener;

use App\Community\Entity\Community;
use App\Community\Entity\CommunityUser;
use App\Community\Service\CommunityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * CommunityUser Event listener.
 */
class CommunityLoadListener
{
    private $requestStack;
    private $communityManager;
    private $avatarDefault;

    public function __construct(RequestStack $requestStack, CommunityManager $communityManager, string $avatarDefault)
    {
        $this->requestStack = $requestStack;
        $this->communityManager = $communityManager;
        $this->avatarDefault = $avatarDefault;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        if ($this->requestStack->getCurrentRequest()) {
            $request = $this->requestStack->getCurrentRequest();

            $community = $args->getEntity();
            if ($community instanceof Community) {
                if ($request->get('userId')) {
                    /** @var CommunityUser[] $communityUsers */
                    $communityUsers = $community->getCommunityUsers();
                    foreach ($communityUsers as $communityUser) {
                        if ($request->get('userId') == $communityUser->getUser()->getId()
                            && (CommunityUser::STATUS_ACCEPTED_AS_MEMBER == $communityUser->getStatus() || CommunityUser::STATUS_ACCEPTED_AS_MODERATOR)
                        ) {
                            $community->setMember(true);

                            break;
                        }
                    }
                }
                // Number of members if this community
                $community->setNbMembers(count($community->getCommunityUsers()));

                // Url Key of the community
                $community->setUrlKey($this->communityManager->generateUrlKey($community));

                // default avatar
                $community->setDefaultAvatar($this->avatarDefault);
            }
        }
    }
}
