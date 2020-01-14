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

namespace App\User\Security;

use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\User\Entity\User;
use Symfony\Component\Security\Core\Security;

class UserVoter extends Voter
{
    const READ = 'read';

    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::READ
            ])) {
            return false;
        }
        
        // only vote on User objects inside this voter
        if (!$subject instanceof User) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        var_dump($this->security->getToken());
        exit;
        switch ($attribute) {
            case self::READ:
                if ($this->security->isGranted('ROLE_ADMIN')) {
                    return true;
                }
        }
        $user = $token->getUser();
        // $ad = $subject;

        return true;
        // switch ($attribute) {
        //     case self::CREATE_AD:
        //         return $this->canCreateAd();
        //     case self::DELETE_AD:
        //         return $this->canDeleteAd($ad, $user);
        //     case self::POST:
        //         return $this->canPostAd($user);
        //     case self::POST_DELEGATE:
        //         return $this->canPostDelegateAd($user);
        //     case self::RESULTS:
        //         return $this->canViewAdResults($ad, $user);
        // }

        throw new \LogicException('This code should not be reached!');
    }
}
