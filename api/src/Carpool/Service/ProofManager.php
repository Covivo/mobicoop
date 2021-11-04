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
use App\Carpool\Exception\DynamicException;
use App\Carpool\Exception\ProofException;
use App\Carpool\Repository\AskRepository;
use App\Carpool\Repository\CarpoolProofRepository;
use App\Carpool\Repository\WaypointRepository;
use App\Carpool\Ressource\ClassicProof;
use App\DataProvider\Entity\CarpoolProofGouvProvider;
use App\Geography\Entity\Direction;
use App\Geography\Service\GeoSearcher;
use App\Geography\Service\GeoTools;
use App\User\Entity\User;
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
    private $geoSearcher;
    private $geoTools;
    private $duration;
    private $minIdentityDistance;
    
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager             The entity manager
     * @param LoggerInterface $logger                           The logger
     * @param CarpoolProofRepository $carpoolProofRepository    The carpool proofs repository
     * @param AskRepository $askRepository                      The ask repository
     * @param WaypointRepository $waypointRepository            The waypoint repository
     * @param GeoTools $geoTools                                The geotools
     * @param string $prefix                                    The prefix for proofs
     * @param string $provider                                  The provider for proofs
     * @param string $uri                                       The uri of the provider
     * @param string $token                                     The token for the provider
     * @param int $duration                                     Number of days to send by default to the carpool register
     * @param int $minIdentityDistance                          Minimal distance in meters between origin and destination/dropoff to determine distinct identities (C Class proof)
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        CarpoolProofRepository $carpoolProofRepository,
        AskRepository $askRepository,
        WaypointRepository $waypointRepository,
        GeoSearcher $geoSearcher,
        GeoTools $geoTools,
        string $prefix,
        string $provider,
        string $uri,
        string $token,
        int $duration,
        int $minIdentityDistance
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->carpoolProofRepository = $carpoolProofRepository;
        $this->askRepository = $askRepository;
        $this->waypointRepository = $waypointRepository;
        $this->geoTools = $geoTools;
        $this->geoSearcher = $geoSearcher;
        $this->duration = $duration;
        $this->minIdentityDistance = $minIdentityDistance;

        switch ($provider) {
            case 'BetaGouv':
            default:
                $this->provider = new CarpoolProofGouvProvider($uri, $token, $prefix);
                break;
        }
    }




    /********************
     * PROOF MANAGEMENT *
     ********************/


    /**
     * Get a carpool proof by its id.
     *
     * @param integer $id   The id of the proof
     * @return CarpoolProof The carpool proof if found or null if not found
     */
    public function getProof(int $id)
    {
        return $this->carpoolProofRepository->find($id);
    }

    /**
    * Get a carpool proof by its id.
    *
    * @param integer $id   The id of the proof
    * @return ClassicProof
    */
    public function getClassicProof(int $id)
    {
        $carpoolProof = $this->carpoolProofRepository->find($id);
        $classicProof = new ClassicProof;
        $classicProof->setId($id);
        $classicProof->setStatus(
            ($carpoolProof->getPickUpPassengerDate() ? '1' : '0') .
            ($carpoolProof->getPickUpDriverDate() ? '1' : '0') .
            ($carpoolProof->getDropOffPassengerDate() ? '1' : '0') .
            ($carpoolProof->getDropOffDriverDate() ? '1' : '0')
        );
        $classicProof->setProofDate($carpoolProof->getStartDriverDate());
        
        return $classicProof;
    }

    /**
     * Get a proof for an Ask an a date
     *
     * @param Ask $ask              The ask
     * @param DateTime $date        The date
     * @return CarpoolProof|null    The carpool proof if it exists
     */
    public function getProofForDate(Ask $ask, DateTime $date)
    {
        return $this->carpoolProofRepository->findByAskAndDate($ask, $date);
    }

    /**
     * Create a realtimeproof for an ask.
     *
     * @param Ask $ask          The ask
     * @param float $longitude  The longitude of the author when the creation is asked
     * @param float $latitude   The latitude of the author when the creation is asked
     * @param string $type      The type of proof
     * @param User $author      The author of the proof
     * @param User $driver      The driver
     * @param User $passenger   The passenger
     * @return CarpoolProof     The created proof
     */
    public function createProof(Ask $ask, float $longitude, float $latitude, string $type, User $author, User $driver, User $passenger)
    {
        $carpoolProof = new CarpoolProof();
        $carpoolProof->setType($type);
        $carpoolProof->setAsk($ask);
        $carpoolProof->setDriver($driver);
        $carpoolProof->setPassenger($passenger);
        $originWaypoint = $this->waypointRepository->findMinPositionForAskAndRole($ask, Waypoint::ROLE_DRIVER);
        $destinationWaypoint = $this->waypointRepository->findMaxPositionForAskAndRole($ask, Waypoint::ROLE_DRIVER);
        $carpoolProof->setOriginDriverAddress(clone $originWaypoint->getAddress());
        $carpoolProof->setDestinationDriverAddress(clone $destinationWaypoint->getAddress());
        // we have to compute the start date of the driver
        if ($ask->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
            // for a punctual ad, we use fromDate and fromTime (both are theoretical, they *should* be correct !)
            /**
             * @var DateTime $startDate
             */
            $startDate = clone $ask->getCriteria()->getFromDate();
            $startDate->setTime($ask->getCriteria()->getFromTime()->format('H'), $ask->getCriteria()->getFromTime()->format('i'));
        } else {
            // for a regular ad, we use the current date and the theoretical time
            $startDate = new DateTime('UTC');
            switch ($startDate->format('w')) {
                // we check for each date of the period if it's a carpoool day
                case 0:     // sunday
                    if ($ask->getCriteria()->isSunCheck()) {
                        $startDate->setTime($ask->getCriteria()->getSunTime()->format('H'), $ask->getCriteria()->getSunTime()->format('i'));
                    }
                    break;
                case 1:     // monday
                    if ($ask->getCriteria()->isMonCheck()) {
                        $startDate->setTime($ask->getCriteria()->getMonTime()->format('H'), $ask->getCriteria()->getMonTime()->format('i'));
                    }
                    break;
                case 2:     // tuesday
                    if ($ask->getCriteria()->isTueCheck()) {
                        $startDate->setTime($ask->getCriteria()->getTueTime()->format('H'), $ask->getCriteria()->getTueTime()->format('i'));
                    }
                    break;
                case 3:     // wednesday
                    if ($ask->getCriteria()->isWedCheck()) {
                        $startDate->setTime($ask->getCriteria()->getWedTime()->format('H'), $ask->getCriteria()->getWedTime()->format('i'));
                    }
                    break;
                case 4:     // thursday
                    if ($ask->getCriteria()->isThuCheck()) {
                        $startDate->setTime($ask->getCriteria()->getThuTime()->format('H'), $ask->getCriteria()->getThuTime()->format('i'));
                    }
                    break;
                case 5:     // friday
                    if ($ask->getCriteria()->isFriCheck()) {
                        $startDate->setTime($ask->getCriteria()->getFriTime()->format('H'), $ask->getCriteria()->getFriTime()->format('i'));
                    }
                    break;
                case 6:     // saturday
                    if ($ask->getCriteria()->isSatCheck()) {
                        $startDate->setTime($ask->getCriteria()->getSatTime()->format('H'), $ask->getCriteria()->getSatTime()->format('i'));
                    }
                    break;
            }
        }
        $carpoolProof->setStartDriverDate($startDate);
        /**
        * @var DateTime $endDate
        */
        // we init the end date with the start date
        $endDate = clone $startDate;
        // then we add the duration till the destination point
        $endDate->modify('+' . $destinationWaypoint->getDuration() . ' second');
        // note : for now, the end date is computed, it's theorEtical and not the 'real' end date
        $carpoolProof->setEndDriverDate($endDate);

        // direction
        $direction = new Direction();
        $direction->setDistance(0);
        $direction->setDuration(0);
        $direction->setDetail("");
        $direction->setSnapped("");
        $direction->setFormat("Dynamic");

        // search the role of the current user
        if ($author->getId() == $passenger->getId()) {
            // the author is the passenger
            $carpoolProof->setPickUpPassengerDate(new \DateTime('UTC'));
            $carpoolProof->setPickUpPassengerAddress($this->geoSearcher->getAddressByPartialAddressArray(['latitude'=>$latitude,'longitude'=>$longitude]));
            $carpoolProof->setPoints([$carpoolProof->getPickUpPassengerAddress()]);
            $direction->setPoints([$carpoolProof->getPickUpPassengerAddress()]);
        } else {
            // the author is the driver
            $carpoolProof->setPickUpDriverDate(new \DateTime('UTC'));
            $carpoolProof->setPickUpDriverAddress($this->geoSearcher->getAddressByPartialAddressArray(['latitude'=>$latitude,'longitude'=>$longitude]));
            $carpoolProof->setPoints([$carpoolProof->getPickUpDriverAddress()]);
            $direction->setPoints([$carpoolProof->getPickUpDriverAddress()]);
        }
        $carpoolProof->setDirection($direction);

        $this->entityManager->persist($carpoolProof);
        $this->entityManager->flush();

        return $carpoolProof;
    }

    /**
     * Update a proof.
     *
     * @param integer $id       The id of the proof to update
     * @param float $longitude  The longitude of the author when the update is asked
     * @param float $latitude   The latitude of the author when the update is asked
     * @param User $author      The author of the update
     * @param User $passenger   The passenger
     * @param int $distance     The max distance between the driver and the passenger to validate the pickup/dropoff
     * @return CarpoolProof     The updated proof
     */
    public function updateProof(int $id, float $longitude, float $latitude, User $author, User $passenger, int $distance)
    {
        // search the proof
        if (!$carpoolProof = $this->carpoolProofRepository->find($id)) {
            throw new ProofException("Proof not found");
        }

        // search the role of the current user
        $actor = null;
        if ($author->getId() == $passenger->getId()) {
            // the user is passenger
            $actor = CarpoolProof::ACTOR_PASSENGER;
        } else {
            // the user is driver
            $actor = CarpoolProof::ACTOR_DRIVER;
        }

        // we perform different actions depending on the role and the moment
        switch ($actor) {
            case CarpoolProof::ACTOR_DRIVER:
                // uncomment this if dropoff is authorized only if both pickups has been made
                // if (!is_null($carpoolProof->getPickUpDriverAddress()) && is_null($carpoolProof->getPickUpPassengerAddress())) {
                //     // the driver can't set the dropoff while the passenger has not certified its pickup
                //     throw new ProofException("The passenger has not sent its pickup certification yet");
                // }
                if (!is_null($carpoolProof->getPickUpDriverAddress())) {
                    // the driver has set its pickup
                    if (!is_null($carpoolProof->getDropOffDriverAddress())) {
                        // the driver has already certified its pickup and dropoff
                        throw new ProofException("The driver has already sent its dropoff certification");
                    }
                    if (is_null($carpoolProof->getDropOffPassengerAddress())) {
                        // the passenger has not set its dropoff
                        $carpoolProof->setDropOffDriverDate(new \DateTime('UTC'));
                        $carpoolProof->setDropOffDriverAddress($this->geoSearcher->getAddressByPartialAddressArray(['latitude'=>$latitude,'longitude'=>$longitude]));
                    } else {
                        // the passenger has set its dropoff, we have to check the positions
                        if ($this->geoTools->haversineGreatCircleDistance(
                            $latitude,
                            $longitude,
                            $carpoolProof->getDropOffPassengerAddress()->getLatitude(),
                            $carpoolProof->getDropOffPassengerAddress()->getLongitude()
                        )<=$distance) {
                            // drop off driver
                            $carpoolProof->setDropOffDriverDate(new \DateTime('UTC'));
                            $carpoolProof->setDropOffDriverAddress($this->geoSearcher->getAddressByPartialAddressArray(['latitude'=>$latitude,'longitude'=>$longitude]));
                            // the driver and the passenger have made their certification, the proof is ready to be sent
                            $carpoolProof->setStatus(CarpoolProof::STATUS_PENDING);
                        // driver direction will be set when the dynamic ad of the driver will be finished
                        } else {
                            throw new ProofException("Driver dropoff certification failed : the passenger certified address is too far");
                        }
                    }
                } elseif (!is_null($carpoolProof->getPickUpPassengerAddress())) {
                    // the driver has not sent its pickup but the passenger has
                    if ($this->geoTools->haversineGreatCircleDistance(
                        $latitude,
                        $longitude,
                        $carpoolProof->getPickUpPassengerAddress()->getLatitude(),
                        $carpoolProof->getPickUpPassengerAddress()->getLongitude()
                    )<=$distance) {
                        $carpoolProof->setPickupDriverDate(new \DateTime('UTC'));
                        $carpoolProof->setPickUpDriverAddress($this->geoSearcher->getAddressByPartialAddressArray(['latitude'=>$latitude,'longitude'=>$longitude]));
                    } else {
                        throw new ProofException("Driver pickup certification failed : the passenger certified address is too far");
                    }
                } else {
                    // the passenger has not set its pickup
                    $carpoolProof->setPickUpDriverDate(new \DateTime('UTC'));
                    $carpoolProof->setPickUpDriverAddress($this->geoSearcher->getAddressByPartialAddressArray(['latitude'=>$latitude,'longitude'=>$longitude]));
                }
                break;
            case CarpoolProof::ACTOR_PASSENGER:
                // uncomment this if dropoff is authorized only if both pickups has been made
                // if (!is_null($carpoolProof->getPickUpPassengerAddress()) && is_null($carpoolProof->getPickUpDriverAddress())) {
                //     // the passenger can't set the dropoff while the driver has not certified its pickup
                //     throw new ProofException("The driver has not sent its pickup certification yet");
                // }
                if (!is_null($carpoolProof->getPickUpPassengerAddress())) {
                    // the passenger has set its pickup
                    if (!is_null($carpoolProof->getDropOffPassengerAddress())) {
                        // the passenger has already certified its pickup and dropoff
                        throw new ProofException("The passenger has already sent its dropoff certification");
                    }
                    if (is_null($carpoolProof->getDropOffDriverAddress())) {
                        // the driver has not set its dropoff
                        $carpoolProof->setDropOffPassengerDate(new \DateTime('UTC'));
                        $carpoolProof->setDropOffPassengerAddress($this->geoSearcher->getAddressByPartialAddressArray(['latitude'=>$latitude,'longitude'=>$longitude]));
                        // set the passenger dynamic ad to finished if relevant
                        if ($carpoolProof->getAsk()->getMatching()->getProposalRequest()->isDynamic()) {
                            $carpoolProof->getAsk()->getMatching()->getProposalRequest()->setFinished(true);
                            $this->entityManager->persist($carpoolProof->getAsk()->getMatching()->getProposalRequest());
                        }
                    } else {
                        // the driver has set its dropoff, we have to check the positions
                        if ($this->geoTools->haversineGreatCircleDistance(
                            $latitude,
                            $longitude,
                            $carpoolProof->getDropOffDriverAddress()->getLatitude(),
                            $carpoolProof->getDropOffDriverAddress()->getLongitude()
                        )<=$distance) {
                            // drop off passenger
                            $carpoolProof->setDropOffPassengerDate(new \DateTime('UTC'));
                            $carpoolProof->setDropOffPassengerAddress($this->geoSearcher->getAddressByPartialAddressArray(['latitude'=>$latitude,'longitude'=>$longitude]));
                            // set the passenger dynamic ad to finished if relevant
                            if ($carpoolProof->getAsk()->getMatching()->getProposalRequest()->isDynamic()) {
                                $carpoolProof->getAsk()->getMatching()->getProposalRequest()->setFinished(true);
                                $this->entityManager->persist($carpoolProof->getAsk()->getMatching()->getProposalRequest());
                            }
                            // the driver and the passenger have made their certification, the proof is ready to be sent
                            $carpoolProof->setStatus(CarpoolProof::STATUS_PENDING);
                        } else {
                            throw new ProofException("Passenger dropoff certification failed : the driver certified address is too far");
                        }
                    }
                } elseif (!is_null($carpoolProof->getPickUpDriverAddress())) {
                    // the passenger has not sent its pickup but the driver has
                    if ($this->geoTools->haversineGreatCircleDistance(
                        $latitude,
                        $longitude,
                        $carpoolProof->getPickUpDriverAddress()->getLatitude(),
                        $carpoolProof->getPickUpDriverAddress()->getLongitude()
                    )<=$distance) {
                        $carpoolProof->setPickupPassengerDate(new \DateTime('UTC'));
                        $carpoolProof->setPickUpPassengerAddress($this->geoSearcher->getAddressByPartialAddressArray(['latitude'=>$latitude,'longitude'=>$longitude]));
                    } else {
                        throw new ProofException("Passenger pickup certification failed : the driver certified address is too far");
                    }
                } else {
                    // the driver has not set its pickup
                    $carpoolProof->setPickupPassengerDate(new \DateTime('UTC'));
                    $carpoolProof->setPickUpPassengerAddress($this->geoSearcher->getAddressByPartialAddressArray(['latitude'=>$latitude,'longitude'=>$longitude]));
                }
                break;
        }

        $this->entityManager->persist($carpoolProof);
        $this->entityManager->flush();

        return $carpoolProof;
    }

    /**
     * Remove proofs of a user.
     * Used to anonymize proofs when a user deletes its account.
     *
     * @param User $user    The user to delete
     * @return void
     */
    public function removeProofs(User $user)
    {
        // we start by searching in the asks, if the user is the first to remove its account
        $asks = $this->askRepository->findAskByUser($user);
        foreach ($asks as $ask) {
            if ($ask->getMatching()->getProposalOffer()->getUser()->getId() == $user->getId()) {
                // the user is the driver
                foreach ($ask->getCarpoolProofs() as $carpoolProof) {
                    /**
                     * @var CarpoolProof $carpoolProof
                     */
                    $carpoolProof->setDriver(null);
                    $ask->removeCarpoolProof($carpoolProof);
                    // if the proof is pending, we set it to canceled
                    if ($carpoolProof->getStatus() == CarpoolProof::STATUS_PENDING) {
                        $carpoolProof->setStatus(CarpoolProof::STATUS_CANCELED);
                    }
                    // uncomment the following to anonymize driver addresses used in the proof
                    // $carpoolProof->setOriginDriverAddress(null);
                    // $carpoolProof->setDestinationDriverAddress(null);
                    $this->entityManager->persist($carpoolProof);
                }
            } else {
                foreach ($ask->getCarpoolProofs() as $carpoolProof) {
                    /**
                     * @var CarpoolProof $carpoolProof
                     */
                    $carpoolProof->setPassenger(null);
                    $ask->removeCarpoolProof($carpoolProof);
                    // if the proof is pending, we set it to canceled
                    if ($carpoolProof->getStatus() == CarpoolProof::STATUS_PENDING) {
                        $carpoolProof->setStatus(CarpoolProof::STATUS_CANCELED);
                    }
                    // uncomment the following to anonymize passenger addresses used in the proof
                    // $carpoolProof->setPickUpPassengerAddress(null);
                    // $carpoolProof->setPickUpDriverAddress(null);
                    // $carpoolProof->setDropOffPassengerAddress(null);
                    // $carpoolProof->setDropOffDriverAddress(null);
                    $this->entityManager->persist($carpoolProof);
                }
            }
        }

        // then we search in the proofs, as another user may have removed its account, the proofs may still exist
        $carpoolProofs = $this->carpoolProofRepository->findRemainingByUser($user);
        foreach ($carpoolProofs as $carpoolProof) {
            /**
             * @var CarpoolProof $carpoolProof
             */
            if (!is_null($carpoolProof->getDriver())) {
                $carpoolProof->setDriver(null);
            // uncomment the following to anonymize driver addresses used in the proof
                // $carpoolProof->setOriginDriverAddress(null);
                // $carpoolProof->setDestinationDriverAddress(null);
            } elseif (!is_null($carpoolProof->getPassenger())) {
                $carpoolProof->setPassenger(null);
                // uncomment the following to anonymize passenger addresses used in the proof
                // $carpoolProof->setPickUpPassengerAddress(null);
                // $carpoolProof->setPickUpDriverAddress(null);
                // $carpoolProof->setDropOffPassengerAddress(null);
                // $carpoolProof->setDropOffDriverAddress(null);
            }
            $this->entityManager->persist($carpoolProof);
        }
        
        // the flush should be made elsewhere...
        // $this->entityManager->flush();
    }




    /*****************************
     * PROOF REGISTER MANAGEMENT *
     *****************************/


    /**
     * Send the pending proofs.
     *
     * @param DateTime|null $fromDate   The start of the period for which we want to send the proofs
     * @param DateTime|null $toDate     The end of the period  for which we want to send the proofs
     * @return int  The number of proofs sent
     */
    public function sendProofs(?DateTime $fromDate = null, ?DateTime $toDate = null)
    {
        // if no dates are sent, we use the last {duration} days
        if (is_null($fromDate)) {
            $fromDate = new DateTime();
            $fromDate->modify('-' . $this->duration . ' day');
            $fromDate->setTime(0, 0);
        }
        if (is_null($toDate)) {
            $toDate = new DateTime();
            $toDate->modify('-1 day');
            $toDate->setTime(23, 59, 59, 999);
        }

        // first we need to validate the waiting proofs : some proof may be in undeterminate status
        $this->validateProofs($fromDate, $toDate);

        // then we get the pending proofs
        $proofs = $this->getProofs($fromDate, $toDate);
        $nbSent = 0;
        // exit;
        // send these proofs
        foreach ($proofs as $proof) {
            /**
             * @var CarpoolProof $proof
             */
            $result = $this->provider->postCollection($proof);
            $this->logger->info("Result of the send for proof #" . $proof->getId() . " : code " . $result->getCode() . " | value : ".$result->getValue());
            if ($result->getCode() == 200) {
                $proof->setStatus(CarpoolProof::STATUS_SENT);
                $nbSent++;
            } else {
                $proof->setStatus(CarpoolProof::STATUS_ERROR);
            }
            $this->entityManager->persist($proof);
        }
        $this->entityManager->flush();
        return $nbSent;
    }

    /**
     * Validate the pending proofs for the given period.
     * Used to update pending with undetermined final class.
     *
     * @param DateTime $fromDate   The start of the period for which we want to update the proofs
     * @param DateTime $toDate     The end of the period  for which we want to update the proofs
     * @return void
     */
    private function validateProofs(DateTime $fromDate, DateTime $toDate)
    {
        // first we search the undetermined proofs for the given period
        $carpoolProofs = $this->carpoolProofRepository->findByTypesAndPeriod([CarpoolProof::TYPE_UNDETERMINED_CLASSIC,CarpoolProof::TYPE_UNDETERMINED_DYNAMIC], $fromDate, $toDate);
        
        if (is_array($carpoolProofs) && count($carpoolProofs)>0) {
            // then we determine the right class depending on the available data
            foreach ($carpoolProofs as $carpoolProof) {
                // we change the status to pending
                $carpoolProof->setStatus(CarpoolProof::STATUS_PENDING);
                
                // For now, TYPE_HIGH is only for dynamique because on organized trip we use declarative origin and destination not GPS given
                if (
                    $carpoolProof->getType() == CarpoolProof::TYPE_UNDETERMINED_DYNAMIC &&
                    !is_null($carpoolProof->getPickUpDriverAddress()) &&
                    !is_null($carpoolProof->getPickUpPassengerAddress()) &&
                    !is_null($carpoolProof->getDropOffDriverAddress()) &&
                    !is_null($carpoolProof->getDropOffPassengerAddress()) &&
                    !is_null($carpoolProof->getPickUpDriverDate()) &&
                    !is_null($carpoolProof->getPickUpPassengerDate()) &&
                    !is_null($carpoolProof->getDropOffDriverDate()) &&
                    !is_null($carpoolProof->getDropOffPassengerDate())
                    && $this->checkDistinctIdentities($carpoolProof)) {
                    // all the possible data is set for both carpoolers => max type
                    $carpoolProof->setType(CarpoolProof::TYPE_HIGH);
                    $this->entityManager->persist($carpoolProof);
                    continue;
                }
                if (
                    !is_null($carpoolProof->getPickUpDriverAddress()) &&
                    !is_null($carpoolProof->getDropOffDriverAddress()) &&
                    !is_null($carpoolProof->getPickUpDriverDate()) &&
                    !is_null($carpoolProof->getDropOffDriverDate())) {
                    // all the possible data is set for the driver => middle type
                    $carpoolProof->setType(CarpoolProof::TYPE_MID);
                    // we need to fill/replace the passenger time details with theoretical data and driver data
                    $pickUpWaypoint = $this->waypointRepository->findMinPositionForAskAndRole($carpoolProof->getAsk(), Waypoint::ROLE_PASSENGER);
                    $dropOffWaypoint = $this->waypointRepository->findMaxPositionForAskAndRole($carpoolProof->getAsk(), Waypoint::ROLE_PASSENGER);
                    /**
                     * @var Datetime $pickUpDate
                     */
                    // we init the pickup date with the start date of the driver
                    $pickUpDate = clone $carpoolProof->getStartDriverDate();
                    // then we add the duration till the pickup point
                    $pickUpDate->modify('+' . $pickUpWaypoint->getDuration() . ' second');
                    /**
                     * @var Datetime $dropOffDate
                     */
                    // we init the dropoff date with the start date of the driver
                    $dropOffDate = clone $carpoolProof->getStartDriverDate();
                    // then we add the duration till the dropoff point
                    $dropOffDate->modify('+' . $dropOffWaypoint->getDuration() . ' second');
                    $carpoolProof->setPickUpPassengerDate($pickUpDate);
                    $carpoolProof->setDropOffPassengerDate($dropOffDate);
                    $this->entityManager->persist($carpoolProof);
                    continue;
                }
                if (
                    !is_null($carpoolProof->getPickUpPassengerAddress()) &&
                    !is_null($carpoolProof->getDropOffPassengerAddress()) &&
                    !is_null($carpoolProof->getPickUpPassengerDate()) &&
                    !is_null($carpoolProof->getDropOffPassengerDate())) {
                    // all the possible data is set for the passenger => middle type
                    // the driver basic information are already filled (they are filled at the carpool proof creation)
                    // we only keep the time information as the geographical data are not validated
                    $carpoolProof->setOriginDriverAddress(null);
                    $carpoolProof->setDestinationDriverAddress(null);
                    $carpoolProof->setType(CarpoolProof::TYPE_MID);
                    $this->entityManager->persist($carpoolProof);
                    continue;
                }
                // if any of the previous is verified, we initialize with the lowest possible type
                $carpoolProof->setType(CarpoolProof::TYPE_LOW);
                $this->entityManager->persist($carpoolProof);
            }
            $this->entityManager->flush();
        }
    }

    /**
     * Check if the two carpoolProof's actors are not the same person
     * The actors' origins must be distant enough Or The driver's destination and the passenger's drop off must be distant enough
     *
     * @param CarpoolProof $carpoolProof
     * @return boolean
     */
    private function checkDistinctIdentities(CarpoolProof $carpoolProof): bool
    {
        $originsDistantEnough = $destinationsDistantEnough = false;

        // The actors' origins

        $originDriverAddress = $carpoolProof->getOriginDriverAddress();

        if ($carpoolProof->getAsk()->getMatching()->getProposalRequest()->getUser()->getId() == $carpoolProof->getPassenger()->getId()) {
            $originPassengerAddress = $carpoolProof->getAsk()->getMatching()->getProposalRequest()->getWaypoints()[0];
        } elseif ($carpoolProof->getAsk()->getMatching()->getProposalOffer()->getUser()->getId() == $carpoolProof->getPassenger()->getId()) {
            $originPassengerAddress = $carpoolProof->getAsk()->getMatching()->getProposalOffer()->getWaypoints()[0];
        } else {
            throw new DynamicException("Passenger can't be found");
        }

        if ($this->geoTools->haversineGreatCircleDistance($originDriverAddress->getLatitude(), $originDriverAddress->getLongitude(), $originPassengerAddress->getAddress()->getLatitude(), $originPassengerAddress->getAddress()->getLongitude())>=$this->minIdentityDistance) {
            $originsDistantEnough = true;
        }

        // The driver's destination and the passenger's drop off
        $destinationDriverAddress = $carpoolProof->getDestinationDriverAddress();
        if (is_null($destinationDriverAddress)) {
            throw new DynamicException("No destination driver address");
        }
        $dropOffPassengerAddress = $carpoolProof->getDropOffPassengerAddress();
        if (is_null($dropOffPassengerAddress)) {
            throw new DynamicException("No dropoff passenger address");
        }
        if ($this->geoTools->haversineGreatCircleDistance($destinationDriverAddress->getLatitude(), $destinationDriverAddress->getLongitude(), $dropOffPassengerAddress->getLatitude(), $dropOffPassengerAddress->getLongitude())>=$this->minIdentityDistance) {
            $destinationsDistantEnough = true;
        }

        // The actors' origins must be distant enough Or The driver's destination and the passenger's drop off must be distant enough
        return ($originsDistantEnough || $destinationsDistantEnough);
    }
    
    /**
     * Create and return the pending proofs for the given period.
     * Used to generate non-realtime proofs.
     *
     * @param DateTime $fromDate   The start of the period for which we want to get the proofs
     * @param DateTime $toDate     The end of the period  for which we want to get the proofs
     * @return array    The proofs
     */
    private function getProofs(DateTime $fromDate, DateTime $toDate)
    {
        // first we search the pending asks for the given period
        $pendingAsks = $this->askRepository->findPendingAsksForPeriod($fromDate, $toDate);

        // then we search the accepted asks for the given period
        $acceptedAsks = $this->askRepository->findAcceptedAsksForPeriod($fromDate, $toDate);

        // we merge both arrays
        $asks = array_merge($pendingAsks, $acceptedAsks);

        // then we create the corresponding proofs
        foreach ($asks as $ask) {
            // we first check if both carpooler have a phone number, as it's mandatory !
            if (is_null($ask->getUser()->getTelephone()) || is_null($ask->getUserRelated()->getTelephone())) {
                continue;
            }
            if ($ask->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                // punctual, only one carpool proof
                // we search if a carpool proof already exists for the date
                if (!$this->carpoolProofRepository->findByAskAndDate($ask, $ask->getCriteria()->getFromDate())) {
                    // no carpool for this date, we create it
                    $carpoolProof = new CarpoolProof();
                    $carpoolProof->setStatus(CarpoolProof::STATUS_PENDING);
                    $carpoolProof->setType(CarpoolProof::TYPE_LOW);
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
                    $endDate->modify('+' . $destinationWaypoint->getDuration() . ' second');
                    $carpoolProof->setEndDriverDate($endDate);
                    /**
                     * @var Datetime $pickUpDate
                     */
                    // we init the pickup date with the start date of the driver
                    $pickUpDate = clone $ask->getCriteria()->getFromDate();
                    $pickUpDate->setTime($ask->getCriteria()->getFromTime()->format('H'), $ask->getCriteria()->getFromTime()->format('i'));
                    // then we add the duration till the pickup point
                    $pickUpDate->modify('+' . $pickUpWaypoint->getDuration() . ' second');
                    /**
                     * @var Datetime $dropOffDate
                     */
                    // we init the dropoff date with the start date of the driver
                    $dropOffDate = clone $startDate;
                    // then we add the duration till the dropoff point
                    $dropOffDate->modify('+' . $dropOffWaypoint->getDuration() . ' second');
                    $carpoolProof->setPickUpPassengerDate($pickUpDate);
                    $carpoolProof->setDropOffPassengerDate($dropOffDate);
                    $this->entityManager->persist($carpoolProof);
                }
            } else {
                // regular, we need to create a carpool item for each day between fromDate (or the ask fromDate if it's after the given fromDate) and toDate
                $curDate = clone max($fromDate, $ask->getCriteria()->getFromDate());
                $continue = true;
                // we get some available information here outside the loop
                $originWaypoint = $this->waypointRepository->findMinPositionForAskAndRole($ask, Waypoint::ROLE_DRIVER);
                $destinationWaypoint = $this->waypointRepository->findMaxPositionForAskAndRole($ask, Waypoint::ROLE_DRIVER);
                $pickUpWaypoint = $this->waypointRepository->findMinPositionForAskAndRole($ask, Waypoint::ROLE_PASSENGER);
                $dropOffWaypoint = $this->waypointRepository->findMaxPositionForAskAndRole($ask, Waypoint::ROLE_PASSENGER);
                while ($continue) {
                    // we search if a carpool proof already exists for the date
                    if (!$this->carpoolProofRepository->findByAskAndDate($ask, $curDate)) {
                        // no carpool for this date, we create it if it's a carpool day
                        $carpoolDay = false;
                        /**
                         * @var Datetime $startDate
                         */
                        $startDate = clone $curDate;
                        /**
                         * @var Datetime $pickUpDate
                         */
                        // we init the pickup date with the start date of the driver
                        $pickUpDate = clone $curDate;
                        switch ($curDate->format('w')) {
                            // we check for each date of the period if it's a carpoool day
                            case 0:     // sunday
                                if ($ask->getCriteria()->isSunCheck()) {
                                    // we check if time is set, could not be the case if ask criteria is different than proposal or matching criteria
                                    // if time is not set we consider that the day is not carpooled
                                    if (is_null($ask->getCriteria()->getSunTime())) {
                                        continue;
                                    }
                                    $carpoolDay = true;
                                    $startDate->setTime($ask->getCriteria()->getSunTime()->format('H'), $ask->getCriteria()->getSunTime()->format('i'));
                                    $pickUpDate->setTime($ask->getCriteria()->getSunTime()->format('H'), $ask->getCriteria()->getSunTime()->format('i'));
                                }
                                break;
                            case 1:     // monday
                                if ($ask->getCriteria()->isMonCheck()) {
                                    // we check if time is set, could not be the case if ask criteria is different than proposal or matching criteria
                                    // if time is not set we consider that the day is not carpooled
                                    if (is_null($ask->getCriteria()->getMonTime())) {
                                        continue;
                                    }
                                    $carpoolDay = true;
                                    $startDate->setTime($ask->getCriteria()->getMonTime()->format('H'), $ask->getCriteria()->getMonTime()->format('i'));
                                    $pickUpDate->setTime($ask->getCriteria()->getMonTime()->format('H'), $ask->getCriteria()->getMonTime()->format('i'));
                                }
                                break;
                            case 2:     // tuesday
                                if ($ask->getCriteria()->isTueCheck()) {
                                    // we check if time is set, could not be the case if ask criteria is different than proposal or matching criteria
                                    // if time is not set we consider that the day is not carpooled
                                    if (is_null($ask->getCriteria()->getTueTime())) {
                                        continue;
                                    }
                                    $carpoolDay = true;
                                    $startDate->setTime($ask->getCriteria()->getTueTime()->format('H'), $ask->getCriteria()->getTueTime()->format('i'));
                                    $pickUpDate->setTime($ask->getCriteria()->getTueTime()->format('H'), $ask->getCriteria()->getTueTime()->format('i'));
                                }
                                break;
                            case 3:     // wednesday
                                if ($ask->getCriteria()->isWedCheck()) {
                                    // we check if time is set, could not be the case if ask criteria is different than proposal or matching criteria
                                    // if time is not set we consider that the day is not carpooled
                                    if (is_null($ask->getCriteria()->getWedTime())) {
                                        continue;
                                    }
                                    $carpoolDay = true;
                                    $startDate->setTime($ask->getCriteria()->getWedTime()->format('H'), $ask->getCriteria()->getWedTime()->format('i'));
                                    $pickUpDate->setTime($ask->getCriteria()->getWedTime()->format('H'), $ask->getCriteria()->getWedTime()->format('i'));
                                }
                                break;
                            case 4:     // thursday
                                if ($ask->getCriteria()->isThuCheck()) {
                                    // we check if time is set, could not be the case if ask criteria is different than proposal or matching criteria
                                    // if time is not set we consider that the day is not carpooled
                                    if (is_null($ask->getCriteria()->getThuTime())) {
                                        continue;
                                    }
                                    $carpoolDay = true;
                                    $startDate->setTime($ask->getCriteria()->getThuTime()->format('H'), $ask->getCriteria()->getThuTime()->format('i'));
                                    $pickUpDate->setTime($ask->getCriteria()->getThuTime()->format('H'), $ask->getCriteria()->getThuTime()->format('i'));
                                }
                                break;
                            case 5:     // friday
                                if ($ask->getCriteria()->isFriCheck()) {
                                    // we check if time is set, could not be the case if ask criteria is different than proposal or matching criteria
                                    // if time is not set we consider that the day is not carpooled
                                    if (is_null($ask->getCriteria()->getFriTime())) {
                                        continue;
                                    }
                                    $carpoolDay = true;
                                    $startDate->setTime($ask->getCriteria()->getFriTime()->format('H'), $ask->getCriteria()->getFriTime()->format('i'));
                                    $pickUpDate->setTime($ask->getCriteria()->getFriTime()->format('H'), $ask->getCriteria()->getFriTime()->format('i'));
                                }
                                break;
                            case 6:     // saturday
                                if ($ask->getCriteria()->isSatCheck()) {
                                    // we check if time is set, could not be the case if ask criteria is different than proposal or matching criteria
                                    // if time is not set we consider that the day is not carpooled
                                    if (is_null($ask->getCriteria()->getSatTime())) {
                                        continue;
                                    }
                                    $carpoolDay = true;
                                    $startDate->setTime($ask->getCriteria()->getSatTime()->format('H'), $ask->getCriteria()->getSatTime()->format('i'));
                                    $pickUpDate->setTime($ask->getCriteria()->getSatTime()->format('H'), $ask->getCriteria()->getSatTime()->format('i'));
                                }
                                break;
                        }
                        if ($carpoolDay) {
                            $carpoolProof = new CarpoolProof();
                            $carpoolProof->setStatus(CarpoolProof::STATUS_PENDING);
                            $carpoolProof->setType(CarpoolProof::TYPE_LOW);
                            $carpoolProof->setAsk($ask);
                            $carpoolProof->setDriver($ask->getMatching()->getProposalOffer()->getUser());
                            $carpoolProof->setPassenger($ask->getMatching()->getProposalRequest()->getUser());
                            $carpoolProof->setOriginDriverAddress(clone $originWaypoint->getAddress());
                            $carpoolProof->setDestinationDriverAddress(clone $destinationWaypoint->getAddress());
                            $carpoolProof->setPickUpPassengerAddress(clone $pickUpWaypoint->getAddress());
                            $carpoolProof->setDropOffPassengerAddress(clone $dropOffWaypoint->getAddress());
                            
                            $carpoolProof->setStartDriverDate($startDate);
                            /**
                             * @var Datetime $endDate
                             */
                            // we init the end date with the start date
                            $endDate = clone $startDate;
                            // then we add the duration till the destination point
                            $endDate->modify('+' . $destinationWaypoint->getDuration() . ' second');
                            $carpoolProof->setEndDriverDate($endDate);
                            // we add the duration till the pickup point
                            $pickUpDate->modify('+' . $pickUpWaypoint->getDuration() . ' second');
                            /**
                             * @var Datetime $dropOffDate
                             */
                            // we init the dropoff date with the start date of the driver
                            $dropOffDate = clone $startDate;
                            // then we add the duration till the dropoff point
                            $dropOffDate->modify('+' . $dropOffWaypoint->getDuration() . ' second');
                            $carpoolProof->setPickUpPassengerDate($pickUpDate);
                            $carpoolProof->setDropOffPassengerDate($dropOffDate);
                            $this->entityManager->persist($carpoolProof);
                        }
                    }

                    if ($curDate->format('Y-m-d') == $toDate->format('Y-m-d')) {
                        $continue = false;
                    } else {
                        $curDate->modify('+1 day');
                    }
                }
            }
        }
        $this->entityManager->flush();

        // we return all the pending proofs
        return $this->carpoolProofRepository->findBy(['status'=>CarpoolProof::STATUS_PENDING]);
    }
}
