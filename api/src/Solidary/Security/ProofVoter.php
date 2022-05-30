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
use App\Solidary\Entity\Proof;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ProofVoter extends Voter
{
    const PROOF_CREATE = 'proof_create';
    const PROOF_READ = 'proof_read';
    const PROOF_UPDATE = 'proof_update';
    const PROOF_DELETE = 'proof_delete';
    const PROOF_LIST = 'proof_list';
    
    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::PROOF_CREATE,
            self::PROOF_READ,
            self::PROOF_UPDATE,
            self::PROOF_DELETE,
            self::PROOF_LIST,
            ])) {
            return false;
        }
      
        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::PROOF_CREATE,
            self::PROOF_READ,
            self::PROOF_UPDATE,
            self::PROOF_DELETE,
            self::PROOF_LIST,
            ]) && !($subject instanceof Paginator) && !($subject instanceof Proof)) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::PROOF_CREATE:
                return $this->canCreateProof();
            case self::PROOF_READ:
                return $this->canReadProof($subject);
            case self::PROOF_UPDATE:
                return $this->canUpdateProof($subject);
            case self::PROOF_DELETE:
                return $this->canDeleteProof($subject);
            case self::PROOF_LIST:
                return $this->canListProof();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateProof()
    {
        return $this->authManager->isAuthorized(self::PROOF_CREATE);
    }

    private function canReadProof(Proof $proof)
    {
        return $this->authManager->isAuthorized(self::PROOF_READ, ['proof'=>$proof]);
    }

    private function canUpdateProof(Proof $proof)
    {
        return $this->authManager->isAuthorized(self::PROOF_UPDATE, ['proof'=>$proof]);
    }
    
    private function canDeleteProof(Proof $proof)
    {
        return $this->authManager->isAuthorized(self::PROOF_DELETE, ['proof'=>$proof]);
    }
    
    private function canListProof()
    {
        return $this->authManager->isAuthorized(self::PROOF_LIST);
    }
}
