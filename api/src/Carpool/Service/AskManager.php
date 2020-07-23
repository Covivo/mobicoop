<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

use App\Carpool\Entity\Ad;
use App\Carpool\Entity\Ask;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Carpool\Event\AskPostedEvent;
use App\Carpool\Event\AskUpdatedEvent;
use App\Carpool\Entity\AskHistory;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Matching;
use App\Carpool\Event\AskAcceptedEvent;
use App\Carpool\Event\AskRefusedEvent;
use App\Carpool\Repository\AskRepository;
use App\Carpool\Repository\MatchingRepository;
use App\Communication\Entity\Message;
use App\Communication\Entity\Recipient;
use App\Payment\Exception\PaymentException;
use App\Solidary\Entity\SolidaryAsk;
use App\Solidary\Entity\SolidaryAskHistory;
use App\User\Entity\User;

/**
 * Ask manager service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class AskManager
{
    private $eventDispatcher;
    private $entityManager;
    private $matchingRepository;
    private $askRepository;
    private $resultManager;
    private $logger;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, EntityManagerInterface $entityManager, MatchingRepository $matchingRepository, AskRepository $askRepository, ResultManager $resultManager, LoggerInterface $logger)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->entityManager = $entityManager;
        $this->matchingRepository = $matchingRepository;
        $this->askRepository = $askRepository;
        $this->resultManager = $resultManager;
        $this->logger = $logger;
    }

    /**
     * Get an ask by its id
     *
     * @param integer $id   The id of the ask to find
     * @return Ask          The ask found or null if not found
     */
    public function getAsk(int $id)
    {
        return $this->askRepository->find($id);
    }
    
    /**
     * Create an ask.
     *
     */
    public function createAsk(Ask $ask)
    {
        // todo : check if an ask already exists for the match and the proposals
        
        $this->entityManager->persist($ask);
        // dispatch en event
        // $event = new AskPostedEvent($ask);
        // $this->eventDispatcher->dispatch(AskPostedEvent::NAME, $event);
        return $ask;
    }

    /**
     * Update an ask.
     *
     */
    public function updateAsk(Ask $ask)
    {
        // todo : check if an ask already exists for the match and the proposals
        
        $this->entityManager->persist($ask);

        $this->createAssociatedAskHistory($ask);

        // dispatch en event
        $event = new AskUpdatedEvent($ask);
        $this->eventDispatcher->dispatch(AskUpdatedEvent::NAME, $event);
        return $ask;
    }

    /**
     * Create the associated AskHistory of an Ask
     */
    private function createAssociatedAskHistory(Ask $ask)
    {
        $askHistory = new AskHistory();
        
        $askHistory->setStatus($ask->getStatus());
        $askHistory->setType($ask->getType());
        $askHistory->setAsk($ask);

        $this->entityManager->persist($askHistory);

        return $askHistory;
    }

    /**
     * Create an ask from an ad.
     *
     * @param Ad $ad        The ad used to create the ask
     * @param bool $formal  The ask is a formal ask
     * @return Ad
     */
    public function createAskFromAd(Ad $ad, bool $formal)
    {
        $ask = new Ask();
        $matching = $this->matchingRepository->find($ad->getMatchingId());
        
        if ($ad->getAdId() == $matching->getProposalOffer()->getId()) {
            // the carpooler is the driver, the requester is the passenger
            $ask->setType($matching->getProposalRequest()->getType());
            $ask->setUser($matching->getProposalRequest()->getUser());
        } else {
            // the carpooler is the passenger, the requester is the driver
            $ask->setType($matching->getProposalOffer()->getType());
            $ask->setUser($matching->getProposalOffer()->getUser());
        }
        
        if ($formal) {
            // if it's a formal ask, the status is pending, depending on the role
            $ask->setStatus($ad->getRole() == Ad::ROLE_DRIVER ? Ask::STATUS_PENDING_AS_DRIVER : Ask::STATUS_PENDING_AS_PASSENGER);
        } else {
            // if it's not a formal ask, the status is initiated
            $ask->setStatus(Ask::STATUS_INITIATED);
        }

        $ask->setMatching($matching);

        // we use the matching criteria
        $criteria = clone $matching->getCriteria();

        // we treat the outward

        // for regular trips we need to check the dates and days
        if ($matching->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
            if ($ad->getOutwardDate()) {
                $criteria->setFromDate($ad->getOutwardDate());
            }
            if ($ad->getOutwardLimitDate()) {
                $criteria->setToDate($ad->getOutwardLimitDate());
            }
            // we init the original schedule
            $criteria->setMonCheck(false);
            $criteria->setTueCheck(false);
            $criteria->setWedCheck(false);
            $criteria->setThuCheck(false);
            $criteria->setFriCheck(false);
            $criteria->setSatCheck(false);
            $criteria->setSunCheck(false);
            // we use the driver times (the passenger times will be computed using these times and the chosen days)
            $criteria->setMonTime($matching->getProposalOffer()->getCriteria()->getMonTime());
            $criteria->setTueTime($matching->getProposalOffer()->getCriteria()->getTueTime());
            $criteria->setWedTime($matching->getProposalOffer()->getCriteria()->getWedTime());
            $criteria->setThuTime($matching->getProposalOffer()->getCriteria()->getThuTime());
            $criteria->setFriTime($matching->getProposalOffer()->getCriteria()->getFriTime());
            $criteria->setSatTime($matching->getProposalOffer()->getCriteria()->getSatTime());
            $criteria->setSunTime($matching->getProposalOffer()->getCriteria()->getSunTime());
            if ($ad->getRole() != Ad::ROLE_DRIVER_OR_PASSENGER) {
                // we fill the selected days if a role has been set
                foreach ($ad->getSchedule() as $schedule) {
                    if ($schedule['outwardTime'] != '') {
                        if (isset($schedule['mon']) && $schedule['mon']) {
                            $criteria->setMonCheck(true);
                        }
                        if (isset($schedule['tue']) && $schedule['tue']) {
                            $criteria->setTueCheck(true);
                        }
                        if (isset($schedule['wed']) && $schedule['wed']) {
                            $criteria->setWedCheck(true);
                        }
                        if (isset($schedule['thu']) && $schedule['thu']) {
                            $criteria->setThuCheck(true);
                        }
                        if (isset($schedule['fri']) && $schedule['fri']) {
                            $criteria->setFriCheck(true);
                        }
                        if (isset($schedule['sat']) && $schedule['sat']) {
                            $criteria->setSatCheck(true);
                        }
                        if (isset($schedule['sun']) && $schedule['sun']) {
                            $criteria->setSunCheck(true);
                        }
                    }
                }
            }
        }

        $ask->setCriteria($criteria);
        
        // we use the matching waypoints
        $waypoints = $matching->getWaypoints();
        foreach ($waypoints as $waypoint) {
            $newWaypoint = clone $waypoint;
            $ask->addWaypoint($newWaypoint);
        }

        // Ask History
        $askHistory = new AskHistory();
        $askHistory->setStatus($ask->getStatus());
        $askHistory->setType($ask->getType());
        $ask->addAskHistory($askHistory);

        // opposite ask ?
        if ($ad->getRole() == Ad::ROLE_DRIVER_OR_PASSENGER && $matching->getMatchingOpposite()) {
            // no role has been defined, we create the opposite ask
            $askOpposite = new Ask();
            $criteriaOpposite = clone $matching->getMatchingOpposite()->getCriteria();
    
            if ($ad->getAdId() == $matching->getMatchingOpposite()->getProposalOffer()->getId()) {
                // the carpooler is the driver, the requester is the passenger
                $askOpposite->setType($matching->getMatchingOpposite()->getProposalRequest()->getType());
                $askOpposite->setUser($matching->getMatchingOpposite()->getProposalRequest()->getUser());
            } else {
                // the carpooler is the passenger, the requester is the driver
                $askOpposite->setType($matching->getMatchingOpposite()->getProposalOffer()->getType());
                $askOpposite->setUser($matching->getMatchingOpposite()->getProposalOffer()->getUser());
            }

            $askOpposite->setStatus($ask->getStatus());
            $askOpposite->setMatching($matching->getMatchingOpposite());
            
            // for regular trips we need to check the dates and days
            if ($matching->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                if ($ad->getOutwardDate()) {
                    $criteriaOpposite->setFromDate($ad->getOutwardDate());
                }
                if ($ad->getOutwardLimitDate()) {
                    $criteriaOpposite->setToDate($ad->getOutwardLimitDate());
                }
                // we init the original schedule
                $criteriaOpposite->setMonCheck(false);
                $criteriaOpposite->setTueCheck(false);
                $criteriaOpposite->setWedCheck(false);
                $criteriaOpposite->setThuCheck(false);
                $criteriaOpposite->setFriCheck(false);
                $criteriaOpposite->setSatCheck(false);
                $criteriaOpposite->setSunCheck(false);
                // we use the driver times (the passenger times will be computed using these times and the chosen days)
                $criteriaOpposite->setMonTime($matching->getMatchingOpposite()->getProposalOffer()->getCriteria()->getMonTime());
                $criteriaOpposite->setTueTime($matching->getMatchingOpposite()->getProposalOffer()->getCriteria()->getTueTime());
                $criteriaOpposite->setWedTime($matching->getMatchingOpposite()->getProposalOffer()->getCriteria()->getWedTime());
                $criteriaOpposite->setThuTime($matching->getMatchingOpposite()->getProposalOffer()->getCriteria()->getThuTime());
                $criteriaOpposite->setFriTime($matching->getMatchingOpposite()->getProposalOffer()->getCriteria()->getFriTime());
                $criteriaOpposite->setSatTime($matching->getMatchingOpposite()->getProposalOffer()->getCriteria()->getSatTime());
                $criteriaOpposite->setSunTime($matching->getMatchingOpposite()->getProposalOffer()->getCriteria()->getSunTime());
            }

            $askOpposite->setCriteria($criteriaOpposite);

            // we use the matching waypoints
            $waypoints = $matching->getMatchingOpposite()->getWaypoints();
            foreach ($waypoints as $waypoint) {
                $newWaypoint = clone $waypoint;
                $askOpposite->addWaypoint($newWaypoint);
            }

            $ask->setAskOpposite($askOpposite);
        }

        // we treat the return (only for regular trips for now)
        if ($matching->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR && $matching->getMatchingLinked()) {
            $askReturn = new Ask();
            $criteriaReturn = clone $matching->getMatchingLinked()->getCriteria();

            $askReturn->setType(Proposal::TYPE_RETURN);
            $askReturn->setUser($ask->getUser());
            $askReturn->setStatus($ask->getStatus());
            $askReturn->setMatching($matching->getMatchingLinked());

            if ($ad->getOutwardDate()) {
                $criteriaReturn->setFromDate($ad->getOutwardDate());
            }
            if ($ad->getOutwardLimitDate()) {
                $criteriaReturn->setToDate($ad->getOutwardLimitDate());
            }
            
            // we init the original schedule
            $criteriaReturn->setMonCheck(false);
            $criteriaReturn->setTueCheck(false);
            $criteriaReturn->setWedCheck(false);
            $criteriaReturn->setThuCheck(false);
            $criteriaReturn->setFriCheck(false);
            $criteriaReturn->setSatCheck(false);
            $criteriaReturn->setSunCheck(false);
            // we use the driver times (the passenger times will be computed using these times and the chosen days)
            $criteriaReturn->setMonTime($matching->getMatchingLinked()->getProposalOffer()->getCriteria()->getMonTime());
            $criteriaReturn->setTueTime($matching->getMatchingLinked()->getProposalOffer()->getCriteria()->getTueTime());
            $criteriaReturn->setWedTime($matching->getMatchingLinked()->getProposalOffer()->getCriteria()->getWedTime());
            $criteriaReturn->setThuTime($matching->getMatchingLinked()->getProposalOffer()->getCriteria()->getThuTime());
            $criteriaReturn->setFriTime($matching->getMatchingLinked()->getProposalOffer()->getCriteria()->getFriTime());
            $criteriaReturn->setSatTime($matching->getMatchingLinked()->getProposalOffer()->getCriteria()->getSatTime());
            $criteriaReturn->setSunTime($matching->getMatchingLinked()->getProposalOffer()->getCriteria()->getSunTime());
        
            // we fill the selected days
            if ($ad->getRole() != Ad::ROLE_DRIVER_OR_PASSENGER) {
                foreach ($ad->getSchedule() as $schedule) {
                    if ($schedule['returnTime'] != '') {
                        if (isset($schedule['mon']) && $schedule['mon']) {
                            $criteriaReturn->setMonCheck(true);
                        }
                        if (isset($schedule['tue']) && $schedule['tue']) {
                            $criteriaReturn->setTueCheck(true);
                        }
                        if (isset($schedule['wed']) && $schedule['wed']) {
                            $criteriaReturn->setWedCheck(true);
                        }
                        if (isset($schedule['thu']) && $schedule['thu']) {
                            $criteriaReturn->setThuCheck(true);
                        }
                        if (isset($schedule['fri']) && $schedule['fri']) {
                            $criteriaReturn->setFriCheck(true);
                        }
                        if (isset($schedule['sat']) && $schedule['sat']) {
                            $criteriaReturn->setSatCheck(true);
                        }
                        if (isset($schedule['sun']) && $schedule['sun']) {
                            $criteriaReturn->setSunCheck(true);
                        }
                    }
                }
            }

            $askReturn->setCriteria($criteriaReturn);

            // we use the matching waypoints
            $waypoints = $matching->getMatchingLinked()->getWaypoints();
            foreach ($waypoints as $waypoint) {
                $newWaypoint = clone $waypoint;
                $askReturn->addWaypoint($newWaypoint);
            }

            // opposite return ask ?
            if ($ad->getRole() == Ad::ROLE_DRIVER_OR_PASSENGER && $matching->getMatchingLinked()->getMatchingOpposite()) {
                // no role has been defined, we create the opposite ask
                $askReturnOpposite = new Ask();
                $criteriaReturnOpposite = clone $matching->getMatchingLinked()->getMatchingOpposite()->getCriteria();
        
                if ($ad->getAdId() == $matching->getMatchingLinked()->getMatchingOpposite()->getProposalOffer()->getId()) {
                    // the carpooler is the driver, the requester is the passenger
                    $askReturnOpposite->setType($matching->getMatchingLinked()->getMatchingOpposite()->getProposalRequest()->getType());
                    $askReturnOpposite->setUser($matching->getMatchingLinked()->getMatchingOpposite()->getProposalRequest()->getUser());
                } else {
                    // the carpooler is the passenger, the requester is the driver
                    $askReturnOpposite->setType($matching->getMatchingLinked()->getMatchingOpposite()->getProposalOffer()->getType());
                    $askReturnOpposite->setUser($matching->getMatchingLinked()->getMatchingOpposite()->getProposalOffer()->getUser());
                }

                $askReturnOpposite->setStatus($ask->getStatus());
                $askReturnOpposite->setMatching($matching->getMatchingLinked()->getMatchingOpposite());
                
                // for regular trips we need to check the dates and days
                if ($ad->getOutwardDate()) {
                    $criteriaReturnOpposite->setFromDate($ad->getOutwardDate());
                }
                if ($ad->getOutwardLimitDate()) {
                    $criteriaReturnOpposite->setToDate($ad->getOutwardLimitDate());
                }

                // we init the original schedule
                $criteriaReturnOpposite->setMonCheck(false);
                $criteriaReturnOpposite->setTueCheck(false);
                $criteriaReturnOpposite->setWedCheck(false);
                $criteriaReturnOpposite->setThuCheck(false);
                $criteriaReturnOpposite->setFriCheck(false);
                $criteriaReturnOpposite->setSatCheck(false);
                $criteriaReturnOpposite->setSunCheck(false);
                // we use the driver times (the passenger times will be computed using these times and the chosen days)
                $criteriaReturnOpposite->setMonTime($matching->getMatchingLinked()->getMatchingOpposite()->getProposalOffer()->getCriteria()->getMonTime());
                $criteriaReturnOpposite->setTueTime($matching->getMatchingLinked()->getMatchingOpposite()->getProposalOffer()->getCriteria()->getTueTime());
                $criteriaReturnOpposite->setWedTime($matching->getMatchingLinked()->getMatchingOpposite()->getProposalOffer()->getCriteria()->getWedTime());
                $criteriaReturnOpposite->setThuTime($matching->getMatchingLinked()->getMatchingOpposite()->getProposalOffer()->getCriteria()->getThuTime());
                $criteriaReturnOpposite->setFriTime($matching->getMatchingLinked()->getMatchingOpposite()->getProposalOffer()->getCriteria()->getFriTime());
                $criteriaReturnOpposite->setSatTime($matching->getMatchingLinked()->getMatchingOpposite()->getProposalOffer()->getCriteria()->getSatTime());
                $criteriaReturnOpposite->setSunTime($matching->getMatchingLinked()->getMatchingOpposite()->getProposalOffer()->getCriteria()->getSunTime());

                $askReturnOpposite->setCriteria($criteriaReturnOpposite);

                // we use the matching waypoints
                $waypoints = $matching->getMatchingLinked()->getMatchingOpposite()->getWaypoints();
                foreach ($waypoints as $waypoint) {
                    $newWaypoint = clone $waypoint;
                    $askReturnOpposite->addWaypoint($newWaypoint);
                }

                $askReturn->setAskOpposite($askReturnOpposite);
                if (isset($askOpposite)) {
                    $askReturnOpposite->setAskLinked($askOpposite);
                }
            }
            $ask->setAskLinked($askReturn);
        }
            
        $this->entityManager->persist($ask);
        $this->entityManager->flush($ask);
        
        
        if ($ask->getStatus() == Ask::STATUS_PENDING_AS_DRIVER || $ask->getStatus() == Ask::STATUS_PENDING_AS_PASSENGER) {
            // dispatch en event
            // get the complete ad to have data for the email
            $ad = $this->getAskFromAd($ask->getId(), $ask->getUser()->getId());
            $event = new AskPostedEvent($ad);
            $this->eventDispatcher->dispatch(AskPostedEvent::NAME, $event);
        }
        return $ad;
    }

    /**
     * Get an ask from an ad

     * @param int $askId    The ask id
     * @param int $userId   The user id of the user making the request
     * @return Ad       The ad for the ask with the computed results
     */
    public function getAskFromAd(int $askId, int $userId)
    {
        $ask = $this->askRepository->find($askId);
        $ad = new Ad();
        $ad->setUserId($userId);
        $ad->setAskId($askId);
        $ad->setAskStatus($ask->getStatus());
        $ad->setMatchingId($ask->getMatching()->getId());

        // first pass for role
        switch ($ask->getStatus()) {
            case Ask::STATUS_INITIATED:
                if ($ask->getMatching()->getProposalOffer()->getUser()->getId() == $userId) {
                    $ad->setRole(Ad::ROLE_DRIVER);
                } else {
                    $ad->setRole(Ad::ROLE_PASSENGER);
                }
                break;
            case Ask::STATUS_PENDING_AS_DRIVER:
            case Ask::STATUS_ACCEPTED_AS_DRIVER:
            case Ask::STATUS_DECLINED_AS_DRIVER:
                $ad->setRole($ask->getUser()->getId() == $userId ? Ad::ROLE_DRIVER : Ad::ROLE_PASSENGER);
                break;
            case Ask::STATUS_PENDING_AS_PASSENGER:
            case Ask::STATUS_ACCEPTED_AS_PASSENGER:
            case Ask::STATUS_DECLINED_AS_PASSENGER:
                $ad->setRole($ask->getUser()->getId() == $userId ? Ad::ROLE_PASSENGER : Ad::ROLE_DRIVER);
                break;
        }

        // second pass for 'update-able'
        switch ($ask->getStatus()) {
            case Ask::STATUS_INITIATED:
                if ($ask->getUser()->getId() == $userId) {
                    $ad->setCanUpdateAsk(true);
                } else {
                    $ad->setCanUpdateAsk(false);
                }
                break;
            case Ask::STATUS_PENDING_AS_DRIVER:
            case Ask::STATUS_PENDING_AS_PASSENGER:
                if ($ask->getUser()->getId() == $userId) {
                    $ad->setCanUpdateAsk(false);
                } else {
                    $ad->setCanUpdateAsk(true);
                }
                break;
            default:
                $ad->setCanUpdateAsk(false);
                break;
        }
        
        // we compute the results
        $ad->setResults([$this->resultManager->createAskResults($ask, $userId)]);

        return $ad;
    }

    /**
     * Update an ask from an ad
     *
     * @param Ad $ad        The body of the ad to use
     * @param int $adId     The id of the ad to use (not initialized in the body)
     * @param int $userId   The user id of the user making the update
     * @return Ad       The ad updated from the updated ask
     */
    public function updateAskFromAd(Ad $ad, int $adId, int $userId)
    {
        $ask = $this->askRepository->find($adId);
        
        // the ask posted is the master ask, we have to update all the asks linked :
        // - the related ask for return trip
        // - the opposite and return opposite if the role wasn't chosen
        $ad->setRole($ask->getUser()->getId() == $userId ? Ad::ROLE_DRIVER : Ad::ROLE_PASSENGER);
        $ask->setStatus($ad->getAskStatus());
        if ($ask->getAskLinked()) {
            $ask->getAskLinked()->setStatus($ad->getAskStatus());
        }
        if ($ask->getAskOpposite()) {
            $ask->getAskOpposite()->setStatus($ad->getAskStatus());
            if ($ask->getAskOpposite()->getAskLinked()) {
                $ask->getAskOpposite()->getAskLinked()->setStatus($ad->getAskStatus());
            }
        }
        if ($ad->getOutwardDate() && $ad->getOutwardLimitDate() && count($ad->getSchedule())>0) {
            // regular
            // we update the criteria of the master ask
            $ask->getCriteria()->setFromDate($ad->getOutwardDate());
            $ask->getCriteria()->setToDate($ad->getOutwardLimitDate());
            // we init the original schedule
            $ask->getCriteria()->setMonCheck(false);
            $ask->getCriteria()->setTueCheck(false);
            $ask->getCriteria()->setWedCheck(false);
            $ask->getCriteria()->setThuCheck(false);
            $ask->getCriteria()->setFriCheck(false);
            $ask->getCriteria()->setSatCheck(false);
            $ask->getCriteria()->setSunCheck(false);
            if ($ask->getAskLinked()) {
                $ask->getAskLinked()->getCriteria()->setMonCheck(false);
                $ask->getAskLinked()->getCriteria()->setTueCheck(false);
                $ask->getAskLinked()->getCriteria()->setWedCheck(false);
                $ask->getAskLinked()->getCriteria()->setThuCheck(false);
                $ask->getAskLinked()->getCriteria()->setFriCheck(false);
                $ask->getAskLinked()->getCriteria()->setSatCheck(false);
                $ask->getAskLinked()->getCriteria()->setSunCheck(false);
                $ask->getAskLinked()->getCriteria()->setFromDate($ad->getOutwardDate());
                $ask->getAskLinked()->getCriteria()->setToDate($ad->getOutwardLimitDate());
            }
            foreach ($ad->getSchedule() as $schedule) {
                if ($schedule['outwardTime'] != '') {
                    if (isset($schedule['mon']) && $schedule['mon']) {
                        $ask->getCriteria()->setMonCheck(true);
                    }
                    if (isset($schedule['tue']) && $schedule['tue']) {
                        $ask->getCriteria()->setTueCheck(true);
                    }
                    if (isset($schedule['wed']) && $schedule['wed']) {
                        $ask->getCriteria()->setWedCheck(true);
                    }
                    if (isset($schedule['thu']) && $schedule['thu']) {
                        $ask->getCriteria()->setThuCheck(true);
                    }
                    if (isset($schedule['fri']) && $schedule['fri']) {
                        $ask->getCriteria()->setFriCheck(true);
                    }
                    if (isset($schedule['sat']) && $schedule['sat']) {
                        $ask->getCriteria()->setSatCheck(true);
                    }
                    if (isset($schedule['sun']) && $schedule['sun']) {
                        $ask->getCriteria()->setSunCheck(true);
                    }
                }
                if ($ask->getAskLinked() && $schedule['returnTime'] != '') {
                    if (isset($schedule['mon']) && $schedule['mon']) {
                        $ask->getAskLinked()->getCriteria()->setMonCheck(true);
                    }
                    if (isset($schedule['tue']) && $schedule['tue']) {
                        $ask->getAskLinked()->getCriteria()->setTueCheck(true);
                    }
                    if (isset($schedule['wed']) && $schedule['wed']) {
                        $ask->getAskLinked()->getCriteria()->setWedCheck(true);
                    }
                    if (isset($schedule['thu']) && $schedule['thu']) {
                        $ask->getAskLinked()->getCriteria()->setThuCheck(true);
                    }
                    if (isset($schedule['fri']) && $schedule['fri']) {
                        $ask->getAskLinked()->getCriteria()->setFriCheck(true);
                    }
                    if (isset($schedule['sat']) && $schedule['sat']) {
                        $ask->getAskLinked()->getCriteria()->setSatCheck(true);
                    }
                    if (isset($schedule['sun']) && $schedule['sun']) {
                        $ask->getAskLinked()->getCriteria()->setSunCheck(true);
                    }
                }
            }
        }

        // Ask History
        $askHistory = new AskHistory();
        $askHistory->setStatus($ask->getStatus());
        $askHistory->setType($ask->getType());
        $ask->addAskHistory($askHistory);

        $this->entityManager->persist($ask);

       
        // If there is a SolidaryAsk we update it
        if (!is_null($ask->getSolidaryAsk())) {
            $solidaryAsk = $ask->getSolidaryAsk();
            if ($ad->getAskStatus()==Ask::STATUS_ACCEPTED_AS_DRIVER || $ad->getAskStatus()==Ask::STATUS_ACCEPTED_AS_PASSENGER) {
                $solidaryAsk->setStatus(SolidaryAsk::STATUS_ACCEPTED);
            } elseif ($ad->getAskStatus()==Ask::STATUS_DECLINED_AS_DRIVER || $ad->getAskStatus()==Ask::STATUS_DECLINED_AS_PASSENGER) {
                $solidaryAsk->setStatus(SolidaryAsk::STATUS_REFUSED);
            }

            // We clone the updated Criteria of the Ask
            $solidaryAsk->setCriteria(clone $ask->getCriteria());

            $solidaryAskHistory = new SolidaryAskHistory();
            $solidaryAskHistory->setSolidaryAsk($solidaryAsk);
            $solidaryAskHistory->setStatus($solidaryAsk->getStatus());

            $solidaryAsk->addSolidaryAskHistory($solidaryAskHistory);
            $this->entityManager->persist($solidaryAsk);
        }

        $this->entityManager->flush();

        // get the complete ad to have data for the email
        $ad = $this->getAskFromAd($ask->getId(), $userId);
        // dispatch en event
        if (($ask->getStatus() == Ask::STATUS_PENDING_AS_DRIVER) || ($ask->getStatus() == Ask::STATUS_PENDING_AS_PASSENGER)) {
            $event = new AskPostedEvent($ad);
            $this->eventDispatcher->dispatch(AskPostedEvent::NAME, $event);
        } elseif (($ask->getStatus() == Ask::STATUS_ACCEPTED_AS_DRIVER) || ($ask->getStatus() == Ask::STATUS_ACCEPTED_AS_PASSENGER)) {
            $event = new AskAcceptedEvent($ad);
            $this->eventDispatcher->dispatch(AskAcceptedEvent::NAME, $event);
        } elseif (($ask->getStatus() == Ask::STATUS_DECLINED_AS_DRIVER) || ($ask->getStatus() == Ask::STATUS_DECLINED_AS_PASSENGER)) {
            $event = new AskRefusedEvent($ad);
            $this->eventDispatcher->dispatch(AskRefusedEvent::NAME, $event);
        }
        return $ad;
    }

    public function getAsksFromProposal(Proposal $proposal)
    {
        $asks = [];

        if (!empty($proposal->getMatchingOffers())) {
            $offers = $proposal->getMatchingOffers();
            /** @var Matching $offer */
            foreach ($offers as $offer) {
                if (!empty($offer->getAsks())) {
                    $asks = array_merge($asks, $offer->getAsks());
                }
            }
        }

        if (!empty($proposal->getMatchingRequests())) {
            $requests = $proposal->getMatchingRequests();
            /** @var Matching $request */
            foreach ($requests as $request) {
                if (!empty($request->getAsks())) {
                    $asks = array_merge($asks, $request->getAsks());
                }
            }
        }
        
        return $asks;
    }
        

    /**
     * Ask user is considered driver if he has made a proposal offer
     *
     * @param Ask $ask
     * @return bool
     */
    public function isAskUserDriver(Ask $ask)
    {
        return $ask->getUser()->getId() === $ask->getMatching()->getProposalOffer()->getUser()->getId();
    }

    /**
     * Ask user is considered passenger if he has made a proposal request
     *
     * @param Ask $ask
     * @return bool
     */
    public function isAskUserPassenger(Ask $ask)
    {
        return $ask->getUser()->getId() === $ask->getMatching()->getProposalRequest()->getUser()->getId();
    }

    /**
     * Get a simplified ask from an ad
     * @param int $askId The ask id
     * @param int $userId The user id of the user making the request
     * @param Proposal|null $proposal - We can give a Proposal if we need these data in results,
     * for example if my Ad is based on an ask and I need the proposal data in results
     * @return Ad       The ad for the ask with the computed results
     */
    public function getSimpleAskFromAd(int $askId, int $userId, ?Proposal $proposal = null)
    {
        $ask = $this->askRepository->find($askId);
        $ad = new Ad();
        $ad->setUserId($userId);
        $ad->setAskId($askId);
        $ad->setAskStatus($ask->getStatus());
        $ad->setOutwardLimitDate($ask->getCriteria()->getToDate());

        // first pass for role
        switch ($ask->getStatus()) {
            case Ask::STATUS_ACCEPTED_AS_DRIVER:
                $ad->setRole($ask->getUser()->getId() == $userId ? Ad::ROLE_DRIVER : Ad::ROLE_PASSENGER);
                break;
            case Ask::STATUS_ACCEPTED_AS_PASSENGER:
                $ad->setRole($ask->getUser()->getId() == $userId ? Ad::ROLE_PASSENGER : Ad::ROLE_DRIVER);
                break;
        }
        // we compute the results
        if ($proposal) {
            $results = array_merge([$this->resultManager->createSimpleAskResults($ask, $userId, $ad->getRole())], $this->resultManager->createAdResults($proposal));
            $ad->setResults($results);
        } else {
            $ad->setResults([$this->resultManager->createSimpleAskResults($ask, $userId, $ad->getRole())]);
        }

        return $ad;
    }




    /************
    *   DYNAMIC *
    *************/

    /**
     * Check if a user has a pending dynamic ad ask.
     *
     * @param User $user The user
     * @return boolean
     */
    public function hasPendingDynamicAsk(User $user)
    {
        // first we get all the asks initiated by the user
        $asks = $this->askRepository->findBy(['user'=>$user,'status'=>[Ask::STATUS_PENDING_AS_PASSENGER,Ask::STATUS_ACCEPTED_AS_DRIVER]]);
        // now we check if one of these asks is related to a dynamic ad, not finished
        foreach ($asks as $ask) {
            // if the user is passenger
            if ($ask->getUser()->getId() == $user->getId() && $ask->getMatching()->getProposalRequest()->isDynamic() && !$ask->getMatching()->getProposalRequest()->isFinished()) {
                return true;
            }
            // if the user is driver
            if ($ask->getUserRelated()->getId() == $user->getId() && $ask->getMatching()->getProposalOffer()->isDynamic() && !$ask->getMatching()->getProposalOffer()->isFinished()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a user has a refused dynamic ad ask related to a given matching.
     *
     * @param User $user The user
     * @return boolean
     */
    public function hasRefusedDynamicAsk(User $user, Matching $matching)
    {
        // first we get all the asks initiated by the user and refused by the carpooler
        $asks = $this->askRepository->findBy(['user'=>$user,'status'=>[Ask::STATUS_DECLINED_AS_DRIVER]]);
        // now we check if one of these asks is related to the given matching
        foreach ($asks as $ask) {
            if ($ask->getMatching()->getId() == $matching->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * Get the payment status of an Ask
     *
     * @param integer $id   Id of the Ask to check
     * @return Ask
     */
    public function getPaymentStatus(int $id): Ask
    {
        // search the ask
        if (!$ask = $this->getAsk($id)) {
            throw new PaymentException(PaymentException::NO_ASK_FOUND);
        }

        if ($ask->getCriteria()->getFrequency()==Criteria::FREQUENCY_PUNCTUAL) {
            // Puntual journey, we just check if it's paid
        } else {
            // Regular journey. To be paid, all the previous week must have been confirmed
        }

        return $ask;
    }
}
