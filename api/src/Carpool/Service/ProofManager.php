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

namespace App\Carpool\Service;

use App\Carpool\Entity\Ask;
use App\Carpool\Entity\CarpoolProof;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Repository\AskRepository;
use App\Carpool\Repository\CarpoolProofRepository;
use App\Carpool\Repository\WaypointRepository;
use App\DataProvider\Entity\CarpoolProofGouvProvider;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Carpool proof manager service, used to send proofs to a register.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class ProofManager
{
    private $entityManager;
    private $logger;
    private $provider;
    private $carpoolProofRepository;
    private $askRepository;
    private $waypointRepository;
    private $proofType;
    
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager             The entity manager
     * @param LoggerInterface $logger                           The logger
     * @param CarpoolProofRepository $carpoolProofRepository    The carpool proofs repository
     * @param AskRepository $askRepository                      The ask repository
     * @param WaypointRepository $waypointRepository            The waypoint repository
     * @param string $provider                                  The provider for proofs
     * @param string $uri                                       The uri of the provider
     * @param string $token                                     The token for the provider
     * @param string $proofType                                 The proof type for classic ads
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        CarpoolProofRepository $carpoolProofRepository,
        AskRepository $askRepository,
        WaypointRepository $waypointRepository,
        string $provider,
        string $uri,
        string $token,
        string $proofType
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->carpoolProofRepository = $carpoolProofRepository;
        $this->askRepository = $askRepository;
        $this->waypointRepository = $waypointRepository;
        $this->proofType = $proofType;

        switch ($provider) {
            case 'BetaGouv':
            default:
                $this->provider = new CarpoolProofGouvProvider($uri, $token);
                break;
        }
    }

    /**
     * Send the pending proofs.
     *
     * @param DateTime|null $fromDate   The start of the period for which we want to send the proofs
     * @param DateTime|null $toDate     The end of the period  for which we want to send the proofs
     * @return void
     */
    public function sendProofs(?DateTime $fromDate = null, ?DateTime $toDate = null)
    {
        // if no dates are sent, we use the previous day
        if (is_null($fromDate)) {
            $fromDate = new DateTime();
            $fromDate->modify('-1 day');
            $fromDate->setTime(0, 0);
        }
        if (is_null($toDate)) {
            $toDate = new DateTime();
            $toDate->modify('-1 day');
            $toDate->setTime(23, 59, 59, 999);
        }

        // we get the pending proofs
        $proofs = $this->getProofs($fromDate, $toDate);

        // send these proofs
        foreach ($proofs as $proof) {
            /**
             * @var CarpoolProof $proof
             */
            if ($this->provider->postCollection($proof)) {
                $proof->setStatus(CarpoolProof::STATUS_SENT);
            } else {
                $proof->setStatus(CarpoolProof::STATUS_ERROR);
            }
            $this->entityManager->persist($proof);
        }
        $this->entityManager->flush();
    }

    /**
     * Create and return the pending proofs for the given period.
     *
     * @param DateTime $fromDate   The start of the period for which we want to get the proofs
     * @param DateTime $toDate     The end of the period  for which we want to get the proofs
     * @return array    The proofs
     */
    private function getProofs(DateTime $fromDate, DateTime $toDate)
    {
        // first we search the accepted asks for the given period
        $asks = $this->askRepository->findAcceptedAsksForPeriod($fromDate, $toDate);

        // then we create the corresponding proofs
        foreach ($asks as $ask) {
            $carpoolProof = new CarpoolProof();
            $carpoolProof->setStatus(CarpoolProof::STATUS_PENDING);
            $carpoolProof->setType($this->proofType);
            $carpoolProof->setAsk($ask);
            $carpoolProof->setDriver($ask->getMatching()->getProposalOffer()->getUser());
            $carpoolProof->setPassenger($ask->getMatching()->getProposalRequest()->getUser());
            if ($ask->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                $pickUpWaypoint = $this->waypointRepository->findMinPositionForAskAndRole($ask, Waypoint::ROLE_PASSENGER);
                $dropOffWaypoint = $this->waypointRepository->findMaxPositionForAskAndRole($ask, Waypoint::ROLE_PASSENGER);
                
                $carpoolProof->setPickUpDriverDate($ask->getCriteria()->getFromDate());
                $carpoolProof->setPickUpPassengerDate($ask->getCriteria()->getFromDate());
                $carpoolProof->setPickUpPassengerAddress(clone $pickUpWaypoint->getAddress());
            }
        }

        // finally we search all the pending proofs
        $proofs = $this->carpoolProofRepository->findBy(['status'=>CarpoolProof::STATUS_PENDING]);
    }
}
