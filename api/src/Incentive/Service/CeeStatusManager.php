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

namespace App\Incentive\Service;

use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Service\ProofManager;
use App\Incentive\Resource\CeeStatus;
use App\User\Entity\User;

/**
 * CEE Status Manager.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CeeStatusManager
{
    private $proofManager;

    /**
     * @var CeeStatus
     */
    private $ceeStatus;

    /**
     * @var array
     */
    private $ceeEligibleProofs;

    public function __construct(ProofManager $proofManager)
    {
        $this->proofManager = $proofManager;
        $this->ceeEligibleProofs = [];
    }

    /**
     * Keep only the eligible proofs (short distance only).
     */
    private function __getCEEEligibleProofs(User $user)
    {
        foreach ($user->getCarpoolProofsAsDriver() as $proof) {
            if (!is_null($proof->getAsk()) && $proof->getAsk()->getMatching()->getCommonDistance() >= CeeStatus::LONG_DISTANCE_MINIMUM_IN_METERS) {
                continue;
            }

            if (CarpoolProof::TYPE_HIGH !== $proof->getType() && CarpoolProof::TYPE_UNDETERMINED_DYNAMIC !== $proof->getType()) {
                continue;
            }

            $this->ceeEligibleProofs[] = $proof;
        }
    }

    private function __computeShortDistance(User $user)
    {
        $ceeShortDistanceStatus = $this->ceeStatus->getShortDistanceStatus();
        $this->__getCEEEligibleProofs($user);
        foreach ($this->ceeEligibleProofs as $proof) {
            switch ($proof->getStatus()) {
                case CarpoolProof::STATUS_PENDING:
                case CarpoolProof::STATUS_SENT:$ceeShortDistanceStatus->setNbPendingProofs($ceeShortDistanceStatus->getNbPendingProofs() + 1);

                    break;

                case CarpoolProof::STATUS_ERROR:
                case CarpoolProof::STATUS_ACQUISITION_ERROR:
                case CarpoolProof::STATUS_NORMALIZATION_ERROR:
                case CarpoolProof::STATUS_FRAUD_ERROR:$ceeShortDistanceStatus->setNbRejectedProofs($ceeShortDistanceStatus->getNbRejectedProofs() + 1);

                    break;

                case CarpoolProof::STATUS_VALIDATED:$ceeShortDistanceStatus->setNbValidatedProofs($ceeShortDistanceStatus->getNbValidatedProofs() + 1);

                    break;
            }
        }

        $this->ceeStatus->setShortDistanceStatus($ceeShortDistanceStatus);
    }

    private function __computeNbCarpoolProofs(User $user)
    {
        $this->__computeShortDistance($user);
    }

    public function getStatus(User $user): CeeStatus
    {
        $this->ceeStatus = new CeeStatus();
        $this->ceeStatus->setId($user->getId());
        $this->__computeNbCarpoolProofs($user);

        return $this->ceeStatus;
    }
}
