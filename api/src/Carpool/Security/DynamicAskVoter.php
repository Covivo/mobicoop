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
use App\Carpool\Ressource\DynamicAsk;
use App\Carpool\Entity\Matching;
use App\Carpool\Repository\MatchingRepository;
use App\Carpool\Service\DynamicManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class DynamicAskVoter extends Voter
{
    public const DYNAMIC_ASK_CREATE = 'dynamic_ask_create';
    public const DYNAMIC_ASK_READ = 'dynamic_ask_read';
    public const DYNAMIC_ASK_UPDATE = 'dynamic_ask_update';

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

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::DYNAMIC_ASK_CREATE,
            self::DYNAMIC_ASK_READ,
            self::DYNAMIC_ASK_UPDATE,
            ])) {
            return false;
        }

        // Dynamic is a 'virtual' resource, we can't check its class
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::DYNAMIC_ASK_CREATE:
                /**
                 * @var DynamicAsk $subject
                 */
                if ($matching = $this->matchingRepository->find($subject->getMatchingId())) {
                    return $this->canCreateDynamicAsk($matching);
                }
                return false;
            case self::DYNAMIC_ASK_READ:
                if ($dynamicAsk = $this->dynamicManager->getDynamicAsk($this->request->get('id'))) {
                    return $this->canReadDynamicAsk($dynamicAsk);
                }
                return false;
            case self::DYNAMIC_ASK_UPDATE:
                if ($dynamicAsk = $this->dynamicManager->getDynamicAsk($this->request->get('id'))) {
                    return $this->canUpdateDynamicAsk($dynamicAsk);
                }
                return false;
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateDynamicAsk(Matching $matching)
    {
        return $this->authManager->isAuthorized(self::DYNAMIC_ASK_CREATE, ['matching' => $matching]);
    }

    private function canReadDynamicAsk(DynamicAsk $dynamicAsk)
    {
        return $this->authManager->isAuthorized(self::DYNAMIC_ASK_READ, ['dynamicAsk' => $dynamicAsk]);
    }

    private function canUpdateDynamicAsk(DynamicAsk $dynamicAsk)
    {
        return $this->authManager->isAuthorized(self::DYNAMIC_ASK_UPDATE, ['dynamicAsk' => $dynamicAsk]);
    }
}
