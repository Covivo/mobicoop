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

namespace App\Utility\Security;

use App\Auth\Service\AuthManager;
use App\Utility\Entity\Version;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class VersionVoter extends Voter
{
    const VERSION_READ = 'app_versioning_read';
    
    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::VERSION_READ
            ])) {
            return false;
        }

        // only vote on Version objects inside this voter
        // if (!in_array($attribute, [
        //     self::VERSION_READ
        //     ]) && !($subject instanceof Version)
        //     ) {
        //     return false;
        // }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::VERSION_READ:
                return $this->canReadVersion();
        }

        throw new \LogicException('This code should not be reached!');
    }
    
    private function canReadVersion()
    {
        return $this->authManager->isAuthorized(self::VERSION_READ);
    }
}
