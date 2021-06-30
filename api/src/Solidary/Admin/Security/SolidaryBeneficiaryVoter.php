<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\Solidary\Admin\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Solidary\Entity\SolidaryBeneficiary;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Repository\SolidaryUserRepository;
use Symfony\Component\HttpFoundation\RequestStack;

class SolidaryBeneficiaryVoter extends Voter
{
    const ADMIN_SOLIDARY_BENEFICIARY_CREATE = 'admin_solidary_beneficiary_create';
    const ADMIN_SOLIDARY_BENEFICIARY_READ = 'admin_solidary_beneficiary_read';
    const ADMIN_SOLIDARY_BENEFICIARY_UPDATE = 'admin_solidary_beneficiary_update';
    const ADMIN_SOLIDARY_BENEFICIARY_DELETE = 'admin_solidary_beneficiary_delete';
    const ADMIN_SOLIDARY_BENEFICIARY_LIST = 'admin_solidary_beneficiary_list';
    const SOLIDARY_BENEFICIARY_CREATE = 'solidary_beneficiary_create';
    const SOLIDARY_BENEFICIARY_READ = 'solidary_beneficiary_read';
    const SOLIDARY_BENEFICIARY_UPDATE = 'solidary_beneficiary_update';
    const SOLIDARY_BENEFICIARY_DELETE = 'solidary_beneficiary_delete';
    const SOLIDARY_BENEFICIARY_LIST = 'solidary_beneficiary_list';

    private $authManager;
    private $solidaryUserRepository;
    private $request;
 
    public function __construct(RequestStack $requestStack, AuthManager $authManager, SolidaryUserRepository $solidaryUserRepository)
    {
        $this->authManager = $authManager;
        $this->solidaryUserRepository = $solidaryUserRepository;
        $this->request = $requestStack->getCurrentRequest();
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ADMIN_SOLIDARY_BENEFICIARY_CREATE,
            self::ADMIN_SOLIDARY_BENEFICIARY_READ,
            self::ADMIN_SOLIDARY_BENEFICIARY_UPDATE,
            self::ADMIN_SOLIDARY_BENEFICIARY_DELETE,
            self::ADMIN_SOLIDARY_BENEFICIARY_LIST
            ])) {
            return false;
        }

        // only vote on Structure objects inside this voter
        if (!in_array($attribute, [
            self::ADMIN_SOLIDARY_BENEFICIARY_CREATE,
            self::ADMIN_SOLIDARY_BENEFICIARY_READ,
            self::ADMIN_SOLIDARY_BENEFICIARY_UPDATE,
            self::ADMIN_SOLIDARY_BENEFICIARY_DELETE,
            self::ADMIN_SOLIDARY_BENEFICIARY_LIST
            ]) && !($subject instanceof Paginator) && !($subject instanceof SolidaryBeneficiary)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (is_null($subject)) {
            $subject = $this->solidaryUserRepository->find($this->request->get('id'));
        }
        switch ($attribute) {
            case self::ADMIN_SOLIDARY_BENEFICIARY_CREATE:
                return $this->canCreateSolidaryBeneficiary();
            case self::ADMIN_SOLIDARY_BENEFICIARY_READ:
                return $this->canReadSolidaryBeneficiary($subject);
            case self::ADMIN_SOLIDARY_BENEFICIARY_UPDATE:
                return $this->canUpdateSolidaryBeneficiary($subject);
            case self::ADMIN_SOLIDARY_BENEFICIARY_DELETE:
                return $this->canDeleteSolidaryBeneficiary($subject);
            case self::ADMIN_SOLIDARY_BENEFICIARY_LIST:
                return $this->canListSolidaryBeneficiary();
        }
        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateSolidaryBeneficiary()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_BENEFICIARY_CREATE);
    }

    private function canReadSolidaryBeneficiary(SolidaryUser $solidaryBeneficiary)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_BENEFICIARY_READ, ['solidaryBeneficiary'=>$solidaryBeneficiary]);
    }

    private function canUpdateSolidaryBeneficiary(SolidaryUser $solidaryBeneficiary)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_BENEFICIARY_UPDATE, ['solidaryBeneficiary'=>$solidaryBeneficiary]);
    }

    private function canDeleteSolidaryBeneficiary(SolidaryUser $solidaryBeneficiary)
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_BENEFICIARY_DELETE, ['solidaryBeneficiary'=>$solidaryBeneficiary]);
    }

    private function canListSolidaryBeneficiary()
    {
        return $this->authManager->isAuthorized(self::SOLIDARY_BENEFICIARY_LIST);
    }
}
