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
            // TODO : search if carpool proofs already exist : could be the case if the driver and passenger used the mobile app
            $alreadyExist = false;
            if ($alreadyExist) {
                // carpool proofs already exist for the given period
            } else {
                // no carpool proof for the given period, we create it
                if ($ask->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                    // punctual, only one carpool proof
                    $carpoolProof = new CarpoolProof();
                    $carpoolProof->setStatus(CarpoolProof::STATUS_PENDING);
                    $carpoolProof->setType($this->proofType);
                    $carpoolProof->setAsk($ask);
                    $carpoolProof->setDriver($ask->getMatching()->getProposalOffer()->getUser());
                    $carpoolProof->setPassenger($ask->getMatching()->getProposalRequest()->getUser());
                    $originWaypoint = $this->waypointRepository->findMinPositionForAskAndRole($ask, Waypoint::ROLE_DRIVER);
                    $destinationWaypoint = $this->waypointRepository->findMaxPositionForAskAndRole($ask, Waypoint::ROLE_DRIVER);
                    $carpoolProof->setOriginDriverAddress(clone $originWaypoint->getAddress());
                    $carpoolProof->setDestinationDriverAddress(clone $destinationWaypoint->getAddress());
                    $pickUpWaypoint = $this->waypointRepository->findMinPositionForAskAndRole($ask, Waypoint::ROLE_PASSENGER);
                    $dropOffWaypoint = $this->waypointRepository->findMaxPositionForAskAndRole($ask, Waypoint::ROLE_PASSENGER);
                    $carpoolProof->setPickUpPassengerAddress(clone $pickUpWaypoint->getAddress());
                    $carpoolProof->setDropOffPassengerAddress(clone $dropOffWaypoint->getAddress());
                    /**
                     * @var Datetime $startDate
                     */
                    $startDate = clone $ask->getCriteria()->getFromDate();
                    $startDate->setTime($ask->getCriteria()->getFromTime()->format('H'), $ask->getCriteria()->getFromTime()->format('i'));
                    $carpoolProof->setStartDriverDate($startDate);
                    /**
                    * @var Datetime $endDate
                    */
                    // we init the end date with the start date
                    $endDate = clone $startDate;
                    // then we add the duration till the destination point
                    $endDate->modify('+' . $destinationWaypoint->getDuration() + ' second');
                    $carpoolProof->setEndDriverDate($endDate);
                    /**
                     * @var Datetime $pickUpDate
                     */
                    // we init the pickup date to the start date of the driver
                    $pickUpDate = clone $ask->getCriteria()->getFromDate();
                    $pickUpDate->setTime($ask->getCriteria()->getFromTime()->format('H'), $ask->getCriteria()->getFromTime()->format('i'));
                    // then we add the duration till the pickup point
                    $pickUpDate->modify('+' . $pickUpWaypoint->getDuration() + ' second');
                    /**
                     * @var Datetime $dropOffDate
                     */
                    // we init the dropoff date with the pickup date
                    $dropOffDate = clone $pickUpDate;
                    // then we add the duration till the dropoff point
                    $dropOffDate->modify('+' . $dropOffWaypoint->getDuration() + ' second');
                    $carpoolProof->setPickUpDriverDate($pickUpDate);
                    $carpoolProof->setPickUpPassengerDate($pickUpDate);
                    $carpoolProof->setDropOffDriverDate($dropOffDate);
                    $carpoolProof->setDropOffPassengerDate($dropOffDate);
                    $this->entityManager->persist($carpoolProof);
                } else {
                    // regular, we need to create a carpool proof for each day between fromDate and toDate
                    $curDate = clone $fromDate;
                    $continue = true;
                    // we get some available information here outside the loop
                    $originWaypoint = $this->waypointRepository->findMinPositionForAskAndRole($ask, Waypoint::ROLE_DRIVER);
                    $destinationWaypoint = $this->waypointRepository->findMaxPositionForAskAndRole($ask, Waypoint::ROLE_DRIVER);
                    $pickUpWaypoint = $this->waypointRepository->findMinPositionForAskAndRole($ask, Waypoint::ROLE_PASSENGER);
                    $dropOffWaypoint = $this->waypointRepository->findMaxPositionForAskAndRole($ask, Waypoint::ROLE_PASSENGER);
                    while ($continue) {
                        $carpoolProof = new CarpoolProof();
                        $carpoolProof->setStatus(CarpoolProof::STATUS_PENDING);
                        $carpoolProof->setType($this->proofType);
                        $carpoolProof->setAsk($ask);
                        $carpoolProof->setDriver($ask->getMatching()->getProposalOffer()->getUser());
                        $carpoolProof->setPassenger($ask->getMatching()->getProposalRequest()->getUser());
                        $carpoolProof->setOriginDriverAddress(clone $originWaypoint->getAddress());
                        $carpoolProof->setDestinationDriverAddress(clone $destinationWaypoint->getAddress());
                        $carpoolProof->setPickUpPassengerAddress(clone $pickUpWaypoint->getAddress());
                        $carpoolProof->setDropOffPassengerAddress(clone $dropOffWaypoint->getAddress());
                        /**
                         * @var Datetime $startDate
                         */
                        $startDate = clone $curDate;
                        /**
                         * @var Datetime $pickUpDate
                         */
                        // we init the pickup date to the start date of the driver
                        $pickUpDate = clone $curDate;
                        switch ($curDate->format('w')) {
                            // we check for each date of the period if it's a carpoool day
                            case 0:     // sunday
                                if ($ask->getCriteria()->isSunCheck()) {
                                    $startDate->setTime($ask->getCriteria()->getSunTime()->format('H'), $ask->getCriteria()->getSunTime()->format('i'));
                                    $pickUpDate->setTime($ask->getCriteria()->getSunTime()->format('H'), $ask->getCriteria()->getSunTime()->format('i'));
                                }
                                break;
                            case 1:     // monday
                                if ($ask->getCriteria()->isMonCheck()) {
                                    $startDate->setTime($ask->getCriteria()->getMonTime()->format('H'), $ask->getCriteria()->getMonTime()->format('i'));
                                    $pickUpDate->setTime($ask->getCriteria()->getMonTime()->format('H'), $ask->getCriteria()->getMonTime()->format('i'));
                                }
                                break;
                            case 2:     // tuesday
                                if ($ask->getCriteria()->isTueCheck()) {
                                    $startDate->setTime($ask->getCriteria()->getTueTime()->format('H'), $ask->getCriteria()->getTueTime()->format('i'));
                                    $pickUpDate->setTime($ask->getCriteria()->getTueTime()->format('H'), $ask->getCriteria()->getTueTime()->format('i'));
                                }
                                break;
                            case 3:     // wednesday
                                if ($ask->getCriteria()->isWedCheck()) {
                                    $startDate->setTime($ask->getCriteria()->getWedTime()->format('H'), $ask->getCriteria()->getWedTime()->format('i'));
                                    $pickUpDate->setTime($ask->getCriteria()->getWedTime()->format('H'), $ask->getCriteria()->getWedTime()->format('i'));
                                }
                                break;
                            case 4:     // thursday
                                if ($ask->getCriteria()->isThuCheck()) {
                                    $startDate->setTime($ask->getCriteria()->getThuTime()->format('H'), $ask->getCriteria()->getThuTime()->format('i'));
                                    $pickUpDate->setTime($ask->getCriteria()->getThuTime()->format('H'), $ask->getCriteria()->getThuTime()->format('i'));
                                }
                                break;
                            case 5:     //friday
                                if ($ask->getCriteria()->isFriCheck()) {
                                    $startDate->setTime($ask->getCriteria()->getFriTime()->format('H'), $ask->getCriteria()->getFriTime()->format('i'));
                                    $pickUpDate->setTime($ask->getCriteria()->getFriTime()->format('H'), $ask->getCriteria()->getFriTime()->format('i'));
                                }
                                break;
                            case 6:     // saturday
                                if ($ask->getCriteria()->isSatCheck()) {
                                    $startDate->setTime($ask->getCriteria()->getSatTime()->format('H'), $ask->getCriteria()->getSatTime()->format('i'));
                                    $pickUpDate->setTime($ask->getCriteria()->getSatTime()->format('H'), $ask->getCriteria()->getSatTime()->format('i'));
                                }
                                break;
                        }
                        $carpoolProof->setStartDriverDate($startDate);
                        /**
                         * @var Datetime $endDate
                         */
                        // we init the end date with the start date
                        $endDate = clone $startDate;
                        // then we add the duration till the destination point
                        $endDate->modify('+' . $destinationWaypoint->getDuration() + ' second');
                        $carpoolProof->setEndDriverDate($endDate);
                        // we add the duration till the pickup point
                        $pickUpDate->modify('+' . $pickUpWaypoint->getDuration() + ' second');
                        /**
                         * @var Datetime $dropOffDate
                         */
                        // we init the dropoff date with the pickup date
                        $dropOffDate = clone $pickUpDate;
                        // then we add the duration till the dropoff point
                        $dropOffDate->modify('+' . $dropOffWaypoint->getDuration() + ' second');
                        $carpoolProof->setPickUpDriverDate($pickUpDate);
                        $carpoolProof->setPickUpPassengerDate($pickUpDate);
                        $carpoolProof->setDropOffDriverDate($dropOffDate);
                        $carpoolProof->setDropOffPassengerDate($dropOffDate);
                        $this->entityManager->persist($carpoolProof);

                        if ($curDate->format('Y-m-d') == $toDate->format('Y-m-d')) {
                            $continue = false;
                        } else {
                            $curDate->modify('+1 day');
                        }
                    }
                }
            }
        }
        $this->entityManager->flush();

        // we return all the pending proofs
        return $this->carpoolProofRepository->findBy(['status'=>CarpoolProof::STATUS_PENDING]);
    }
}
