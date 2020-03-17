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

namespace App\Carpool\Security;

use App\Auth\Service\AuthManager;
use App\Carpool\Entity\DynamicAsk;
use App\Carpool\Entity\Matching;
use App\Carpool\Repository\MatchingRepository;
use App\Carpool\Service\DynamicManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class DynamicAskVoter extends Voter
{
    const DYNAMIC_ASK_CREATE = 'dynamic_ask_create';
    const DYNAMIC_ASK_READ = 'dynamic_ask_read';
    const DYNAMIC_ASK_UPDATE = 'dynamic_ask_update';
    const DYNAMIC_ASK_DELETE = 'dynamic_ask_delete';
    
    private $security;
    private $request;
    private $authManager;
    private $dynamicManager;
    private $matchingRepository;

    public function __construct(RequestStack $requestStack, Security $security, AuthManager $authManager, DynamicManager $dynamicManager, MatchingRepository $matchingRepository)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->security = $security;
        $this->authManager = $authManager;
        $this->dynamicManager = $dynamicManager;
        $this->matchingRepository = $matchingRepository;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::DYNAMIC_ASK_CREATE,
            self::DYNAMIC_ASK_READ,
            self::DYNAMIC_ASK_UPDATE,
            self::DYNAMIC_ASK_DELETE
            ])) {
            return false;
        }

        // Dynamic is a 'virtual' resource, we can't check its class
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::DYNAMIC_ASK_CREATE:
                /**
                 * @var DynamicAsk $subject
                 */
                if ($matching = $this->matchingRepository->find($subject->getMatchingId())) {
                    return $this->canCreateDynamicAsk($matching);
                }
            // case self::DYNAMIC_ASK_READ:
            //     if ($dynamic = $this->dynamicManager->getDynamic($this->request->get('id'))) {
            //         return $this->canReadDynamicAsk($dynamic);
            //     }
            //     return false;
            // case self::DYNAMIC_ASK_UPDATE:
            //     if ($dynamic = $this->dynamicManager->getDynamic($this->request->get('id'))) {
            //         return $this->canUpdateDynamicAsk($dynamic);
            //     }
            //     return false;
            // case self::DYNAMIC_ASK_DELETE:
            //     if ($dynamic = $this->dynamicManager->getDynamic($this->request->get('id'))) {
            //         return $this->canDeleteDynamicAsk($dynamic);
            //     }
            //     return false;
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateDynamicAsk(Matching $matching)
    {
        return $this->authManager->isAuthorized(self::DYNAMIC_ASK_CREATE, ['matching' => $matching]);
    }

    // private function canReadDynamicAsk(Dynamic $dynamic)
    // {
    //     return $this->authManager->isAuthorized(self::DYNAMIC_ASK_READ, ['dynamic' => $dynamic]);
    // }

    // private function canUpdateDynamicAsk(Dynamic $dynamic)
    // {
    //     return $this->authManager->isAuthorized(self::DYNAMIC_ASK_UPDATE, ['dynamic' => $dynamic]);
    // }

    // private function canDeleteDynamicAsk(Dynamic $dynamic)
    // {
    //     return $this->authManager->isAuthorized(self::DYNAMIC_ASK_DELETE, ['dynamic' => $dynamic]);
    // }
}
