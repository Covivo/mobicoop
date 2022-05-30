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
use App\Solidary\Entity\SolidaryBeneficiary;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryBeneficiaryVoter extends Voter
{
    const SOLIDARY_BENEFICIARY_CREATE = 'solidary_beneficiary_create';
    const SOLIDARY_BENEFICIARY_READ = 'solidary_beneficiary_read';
    const SOLIDARY_BENEFICIARY_UPDATE = 'solidary_beneficiary_update';
    const SOLIDARY_BENEFICIARY_DELETE = 'solidary_beneficiary_delete';
    const SOLIDARY_BENEFICIARY_LIST = 'solidary_beneficiary_list';
    
    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::SOLIDARY_BENEFICIARY_CREATE,
            self::SOLIDARY_BENEFICIARY_READ,
            self::SOLIDARY_BENEFICIARY_UPDATE,
            self::SOLIDARY_BENEFICIARY_DELETE,
            self::SOLIDARY_BENEFICIARY_LIST
            ])) {
            return false;
        }
      
        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::SOLIDARY_BENEFICIARY_CREATE,
            self::SOLIDARY_BENEFICIARY_READ,
            self::SOLIDARY_BENEFICIARY_UPDATE,
            self::SOLIDARY_BENEFICIARY_DELETE,
            self::SOLIDARY_BENEFICIARY_LIST
            ]) && !($subject instanceof Paginator) &&
                !($subject instanceof SolidaryBeneficiary)
            ) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::SOLIDARY_BENEFICIARY_CREATE:
                return $this->canCreateSolidaryBeneficiary();
            case self::SOLIDARY_BENEFICIARY_READ:
                return $this->canReadSolidaryBeneficiary($subject);
            case self::SOLIDARY_BENEFICIARY_UPDATE:
                return $this->canUpdateSolidaryBeneficiary($subject);
            case self::SOLIDARY_BENEFICIARY_DELETE:
                return $this->canDeleteSolidaryBeneficiary($subject);
            case self::SOLIDARY_BENEFICIARY_LIST:
                return $this->canListSolidaryBeneficiary();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateSolidaryBeneficiary()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_BENEFICIARY_CREATE);
    }

    private function canReadSolidaryBeneficiary(SolidaryBeneficiary $solidaryBeneficiary)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_BENEFICIARY_READ, ['solidaryBeneficiary'=>$solidaryBeneficiary]);
    }

    private function canUpdateSolidaryBeneficiary(SolidaryBeneficiary $solidaryBeneficiary)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_BENEFICIARY_UPDATE, ['solidaryBeneficiary'=>$solidaryBeneficiary]);
    }
    
    private function canDeleteSolidaryBeneficiary(SolidaryBeneficiary $solidaryBeneficiary)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_BENEFICIARY_DELETE, ['solidaryBeneficiary'=>$solidaryBeneficiary]);
    }
    
    private function canListSolidaryBeneficiary()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_BENEFICIARY_LIST);
    }
}
