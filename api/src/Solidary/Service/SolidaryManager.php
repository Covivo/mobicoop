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

namespace App\Solidary\Service;

use App\Carpool\Entity\Ad;
use App\Carpool\Entity\Criteria;
use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Service\AdManager;
use App\Geography\Entity\Address;
use App\Geography\Repository\AddressRepository;
use App\Solidary\Entity\Need;
use App\Solidary\Entity\Proof;
use App\Solidary\Entity\Solidary;
use App\Solidary\Entity\SolidaryAsksListItem;
use App\Solidary\Entity\SolidarySearch;
use App\Solidary\Event\SolidaryCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use App\Solidary\Event\SolidaryUpdatedEvent;
use App\Solidary\Exception\SolidaryException;
use App\Solidary\Repository\SolidaryAskRepository;
use App\Solidary\Repository\SolidaryRepository;
use App\Solidary\Repository\SolidaryUserRepository;
use Symfony\Component\Security\Core\Security;
use App\Solidary\Entity\SolidaryAsk;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Entity\SolidaryUserStructure;
use App\Solidary\Repository\SolidaryUserStructureRepository;
use App\Solidary\Repository\StructureProofRepository;
use App\Solidary\Repository\StructureRepository;
use App\User\Entity\User;
use App\User\Service\UserManager;
use App\User\Repository\UserRepository;
use DateTime;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class SolidaryManager
{
    private $entityManager;
    private $eventDispatcher;
    private $security;
    private $solidaryRepository;
    private $solidaryAskRepository;
    private $solidaryUserRepository;
    private $adManager;
    private $solidaryMatcher;
    private $addressRepository;
    private $proposalRepository;
    private $solidaryUserStructureRepository;
    private $userManager;
    private $userRepository;
    private $structureProofRepository;
    private $structureRepository;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher, Security $security, SolidaryRepository $solidaryRepository, SolidaryUserRepository $solidaryUserRepository, AdManager $adManager, SolidaryMatcher $solidaryMatcher, SolidaryAskRepository $solidaryAskRepository, AddressRepository $addressRepository, ProposalRepository $proposalRepository, SolidaryUserStructureRepository $solidaryUserStructureRepository, UserManager $userManager, UserRepository $userRepository, StructureProofRepository $structureProofRepository, StructureRepository $structureRepository)
    {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->security = $security;
        $this->solidaryRepository = $solidaryRepository;
        $this->solidaryUserRepository = $solidaryUserRepository;
        $this->adManager = $adManager;
        $this->solidaryMatcher = $solidaryMatcher;
        $this->solidaryAskRepository = $solidaryAskRepository;
        $this->addressRepository = $addressRepository;
        $this->proposalRepository = $proposalRepository;
        $this->solidaryUserStructureRepository = $solidaryUserStructureRepository;
        $this->userManager = $userManager;
        $this->userRepository = $userRepository;
        $this->structureProofRepository = $structureProofRepository;
        $this->structureRepository = $structureRepository;
    }

    public function getSolidary($id): ?Solidary
    {
        $solidary = $this->solidaryRepository->find($id);

        // we get the origin and destination associated to the demand
        $solidary->setOrigin(json_decode(json_encode($solidary->getProposal()->getWaypoints()[0]->getAddress()), true));
        $solidary->setDestination(json_decode(json_encode($solidary->getProposal()->getWaypoints()[1]->getAddress()), true));
        
        // we get the solidaryuser associated to the demand
        $solidary->setSolidaryUser($solidary->getSolidaryUserStructure()->getSolidaryUser());
        
        // we get the date and time associated to the demand
        $outwardDatetime = $solidary->getProposal()->getCriteria()->getFromDate();
        $outwardDealineDatetime = null;
        $returnDatetime = null;
        $returnDealineDatetime = null;
        $outwardHours = null;
        $outwardMinutes = null;
        // we set time if it's a regular proposal
        if ($solidary->getProposal()->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
            if ($solidary->getProposal()->getCriteria()->isMonCheck()) {
                $outwardHours = $solidary->getProposal()->getCriteria()->getMonTime()->format('h');
                $outwardMinutes = $solidary->getProposal()->getCriteria()->getMonTime()->format('i');
            } elseif ($solidary->getProposal()->getCriteria()->isTueCheck()) {
                $outwardHours = $solidary->getProposal()->getCriteria()->getTueTime()->format('h');
                $outwardMinutes = $solidary->getProposal()->getCriteria()->getTueTime()->format('i');
            } elseif ($solidary->getProposal()->getCriteria()->isWedCheck()) {
                $outwardHours = $solidary->getProposal()->getCriteria()->getWedTime()->format('h');
                $outwardMinutes = $solidary->getProposal()->getCriteria()->getWedTime()->format('i');
            } elseif ($solidary->getProposal()->getCriteria()->isThuCheck()) {
                $outwardHours = $solidary->getProposal()->getCriteria()->getThuTime()->format('h');
                $outwardMinutes = $solidary->getProposal()->getCriteria()->getThuTime()->format('i');
            } elseif ($solidary->getProposal()->getCriteria()->isFriCheck()) {
                $outwardHours = $solidary->getProposal()->getCriteria()->getFriTime()->format('h');
                $outwardMinutes = $solidary->getProposal()->getCriteria()->getFriTime()->format('i');
            } elseif ($solidary->getProposal()->getCriteria()->isSatCheck()) {
                $outwardHours = $solidary->getProposal()->getCriteria()->getSatTime()->format('h');
                $outwardMinutes = $solidary->getProposal()->getCriteria()->getSatTime()->format('i');
            } elseif ($solidary->getProposal()->getCriteria()->isSunCheck()) {
                $outwardHours = $solidary->getProposal()->getCriteria()->getSunTime()->format('h');
                $outwardMinutes = $solidary->getProposal()->getCriteria()->getSunTime()->format('i');
            }
            // we set the limit date and time of the regular
            $outwardDealineDatetime = $solidary->getProposal()->getCriteria()->getToDate();
            date_time_set($outwardDealineDatetime, $outwardHours, $outwardMinutes);
            $solidary->setOutwardDeadlineDatetime($outwardDealineDatetime);
        } else {
            $outwardHours = $solidary->getProposal()->getCriteria()->getFromTime()->format('h');
            $outwardMinutes = $solidary->getProposal()->getCriteria()->getFromTime()->format('i');
        }
        
        date_time_set($outwardDatetime, $outwardHours, $outwardMinutes);
        $solidary->setOutwardDatetime($outwardDatetime);

        // we do the same if we have a return
        if ($solidary->getProposal()->getProposalLinked() !== null) {
            $returnDatetime = $solidary->getProposal()->getProposalLinked()->getCriteria()->getFromDate();
            $returnHours = null;
            $returnMinutes = null;
            if ($solidary->getProposal()->getProposalLinked()->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                if ($solidary->getProposal()->getProposalLinked()->getCriteria()->isMonCheck()) {
                    $returnHours = $solidary->getProposal()->getProposalLinked()->getCriteria()->getMonTime()->format('h');
                    $returnMinutes = $solidary->getProposal()->getProposalLinked()->getCriteria()->getMonTime()->format('i');
                } elseif ($solidary->getProposal()->getProposalLinked()->getCriteria()->isTueCheck()) {
                    $returnHours = $solidary->getProposal()->getProposalLinked()->getCriteria()->getTueTime()->format('h');
                    $returnMinutes = $solidary->getProposal()->getProposalLinked()->getCriteria()->getTueTime()->format('i');
                } elseif ($solidary->getProposal()->getProposalLinked()->getCriteria()->isWedCheck()) {
                    $returnHours = $solidary->getProposal()->getProposalLinked()->getCriteria()->getWedTime()->format('h');
                    $returnMinutes = $solidary->getProposal()->getProposalLinked()->getCriteria()->getWedTime()->format('i');
                } elseif ($solidary->getProposal()->getProposalLinked()->getCriteria()->isThuCheck()) {
                    $returnHours = $solidary->getProposal()->getProposalLinked()->getCriteria()->getThuTime()->format('h');
                    $returnMinutes = $solidary->getProposal()->getProposalLinked()->getCriteria()->getThuTime()->format('i');
                } elseif ($solidary->getProposal()->getProposalLinked()->getCriteria()->isFriCheck()) {
                    $returnHours = $solidary->getProposal()->getProposalLinked()->getCriteria()->getFriTime()->format('h');
                    $returnMinutes = $solidary->getProposal()->getProposalLinked()->getCriteria()->getFriTime()->format('i');
                } elseif ($solidary->getProposal()->getProposalLinked()->getCriteria()->isSatCheck()) {
                    $returnHours = $solidary->getProposal()->getProposalLinked()->getCriteria()->getSatTime()->format('h');
                    $returnMinutes = $solidary->getProposal()->getProposalLinked()->getCriteria()->getSatTime()->format('i');
                } elseif ($solidary->getProposal()->getProposalLinked()->getCriteria()->isSunCheck()) {
                    $returnHours = $solidary->getProposal()->getProposalLinked()->getCriteria()->getSunTime()->format('h');
                    $returnMinutes = $solidary->getProposal()->getProposalLinked()->getCriteria()->getSunTime()->format('i');
                }
                // we set the limit date and time of the regular
                $returnDealineDatetime = $solidary->getProposal()->getProposalLinked()->getCriteria()->getToDate();
                date_time_set($returnDealineDatetime, $returnHours, $returnMinutes);
                // we get the return deadline date and time
                $solidary->setReturnDeadlineDatetime($returnDealineDatetime);
            } else {
                $returnHours = $solidary->getProposal()->getProposalLinked()->getCriteria()->getFromTime()->format('h');
                $returnMinutes = $solidary->getProposal()->getProposalLinked()->getCriteria()->getFromTime()->format('i');
            }
            date_time_set($returnDatetime, $returnHours, $returnMinutes);
            // we get the return date and time
            $solidary->setReturnDatetime($returnDatetime);
        }
        $days = ['mon' => false, 'tue' => false,'wed' => false,'thu' => false,'fri' => false, 'sat' => false, 'sun' => false];
        $criteria = $solidary->getProposal()->getCriteria();
        if ($solidary->getProposal()->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
            if ($criteria->isMonCheck()) {
                $days['mon'] = true;
            }
            if ($criteria->isTueCheck()) {
                $days['tue'] = true;
            }
            if ($criteria->isWedCheck()) {
                $days['wed'] = true;
            }
            if ($criteria->isThuCheck()) {
                $days['thu'] = true;
            }
            if ($criteria->isFriCheck()) {
                $days['fri'] = true;
            }
            if ($criteria->isSatCheck()) {
                $days['sat'] = true;
            }
            if ($criteria->isSunCheck()) {
                $days['sun'] = true;
            }
            $solidary->setDays($days);
        }

        // $solidary->setSolutions();
        $solidary->setAsksList($this->solidaryManager->getAsksList($solidary->getId()));

        // We find the last entry of diary for this solidary to get the progression
        // $diariesEntires = $this->solidaryRepository->getDiaries($solidary);
        // (count($diariesEntires)>0) ? $solidary->setProgression($diariesEntires[0]->getProgression()) : $solidary->setProgression(0);

        return $solidary;
    }

    public function getSolidaries(): ?array
    {
        // $solidaryUserStrutures = $structure->getSolidaryUserStructure();
        // $solidaryUsers = []
        // foreach ($solidaryUserStructures as $solidaryUserStructure) {
        //     $solidaryUsers[] = $solidaryUserStructures->getSolidaryUser;
        // }
        // $solidaries = [];
        // foreach ($solidaryUsers as $solidaryUser) {
        //     $solidaries[] = $solidaryUser->getSolidary();
        // }
        
        // {
        //     $solidaries[] = $this->getSolidary();
        // }

        // return $solidaries;
    }


    /**
     * Create a solidary
     *
     * @param Solidary $solidary
     * @return Solidary
     */
    public function createSolidary(Solidary $solidary)
    {
        
        // We create a new user if necessary if it's a demand from the front
        $userId = null;
        if ($solidary->getEmail()) {
            $user = $this->solidaryCreateUser($solidary);
            $userId = $user->getId();
        }
        
        // Create an ad and get the associated proposal
        $ad = $this->createJourneyFromSolidary($solidary, $userId);
        $proposal = $this->proposalRepository->find($ad->getId());

        // we get solidaryUserStructure
        $solidaryStructureId = $solidary->getStructure() ? substr($solidary->getStructure(), strrpos($solidary->getStructure(), '/') + 1) : $this->security->getUser()->getSolidaryStructures()[0]->getId();
        $solidaryUserId = $solidary->getSolidaryUser() ? $solidary->getSolidaryUser()->getId() : $user->getSolidaryUser()->getId();
        $solidaryUserStructure = $this->solidaryUserStructureRepository->findByStructureAndSolidaryUser($solidaryStructureId, $solidaryUserId);

        // we check if we have a deadline if yes we update solidary
        if ($solidary->getOutwardDeadlineDatetime()) {
            $solidary->setDeadlineDate($solidary->getOutwardDeadlineDatetime());
        }

        // we update solidary
        $solidary->setProposal($proposal);
        $solidary->setSolidaryUserStructure($solidaryUserStructure[0]);

        $this->entityManager->persist($solidary);
        $this->entityManager->flush();

        // We trigger the event
        $event = new SolidaryCreatedEvent($solidary->getSolidaryUserStructure()->getSolidaryUser()->getUser(), $this->security->getUser());
        $this->eventDispatcher->dispatch(SolidaryCreatedEvent::NAME, $event);

        return $solidary;
    }

    public function updateSolidary(Solidary $solidary)
    {
        // We trigger the event
        $event = new SolidaryUpdatedEvent($solidary);
        $this->eventDispatcher->dispatch(SolidaryUpdatedEvent::NAME, $event);

        $this->entityManager->persist($solidary);
        $this->entityManager->flush();
    }

    /**
     * Get the results for a Solidary Transport Search
     *
     * @param SolidarySearch $solidarySearch
     * @return SolidarySearch
     */
    public function getSolidaryTransportSearchResults(SolidarySearch $solidarySearch): SolidarySearch
    {
        $solidarySearch->setResults($this->solidaryUserRepository->findForASolidaryTransportSearch($solidarySearch));
        
        return $solidarySearch;
    }

    /**
     * Get the results for a Solidary Carpool Search
     *
     * @param SolidarySearch $solidarySearch
     * @return SolidarySearch
     */
    public function getSolidaryCarpoolSearchSearchResults(SolidarySearch $solidarySearch): SolidarySearch
    {

        // We make an Ad from the proposal linked to the solidary (if it's on the return, we take the ProposalLinked)
        // I'll have the results directly in the Ad
        
        if ($solidarySearch->getWay()=="outward") {
            $proposal = $solidarySearch->getSolidary()->getProposal();
        } else {
            if (!is_null($solidarySearch->getSolidary()->getProposal()->getProposalLinked())) {
                $proposal = $solidarySearch->getSolidary()->getProposal()->getProposalLinked();
            }
            throw new SolidaryException(SolidaryException::NO_RETURN_PROPOSAL);
        }

        // If the proposal doesn't have any destination, we duplicate the origin waypoint
        $waypoints = $proposal->getWaypoints();
        $withDestination = false;
        foreach ($waypoints as $waypoint) {
            if ($waypoint->isDestination()) {
                $withDestination=true;
            }
        }
        if (!$withDestination) {
            // We clone the first waypoint and we use it as destination
            $newDestination = clone $waypoints[0];
            $newDestination->setDestination(true);
            $proposal->addWaypoint(clone $newDestination);
        }

        $ad = $this->adManager->makeAd($proposal, $proposal->getUser()->getId());
        // echo count($ad->getResults());die;
        // We need to build and persist all the new results as SolidaryMatching.
        $solidaryMatchings = $this->solidaryMatcher->buildSolidaryMatchingsForCarpool($solidarySearch->getSolidary(), $ad->getResults());

        // We make Solidary Results out of the Ad's results
        $results = [];
        foreach ($solidaryMatchings as $solidaryMatching) {
            $results[] = $this->solidaryMatcher->buildSolidaryResultCarpool($solidaryMatching);
        }
        $solidarySearch->setResults($results);

        return $solidarySearch;
    }

    /**
     * Get the solidary solutions of a solidary
     *
     * @param int $solidaryId Id of the Solidary
     * @return array|null
     */
    public function getSolidarySolutions(int $solidaryId): array
    {
        return $this->solidaryRepository->findSolidarySolutions($solidaryId);
    }

    /**
     * Get the list of all the Asks (Solidary or not) linked to a Solidary
     *
     * @param integer $solidaryId
     * @return Solidary
     */
    public function getAsksList(int $solidaryId): Solidary
    {
        $asksList = [];

        $solidaryAsks = $this->solidaryAskRepository->findSolidaryAsks($solidaryId);

        foreach ($solidaryAsks as $solidaryAsk) {

            /**
             * @var SolidaryAsk $solidaryAsk
             */

            $solidaryAsksItem = new SolidaryAsksListItem();

            $askCriteria = $askCriteriaReturn = null;
            if (!is_null($solidaryAsk->getSolidarySolution()->getSolidaryMatching()->getMatching())) {
                // Carpool
                $user = $solidaryAsk->getSolidarySolution()->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser();
                $askCriteria = $solidaryAsk->getCriteria();
                if (!is_null($solidaryAsk->getSolidarySolution()->getSolidaryMatching()->getMatching()->getMatchingLinked())) {
                    $askCriteriaReturn = $solidaryAsk->getSolidarySolution()->getSolidaryMatching()->getMatching()->getMatchingLinked()->getCriteria();
                }

                $solidaryAsksItem->setDriverType(SolidaryAsksListItem::DRIVER_TYPE_CARPOOLER);
            } else {
                // Solidary Transport
                $user = $solidaryAsk->getSolidarySolution()->getSolidaryMatching()->getSolidaryUser()->getUser();
                $askCriteria = $solidaryAsk->getCriteria();

                $solidaryAsksItem->setDriverType(SolidaryAsksListItem::DRIVER_TYPE_VOLUNTEER);
            }

            // Messages
            $messages = [];
            foreach ($solidaryAsk->getSolidaryAskHistories() as $solidaryAskHistory) {
                $messages[$solidaryAskHistory->getMessage()->getContent()] = $solidaryAskHistory->getMessage()->getDate();
            }
            var_dump($messages);
            die;
            // Frequency
            $solidaryAsksItem->setFrequency($askCriteria->getFrequency());
            
            // Status
            $solidaryAsksItem->setStatus($solidaryAsk->getStatus());

            // FromDate, to date
            $solidaryAsksItem->setFromDate($askCriteria->getFromDate());
            $days = ["mon","tue","wed","thu","fri","sat","sun"];

            $schedule = [];
            if ($askCriteria->getFrequency()==Criteria::FREQUENCY_REGULAR) {
                // Regular journey
                $solidaryAsksItem->setToDate($askCriteria->getToDate());
                
                $schedule = $this->adManager->getScheduleFromCriteria($askCriteria, $askCriteriaReturn);
            } else {
                // Punctual journey
                $schedule[0]['outwardTime'] = $askCriteria->getFromTime()->format("H:i");
                if (!is_null($askCriteriaReturn)) {
                    $schedule[0]['returnTime'] = $askCriteriaReturn->getFromTime()->format("H:i");
                }
                // init days
                foreach ($days as $day) {
                    $schedule[0][$day] = false;
                }
                if ($askCriteria->isMonCheck()) {
                    $schedule[0]["mon"]=true;
                }
                if ($askCriteria->isTueCheck()) {
                    $schedule[0]["tue"]=true;
                }
                if ($askCriteria->isWedCheck()) {
                    $schedule[0]["wed"]=true;
                }
                if ($askCriteria->isThuCheck()) {
                    $schedule[0]["thu"]=true;
                }
                if ($askCriteria->isFriCheck()) {
                    $schedule[0]["fri"]=true;
                }
                if ($askCriteria->isSatCheck()) {
                    $schedule[0]["sat"]=true;
                }
                if ($askCriteria->isSunCheck()) {
                    $schedule[0]["sun"]=true;
                }
            }

            $solidaryAsksItem->setSchedule($schedule);

            // The driver
            $solidaryAsksItem->setDriver($user->getGivenName()." ".$user->getFamilyName());
            $solidaryAsksItem->setTelephone($user->getTelephone());

            // SolidarySolutionId (usefull for a SolidaryContact)
            $solidaryAsksItem->setSolidarySolutionId($solidaryAsk->getSolidarySolution()->getId());

            $asksList[] = $solidaryAsksItem;
        }

        $solidary = $this->getSolidary($solidaryId);
        $solidary->setAsksList($asksList);

        return $solidary;
    }

    /**
     * We create the ad associated to the solidary
     *
     * @param Solidary $solidary
     * @return Ad
     */
    private function createJourneyFromSolidary(Solidary $solidary, int $userId = null): Ad
    {
        $ad = new Ad;

        // we get and set the origin and destination of the demand
        $origin = new Address;
        $destination = null;
        if (isset($solidary->getOrigin()['iri'])) {
            $origin = clone $this->addressRepository->find(substr($solidary->getOrigin()['iri'], strrpos($solidary->getOrigin()['iri'], '/') + 1));
        } else {
            $origin->setHouseNumber($solidary->getOrigin()['houseNumber']);
            $origin->setStreet($solidary->getOrigin()['street']);
            $origin->setStreetAddress($solidary->getOrigin()['streetAddress']);
            $origin->setPostalCode($solidary->getOrigin()['postalCode']);
            $origin->setSubLocality($solidary->getOrigin()['subLocality']);
            $origin->setAddressLocality($solidary->getOrigin()['addressLocality']);
            $origin->setLocalAdmin($solidary->getOrigin()['localAdmin']);
            $origin->setCounty($solidary->getOrigin()['county']);
            $origin->setMacroCounty($solidary->getOrigin()['macroCounty']);
            $origin->setRegion($solidary->getOrigin()['region']);
            $origin->setMacroRegion($solidary->getOrigin()['macroRegion']);
            $origin->setAddressCountry($solidary->getOrigin()['addressCountry']);
            $origin->setCountryCode($solidary->getOrigin()['countryCode']);
            $origin->setLatitude($solidary->getOrigin()['latitude']);
            $origin->setLongitude($solidary->getOrigin()['longitude']);
        }

        if ($solidary->getDestination() && isset($solidary->getDestination()['iri'])) {
            $destination = clone $this->addressRepository->find(substr($solidary->getDestination()['iri'], strrpos($solidary->getDestination()['iri'], '/') + 1));
        } elseif ($solidary->getDestination()) {
            $destination = new Address();
            $destination->setHouseNumber($solidary->getDestination()['houseNumber']);
            $destination->setStreet($solidary->getDestination()['street']);
            $destination->setStreetAddress($solidary->getDestination()['streetAddress']);
            $destination->setPostalCode($solidary->getDestination()['postalCode']);
            $destination->setSubLocality($solidary->getDestination()['subLocality']);
            $destination->setAddressLocality($solidary->getDestination()['addressLocality']);
            $destination->setLocalAdmin($solidary->getDestination()['localAdmin']);
            $destination->setCounty($solidary->getDestination()['county']);
            $destination->setMacroCounty($solidary->getDestination()['macroCounty']);
            $destination->setRegion($solidary->getDestination()['region']);
            $destination->setMacroRegion($solidary->getDestination()['macroRegion']);
            $destination->setAddressCountry($solidary->getDestination()['addressCountry']);
            $destination->setCountryCode($solidary->getDestination()['countryCode']);
            $destination->setLatitude($solidary->getDestination()['latitude']);
            $destination->setLongitude($solidary->getDestination()['longitude']);
        }
        
        // Role is always passenger since it's a solidary demand
        $ad->setRole(Ad::ROLE_PASSENGER);

        // round-trip
        $ad->setOneWay(true);
        if ($solidary->getReturnDatetime()) {
            $ad->setOneWay(false);
        }

        // Frequency
        $ad->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
        // We set the date and time of the demand
        $ad->setOutwardDate($solidary->getOutwardDatetime());
        $ad->setReturnDate($solidary->getReturnDatetime() ? $solidary->getReturnDatetime() : null);
        $ad->setOutwardTime($solidary->getOutwardDatetime()->format("H:i"));
        $ad->setReturnTime($solidary->getReturnDatetime() ? $solidary->getReturnDatetime()->format("H:i") : null);
        if ($solidary->getFrequency() === criteria::FREQUENCY_REGULAR) {
            $ad->setFrequency(Criteria::FREQUENCY_REGULAR);

            // we set the schedule and the limit date of the regular demand
            $ad->setOutwardLimitDate($solidary->getOutwardDeadlineDatetime());
            $ad->setReturnLimitDate($solidary->getReturnDeadlineDatetime() ? $solidary->getReturnDeadlineDatetime() : null);
            // Schedule
            $schedule = [];
            $days = $solidary->getDays();
            foreach ($days as $day => $value) {
                $schedule[0][$day] = $value;
            }
            $schedule[0]['outwardTime'] = $solidary->getOutwardDatetime()->format("H:i");
            $schedule[0]['returnTime'] =$solidary->getReturnDatetime() ? $solidary->getReturnDatetime()->format("H:i") : null;

            $ad->setSchedule($schedule);
        }

        // If the destination is not specified we use the origin
        if ($destination == null) {
            $destination = $origin;
        }
        // Outward waypoint
        $outwardWaypoints = [
            clone $origin,
            clone $destination
        ];

        $ad->setOutwardWaypoints($outwardWaypoints);

        // return waypoint
        $returnWaypoints = [
            clone $destination,
            clone $origin
        ];

        $ad->setReturnWaypoints($returnWaypoints);

        // The User
        $ad->setUserId($userId ? $userId : $solidary->getSolidaryUser()->getUser()->getId());

        return $this->adManager->createAd($ad, true);
    }

    /**
     * We create the user associate to the solidary demand if the user is not already created
     * We also create the solidaryUser associated if necessary
     *
     * @param Solidary $solidary
     * @return User
     */
    private function solidaryCreateUser(Solidary $solidary): User
    {

        // We check if the user exist
        $user = $this->userRepository->findOneBy(['email'=>$solidary->getEmail()]);
        if ($user == null) {
            // We create a new user
            $user = new User();
            $user->setEmail($solidary->getEmail());
            $user->setPassword($solidary->getPassword());
            $user->setGivenName($solidary->getGivenName());
            $user->setFamilyName($solidary->getFamilyName());
            $user->setBirthDate($solidary->getBirthDate());
            $user->setTelephone($solidary->getTelephone());
            $user->setGender($solidary->getGender());
            
            // we add homeAddress to the user
            $homeAddress = new Address;
            $homeAddress->setHouseNumber($solidary->getHomeAddress()['houseNumber']);
            $homeAddress->setStreet($solidary->getHomeAddress()['street']);
            $homeAddress->setStreetAddress($solidary->getHomeAddress()['streetAddress']);
            $homeAddress->setPostalCode($solidary->getHomeAddress()['postalCode']);
            $homeAddress->setSubLocality($solidary->getHomeAddress()['subLocality']);
            $homeAddress->setAddressLocality($solidary->getHomeAddress()['addressLocality']);
            $homeAddress->setLocalAdmin($solidary->getHomeAddress()['localAdmin']);
            $homeAddress->setCounty($solidary->getHomeAddress()['county']);
            $homeAddress->setMacroCounty($solidary->getHomeAddress()['macroCounty']);
            $homeAddress->setRegion($solidary->getHomeAddress()['region']);
            $homeAddress->setMacroRegion($solidary->getHomeAddress()['macroRegion']);
            $homeAddress->setAddressCountry($solidary->getHomeAddress()['addressCountry']);
            $homeAddress->setCountryCode($solidary->getHomeAddress()['countryCode']);
            $homeAddress->setLatitude($solidary->getHomeAddress()['latitude']);
            $homeAddress->setLongitude($solidary->getHomeAddress()['longitude']);
            
            $homeAddress->setHome(true);
            $user->addAddress($homeAddress);

            $user = $this->userManager->registerUser($user);
        }
        // We also create the solidaryUser associated to the demand
        if (is_null($user->getSolidaryUser())) {
            $solidaryUser = new SolidaryUser();
            $solidaryUser->setBeneficiary(true);
            $solidaryUser->setAddress(clone $user->getAddresses()[0]);
            $user->setSolidaryUser($solidaryUser);
        } else {
            $solidaryUser = $user->getSolidaryUser();
        }

        // We create the solidaryUserStructure associated to the demand
        $solidaryUserStructure = new SolidaryUserStructure();
        $structure = $this->structureRepository->find(substr($solidary->getStructure(), strrpos($solidary->getStructure(), '/') + 1));
        $solidaryUserStructure->setStructure($structure);
        $solidaryUserStructure->setSolidaryUser($solidaryUser);

        // We add the proofs associated to the demand
        foreach ($solidary->getProofs() as $givenProof) {
            // We get the structure proof and we create a proof to persist
            $structureProofId = null;
            if (strrpos($givenProof['id'], '/')) {
                $structureProofId = substr($givenProof['id'], strrpos($givenProof['id'], '/') + 1);
            }
                
            $structureProof = $this->structureProofRepository->find($structureProofId);
            if (!is_null($structureProof) && isset($givenProof['value']) && !is_null($givenProof['value'])) {
                $proof = new Proof();
                $proof->setStructureProof($structureProof);
                $proof->setValue($givenProof['value']);
                $solidaryUserStructure->addProof($proof);
            }
        }

        $solidaryUser->addSolidaryUserStructure($solidaryUserStructure);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $user;
    }
}
