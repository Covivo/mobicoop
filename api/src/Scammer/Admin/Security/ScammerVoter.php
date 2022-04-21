<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

namespace App\Scammer\Admin\Security;

use App\Auth\Service\AuthManager;
use App\Scammer\Entity\Scammer;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ScammerVoter extends Voter
{
    public const ADMIN_SCAMMER_MANAGE = 'admin_scammer_manage';
    public const SCAMMER_MANAGE = 'scammer_manage';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ADMIN_SCAMMER_MANAGE,
        ])) {
            return false;
        }

        // only vote on Scammer objects inside this voter
        if (!in_array($attribute, [
            self::ADMIN_SCAMMER_MANAGE,
        ]) && !($subject instanceof Scammer)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::ADMIN_SCAMMER_MANAGE:
                return $this->canManageScammer();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canManageScammer()
    {
        return $this->authManager->isAuthorized(self::SCAMMER_MANAGE);
    }
}
