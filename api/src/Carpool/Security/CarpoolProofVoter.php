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
use App\Carpool\Entity\Ask;
use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Ressource\ClassicProof;
use App\Carpool\Repository\AskRepository;
use App\Carpool\Repository\CarpoolProofRepository;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

class CarpoolProofVoter extends Voter
{
    const CARPOOL_PROOF_CREATE = 'carpool_proof_create';
    const CARPOOL_PROOF_READ = 'carpool_proof_read';
    const CARPOOL_PROOF_UPDATE = 'carpool_proof_update';
    
    private $security;
    private $request;
    private $authManager;
    private $askRepository;
    private $carpoolProofRepository;

    public function __construct(RequestStack $requestStack, Security $security, AuthManager $authManager, AskRepository $askRepository, CarpoolProofRepository $carpoolProofRepository)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->security = $security;
        $this->authManager = $authManager;
        $this->askRepository = $askRepository;
        $this->carpoolProofRepository = $carpoolProofRepository;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::CARPOOL_PROOF_CREATE,
            self::CARPOOL_PROOF_READ,
            self::CARPOOL_PROOF_UPDATE
            ])) {
            return false;
        }

        // Classic Proof is a 'virtual' resource, we can't check its class
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::CARPOOL_PROOF_CREATE:
                /**
                 * @var ClassicProof $subject
                 */
                if ($ask = $this->askRepository->find($subject->getAskId())) {
                    return $this->canCreateClassicProof($ask);
                }
                return false;
            case self::CARPOOL_PROOF_READ:
                if ($carpoolProof = $this->carpoolProofRepository->find($this->request->get('id'))) {
                    return $this->canReadClassicProof($carpoolProof);
                }
                return false;
            case self::CARPOOL_PROOF_UPDATE:
                if ($carpoolProof = $this->carpoolProofRepository->find($this->request->get('id'))) {
                    return $this->canUpdateClassicProof($carpoolProof);
                }
                return false;
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateClassicProof(Ask $ask)
    {
        return $this->authManager->isAuthorized(self::CARPOOL_PROOF_CREATE, ['ask' => $ask]);
    }

    private function canReadClassicProof(CarpoolProof $carpoolProof)
    {
        return $this->authManager->isAuthorized(self::CARPOOL_PROOF_READ, ['ask' => $carpoolProof->getAsk()]);
    }

    private function canUpdateClassicProof(CarpoolProof $carpoolProof)
    {
        return $this->authManager->isAuthorized(self::CARPOOL_PROOF_UPDATE, ['ask' => $carpoolProof->getAsk()]);
    }
}
