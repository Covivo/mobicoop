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

namespace App\Solidary\Security;

use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Solidary\Entity\SolidaryAnimation;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryAnimationVoter extends Voter
{
    const SOLIDARY_ANIMATION_CREATE = 'solidary_animation_create';
    const SOLIDARY_ANIMATION_LIST = 'solidary_animation_list';
    
    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::SOLIDARY_ANIMATION_CREATE,
            self::SOLIDARY_ANIMATION_LIST,
            ])) {
            return false;
        }
      
        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::SOLIDARY_ANIMATION_CREATE,
            self::SOLIDARY_ANIMATION_LIST,
            ]) && !($subject instanceof Paginator) &&
                !($subject instanceof SolidaryAnimation)
            ) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::SOLIDARY_ANIMATION_CREATE:
                return $this->canCreateSolidaryAnimation();
            case self::SOLIDARY_ANIMATION_LIST:
                return $this->canListSolidaryAnimation();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateSolidaryAnimation()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_ANIMATION_CREATE);
    }

    private function canListSolidaryAnimation()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_ANIMATION_LIST);
    }
}
