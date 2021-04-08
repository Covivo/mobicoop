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

use App\Action\Entity\Action;
use App\Auth\Entity\AuthItem;
use App\Auth\Entity\UserAuthAssignment;
use App\Auth\Repository\AuthItemRepository;
use App\Carpool\Ressource\Ad;
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
use App\Solidary\Entity\SolidarySolution;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Entity\SolidaryUserStructure;
use App\Solidary\Entity\Structure;
use App\Solidary\Repository\SolidaryUserStructureRepository;
use App\Solidary\Repository\StructureProofRepository;
use App\Solidary\Repository\StructureRepository;
use App\User\Entity\User;
use App\User\Service\UserManager;
use App\User\Repository\UserRepository;
use DateTime;
use App\Solidary\Entity\SolidaryVolunteerPlanning\SolidaryVolunteerPlanning;
use App\Solidary\Entity\SolidaryVolunteerPlanning\SolidaryVolunteerPlanningItem;
use Negotiation\Accept;

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
    private $authItemRepository;

    public function __construct(EntityManagerInterface $entityManager, EventDispatcherInterface $eventDispatcher, Security $security, SolidaryRepository $solidaryRepository, SolidaryUserRepository $solidaryUserRepository, AdManager $adManager, SolidaryMatcher $solidaryMatcher, SolidaryAskRepository $solidaryAskRepository, AddressRepository $addressRepository, ProposalRepository $proposalRepository, SolidaryUserStructureRepository $solidaryUserStructureRepository, UserManager $userManager, UserRepository $userRepository, StructureProofRepository $structureProofRepository, StructureRepository $structureRepository, AuthItemRepository $authItemRepository)
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
        $this->authItemRepository = $authItemRepository;
    }

    public function getSolidary($id): ?Solidary
    {
        $solidary = $this->solidaryRepository->find($id);
        if (empty($solidary)) {
            throw new SolidaryException(SolidaryException::UNKNOWN_SOLIDARY);
        }

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
        $outwardTimes = ['mon'=>null,'tue'=>null,'wed'=>null,'thu'=>null,"fri"=>null,"sat"=>null,"sun"=>null];
        // we set time if it's a regular proposal
        if ($solidary->getProposal()->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
            if ($solidary->getProposal()->getCriteria()->isMonCheck()) {
                $outwardHours = $solidary->getProposal()->getCriteria()->getMonTime()->format('H');
                $outwardMinutes = $solidary->getProposal()->getCriteria()->getMonTime()->format('i');
                $outwardTimes['mon'] = $solidary->getProposal()->getCriteria()->getMonTime()->format('H:i');
            }
            if ($solidary->getProposal()->getCriteria()->isTueCheck()) {
                $outwardHours = $solidary->getProposal()->getCriteria()->getTueTime()->format('H');
                $outwardMinutes = $solidary->getProposal()->getCriteria()->getTueTime()->format('i');
                $outwardTimes['tue'] = $solidary->getProposal()->getCriteria()->getTueTime()->format('H:i');
            }
            if ($solidary->getProposal()->getCriteria()->isWedCheck()) {
                $outwardHours = $solidary->getProposal()->getCriteria()->getWedTime()->format('H');
                $outwardMinutes = $solidary->getProposal()->getCriteria()->getWedTime()->format('i');
                $outwardTimes['wed'] = $solidary->getProposal()->getCriteria()->getWedTime()->format('H:i');
            }
            if ($solidary->getProposal()->getCriteria()->isThuCheck()) {
                $outwardHours = $solidary->getProposal()->getCriteria()->getThuTime()->format('H');
                $outwardMinutes = $solidary->getProposal()->getCriteria()->getThuTime()->format('i');
                $outwardTimes['thu'] = $solidary->getProposal()->getCriteria()->getThuTime()->format('H:i');
            }
            if ($solidary->getProposal()->getCriteria()->isFriCheck()) {
                $outwardHours = $solidary->getProposal()->getCriteria()->getFriTime()->format('H');
                $outwardMinutes = $solidary->getProposal()->getCriteria()->getFriTime()->format('i');
                $outwardTimes['fri'] = $solidary->getProposal()->getCriteria()->getFriTime()->format('H:i');
            }
            if ($solidary->getProposal()->getCriteria()->isSatCheck()) {
                $outwardHours = $solidary->getProposal()->getCriteria()->getSatTime()->format('H');
                $outwardMinutes = $solidary->getProposal()->getCriteria()->getSatTime()->format('i');
                $outwardTimes['sat'] = $solidary->getProposal()->getCriteria()->getSatTime()->format('H:i');
            }
            if ($solidary->getProposal()->getCriteria()->isSunCheck()) {
                $outwardHours = $solidary->getProposal()->getCriteria()->getSunTime()->format('H');
                $outwardMinutes = $solidary->getProposal()->getCriteria()->getSunTime()->format('i');
                $outwardTimes['sun'] = $solidary->getProposal()->getCriteria()->getSunTime()->format('H:i');
            }
            // we set the limit date and time of the regular
            $outwardDealineDatetime = $solidary->getProposal()->getCriteria()->getToDate();
            date_time_set($outwardDealineDatetime, $outwardHours, $outwardMinutes);
            $solidary->setOutwardDeadlineDatetime($outwardDealineDatetime);
            $solidary->setOutwardTimes($outwardTimes);
        } else {
            $outwardHours = $solidary->getProposal()->getCriteria()->getFromTime()->format('H');
            $outwardMinutes = $solidary->getProposal()->getCriteria()->getFromTime()->format('i');
        }
        
        date_time_set($outwardDatetime, $outwardHours, $outwardMinutes);
        $solidary->setOutwardDatetime($outwardDatetime);
        // we set the margin duration
        $solidary->setMarginDuration($solidary->getProposal()->getCriteria()->getMarginDuration());
        // we do the same if we have a return
        $returnTimes = ['mon'=>null,'tue'=>null,'wed'=>null,'thu'=>null,"fri"=>null,"sat"=>null,"sun"=>null];
        if ($solidary->getProposal()->getProposalLinked() !== null) {
            $returnDatetime = $solidary->getProposal()->getProposalLinked()->getCriteria()->getFromDate();
            $returnHours = null;
            $returnMinutes = null;
            if ($solidary->getProposal()->getProposalLinked()->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                if ($solidary->getProposal()->getProposalLinked()->getCriteria()->isMonCheck()) {
                    $returnHours = $solidary->getProposal()->getProposalLinked()->getCriteria()->getMonTime()->format('H');
                    $returnMinutes = $solidary->getProposal()->getProposalLinked()->getCriteria()->getMonTime()->format('i');
                    $returnTimes['mon'] = $solidary->getProposal()->getProposalLinked()->getCriteria()->getMonTime()->format('H:i');
                }
                if ($solidary->getProposal()->getProposalLinked()->getCriteria()->isTueCheck()) {
                    $returnHours = $solidary->getProposal()->getProposalLinked()->getCriteria()->getTueTime()->format('H');
                    $returnMinutes = $solidary->getProposal()->getProposalLinked()->getCriteria()->getTueTime()->format('i');
                    $returnTimes['tue'] = $solidary->getProposal()->getProposalLinked()->getCriteria()->getTueTime()->format('H:i');
                }
                if ($solidary->getProposal()->getProposalLinked()->getCriteria()->isWedCheck()) {
                    $returnHours = $solidary->getProposal()->getProposalLinked()->getCriteria()->getWedTime()->format('H');
                    $returnMinutes = $solidary->getProposal()->getProposalLinked()->getCriteria()->getWedTime()->format('i');
                    $returnTimes['wed'] = $solidary->getProposal()->getProposalLinked()->getCriteria()->getWedTime()->format('H:i');
                }
                if ($solidary->getProposal()->getProposalLinked()->getCriteria()->isThuCheck()) {
                    $returnHours = $solidary->getProposal()->getProposalLinked()->getCriteria()->getThuTime()->format('H');
                    $returnMinutes = $solidary->getProposal()->getProposalLinked()->getCriteria()->getThuTime()->format('i');
                    $returnTimes['thu'] = $solidary->getProposal()->getProposalLinked()->getCriteria()->getThuTime()->format('H:i');
                }
                if ($solidary->getProposal()->getProposalLinked()->getCriteria()->isFriCheck()) {
                    $returnHours = $solidary->getProposal()->getProposalLinked()->getCriteria()->getFriTime()->format('H');
                    $returnMinutes = $solidary->getProposal()->getProposalLinked()->getCriteria()->getFriTime()->format('i');
                    $returnTimes['fri'] = $solidary->getProposal()->getProposalLinked()->getCriteria()->getFriTime()->format('H:i');
                }
                if ($solidary->getProposal()->getProposalLinked()->getCriteria()->isSatCheck()) {
                    $returnHours = $solidary->getProposal()->getProposalLinked()->getCriteria()->getSatTime()->format('H');
                    $returnMinutes = $solidary->getProposal()->getProposalLinked()->getCriteria()->getSatTime()->format('i');
                    $returnTimes['sat'] = $solidary->getProposal()->getProposalLinked()->getCriteria()->getSatTime()->format('H:i');
                }
                if ($solidary->getProposal()->getProposalLinked()->getCriteria()->isSunCheck()) {
                    $returnHours = $solidary->getProposal()->getProposalLinked()->getCriteria()->getSunTime()->format('H');
                    $returnMinutes = $solidary->getProposal()->getProposalLinked()->getCriteria()->getSunTime()->format('i');
                    $returnTimes['sun'] = $solidary->getProposal()->getProposalLinked()->getCriteria()->getSunTime()->format('H:i');
                }
                // we set the limit date and time of the regular
                $returnDealineDatetime = $solidary->getProposal()->getProposalLinked()->getCriteria()->getToDate();
                date_time_set($returnDealineDatetime, $returnHours, $returnMinutes);
                // we get the return deadline date and time
                $solidary->setReturnDeadlineDatetime($returnDealineDatetime);
                $solidary->setReturnTimes($returnTimes);
            } else {
                $returnHours = $solidary->getProposal()->getProposalLinked()->getCriteria()->getFromTime()->format('H');
                $returnMinutes = $solidary->getProposal()->getProposalLinked()->getCriteria()->getFromTime()->format('i');
            }
            date_time_set($returnDatetime, $returnHours, $returnMinutes);
            // we get the return date and time
            $solidary->setReturnDatetime($returnDatetime);
        } else {
            // We juste set the returnTime array at null if there is no return
            $solidary->setReturnTimes($returnTimes);
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
        $solidary->setFrequency($solidary->getProposal()->getCriteria()->getFrequency());
       
        // we check the solidary is a demand or a volunteer proposal
        $solidary->setPassenger($solidary->getProposal()->getCriteria()->isPassenger() ? true : false);
        $solidary->setDriver($solidary->getProposal()->getCriteria()->isDriver() ? true : false);
       
        $solidary->setAsksList($this->getAsksList($solidary->getId()));
        // the display label of the solidary 'subject : origin -> destination'
        if ($solidary->getOrigin() && $solidary->getDestination()) {
            $solidary->setDisplayLabel($solidary->getSubject()->getLabel().": ".$solidary->getOrigin()['addressLocality']."->".$solidary->getDestination()['addressLocality']);
        }
        $solidary->setDisplayLabel($solidary->getSubject()->getLabel());
        // We find the last entry of diary for this solidary to get the progression and the author of the last update
        $solidary->setProgression(0);
        $solidary->setOperator(null);
        // we get all diaries order by DESC so the first one is the most recent
        $diariesEntries = $this->solidaryRepository->getDiaries($solidary);
        if (count($diariesEntries)>0) {
            $solidary->setProgression($diariesEntries[0]->getProgression());
            $solidary->setLastAction($diariesEntries[0]->getAction()->getName());
            foreach ($diariesEntries as $diary) {
                if ($diary->getAction()->getId() === Action::SOLIDARY_CREATE && $diary->getAuthor()->getId() !== $diary->getUser()->getId()) {
                    $solidary->setOperator($diary->getAuthor());
                }
            }
        }

        // we display solutions associated to the solidary.
        $solutions = [];
        $solidarySolutions = $solidary->getSolidarySolutions();
        foreach ($solidarySolutions as $solidarySolution) {
            $solution = [];
            if ($solidarySolution->getSolidaryMatching()->getSolidaryUser()) {
                $solution['id'] = $solidarySolution->getId();
                $solution['Type'] = SolidarySolution::TRANSPORTER;
                $solution['FamilyName'] = $solidarySolution->getSolidaryMatching()->getSolidaryUser()->getUser()->getFamilyName();
                $solution['GivenName'] = $solidarySolution->getSolidaryMatching()->getSolidaryUser()->getUser()->getGivenName();
                $solution['Telephone'] = $solidarySolution->getSolidaryMatching()->getSolidaryUser()->getUser()->getTelephone();
                $solution['UserId'] = $solidarySolution->getSolidaryMatching()->getSolidaryUser()->getUser()->getId();
            } elseif ($solidarySolution->getSolidaryMatching()->getMatching()) {
                $solution['id'] = $solidarySolution->getId();
                $solution['Type'] = SolidarySolution::CARPOOLER;
                $solution['FamilyName'] = $solidarySolution->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser()->getFamilyName();
                $solution['GivenName'] = $solidarySolution->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser()->getGivenName();
                $solution['Telephone'] = $solidarySolution->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser()->getTelephone();
                $solution['UserId'] = $solidarySolution->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser()->getId();
            }
            $solutions[]=$solution;
        }
        $solidary->setSolutions($solutions);

        return $solidary;
    }

    /**
     * Get solidaries of a user
     *
     * @param User $user
     * @return void
     */
    public function getMySolidaries(User $user)
    {
        $fullSolidaries = [];
        if (is_null($user->getSolidaryUser())) {
            throw new SolidaryException(SolidaryException:: NO_SOLIDARY_USER);
        }
        $solidaries = $user->getSolidaryUser()->getSolidaryUserStructures()[0]->getSolidaries();
        
        foreach ($solidaries as $solidary) {
            $fullSolidaries[] = $this->getSolidary($solidary->getId());
        }

        return $fullSolidaries;
    }

    /**
    *  Get solidaries of a structure and can be filtered by solidaryUser and/or progression
    *
    * @param Structure $structure
    * @param Int $solidaryUserId id of the solidaryUser
    * @param Int $progression level of progression
    * @return void
    */
    public function getSolidaries(Structure $structure, Int $solidaryUserId=null, Int $progression=null)
    {
        $solidaries = null;
        $fullSolidaries = [];
        $solidaryUserStructures = $structure->getSolidaryUserStructures();
        foreach ($solidaryUserStructures as $solidaryUserStructure) {
            // we check if we indicate a specific solidaryUser if yes we get only his solidaries
            if (!is_null($solidaryUserId)) {
                if ($solidaryUserStructure->getSolidaryUser()->getId() == $solidaryUserId) {
                    $solidaries = $solidaryUserStructure->getSolidaries();
                    if (!empty($solidaries)) {
                        foreach ($solidaries as $solidary) {
                            // we check if we indicate a progression if yes we get only solidaries with that progression
                            if (!is_null($progression)) {
                                if ($this->getSolidary($solidary->getId())->getProgression() == $progression) {
                                    $fullSolidaries[] = $this->getSolidary($solidary->getId());
                                }
                                // case without progression
                            } else {
                                $fullSolidaries[] = $this->getSolidary($solidary->getId());
                            }
                        }
                    }
                }
                // case without solidaryUser
            } else {
                $solidaries = $solidaryUserStructure->getSolidaries();
                if (!empty($solidaries)) {
                    foreach ($solidaries as $solidary) {
                        // we check if we indicate a progression if yes we get only solidaries with that progression
                        if (!is_null($progression)) {
                            if ($this->getSolidary($solidary->getId())->getProgression() == $progression) {
                                $fullSolidaries[] = $this->getSolidary($solidary->getId());
                            }
                            // case without progression
                        } else {
                            $fullSolidaries[] = $this->getSolidary($solidary->getId());
                        }
                    }
                }
            }
        }
        return $fullSolidaries;
    }

    /**
     * Create a solidary
     *
     * @param Solidary $solidary
     * @return Solidary
     */
    public function createSolidary(Solidary $solidary)
    {
        // set default role
        if (is_null($solidary->isPassenger()) && is_null($solidary->isDriver())) {
            $solidary->setPassenger(true);
        }
        
        // We create a new user if necessary if it's a demand from the front
        $userId = null;
        $user = null;

        // first we need to check if the associated structure as an email :
        // - if so the user needs an email OR phone number
        // - otherwise the email is mandatory
        $solidaryStructureId = $solidary->getStructure() ? substr($solidary->getStructure(), strrpos($solidary->getStructure(), '/') + 1) : $this->security->getUser()->getSolidaryStructures()[0]->getId();
        $structure = $this->structureRepository->find($solidaryStructureId);
        
        if (is_null($solidary->getEmail()) && is_null($solidary->getUser()) && is_null($solidary->getTelephone())) {
            throw new SolidaryException(SolidaryException::MANDATORY_EMAIL_OR_PHONE);
        }
        if (is_null($solidary->getEmail()) && is_null($structure->getEmail())) {
            throw new SolidaryException(SolidaryException::MANDATORY_EMAIL);
        }
        if ($solidary->getEmail() || ($structure->getEmail() && is_null($solidary->getEmail())) || $solidary->getUser()) {
            $user = $this->solidaryCreateUser($solidary, $structure);
            $userId = $user->getId();
        }
        
        // Create an ad and get the associated proposal
        $ad = $this->createJourneyFromSolidary($solidary, $userId);
        $proposal = $this->proposalRepository->find($ad->getId());

        // we get solidaryUserStructure
        $solidaryUserId = $solidary->getSolidaryUser() ? $solidary->getSolidaryUser()->getId() : $user->getSolidaryUser()->getId();
        $solidaryUserStructure = $this->solidaryUserStructureRepository->findByStructureAndSolidaryUser($solidaryStructureId, $solidaryUserId);

        // we check if we have a deadline if yes we update solidary
        if ($solidary->getOutwardDeadlineDatetime()) {
            $solidary->setDeadlineDate($solidary->getOutwardDeadlineDatetime());
        }

        // we update solidary
        $solidary->setProposal($proposal);
        $solidary->setSolidaryUserStructure($solidaryUserStructure[0]);

        if ($solidary->isPassenger()) {
            $this->entityManager->persist($solidary);
            $this->entityManager->flush();

            // We trigger the event
            $event = new SolidaryCreatedEvent($user ? $user : $solidary->getSolidaryUserStructure()->getSolidaryUser()->getUser(), $this->security->getUser(), $solidary);
            $this->eventDispatcher->dispatch(SolidaryCreatedEvent::NAME, $event);
        }
        

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
    public function getAsksList(int $solidaryId): array
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
                if ($solidaryAskHistory->getMessage() !== null) {
                    $userDelegate = $solidaryAskHistory->getMessage()->getUserDelegate();

                    $messages[] = [
                        "userDelegateId" => $userDelegate ? $userDelegate->getId() : null,
                        "userDelegateFamilyName" => $userDelegate ? $userDelegate->getFamilyName() : null,
                        "userDelegateGivenName" => $userDelegate ? $userDelegate->getGivenName() : null,
                        "userId" => $solidaryAskHistory->getMessage()->getUser()->getId(),
                        "userFamilyName" => $solidaryAskHistory->getMessage()->getUser()->getFamilyName(),
                        "userGivenName" => $solidaryAskHistory->getMessage()->getUser()->getGivenName(),
                        "text" => $solidaryAskHistory->getMessage()->getText(),
                        "createdDate" => $solidaryAskHistory->getMessage()->getCreatedDate()];
                }
            }
            $solidaryAsksItem->setMessages($messages);
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

        return $asksList;
    }

    /**
     * We create the ad associated to the solidary
     *
     * @param Solidary $solidary
     * @return Ad
     */
    private function createJourneyFromSolidary(Solidary $solidary, int $userId = null): Ad
    {
        $ad = new Ad();
        // we get and set the origin and destination of the demand
        $origin = new Address();
        $destination = null;
        
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
        
        if ($solidary->getDestination()) {
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
        
        // Set role of the ad
        if ($solidary->isPassenger() && $solidary->isDriver()) {
            $ad->setRole(Ad::ROLE_DRIVER_OR_PASSENGER);
        } elseif ($solidary->isDriver()) {
            $ad->setRole(Ad::ROLE_DRIVER);
        } else {
            $ad->setRole(Ad::ROLE_PASSENGER);
        }

        // round-trip
        $ad->setOneWay(true);

        // we set the ad as a solidary ad
        $ad->setSolidary(true);
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
            
            $days = $solidary->getDays();

            // Check if there is a outward time for each given day
            $outwardTimes = $solidary->getOutwardTimes();
            if (is_null($outwardTimes)) {
                throw new SolidaryException(SolidaryException::NO_OUTWARD_TIMES);
            }
            foreach ($days as $outwardDay => $outwardDayChecked) {
                if (
                    !array_key_exists($outwardDay, $outwardTimes) ||
                    ((bool)$outwardDayChecked && is_null($outwardTimes[$outwardDay]))
                ) {
                    throw new SolidaryException(SolidaryException::DAY_CHECK_BUT_NO_OUTWARD_TIME);
                }
            }
        
            if (!is_null($solidary->getReturnDatetime())) {
                $returnTimes = $solidary->getReturnTimes();
                if (is_null($returnTimes)) {
                    throw new SolidaryException(SolidaryException::NO_RETURN_TIMES);
                }

                // Check if there is a return time for each given day
                foreach ($days as $returnDay => $returnDayChecked) {
                    if (
                        !array_key_exists($returnDay, $returnTimes) ||
                        (true===$returnDayChecked && is_null($returnTimes[$returnDay]))
                    ) {
                        throw new SolidaryException(SolidaryException::DAY_CHECK_BUT_NO_RETURN_TIME);
                    }
                }
                $ad->setOneWay(false);
            }
            // We build the schedule
            $buildedSchedules = $this->buildSchedulesForAd($solidary->getDays(), $solidary->getOutwardTimes(), $solidary->getReturnTimes());

            $ad->setSchedule($buildedSchedules);
        }
        // we set the margin time of the demand
        $ad->setMarginDuration($solidary->getMarginDuration() ? $solidary->getMarginDuration() : null);

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

        // The subject
        $ad->setSubjectId($solidary->getSubject()->getId());

        return $this->adManager->createAd($ad);
    }

    
    /**
     * Build a schedule for an Ad from the Solidary $days and $outwardTimes/$returnTimes
     *
     * @param array $days   Solidary days
     * @param array $outwardTimes  Solidary $outwardTimes
     * @param array $returnTimes  Solidary $returnTimesTimes
     * @return array The builded schedules
     */
    private function buildSchedulesForAd(array $days, array $outwardTimes, ?array $returnTimes): array
    {
        $returnSchedules = [];
        
        foreach ($days as $day => $value) {
            $alreadySet = false;
            // Check if the day is checked
            if ($value == 1) {
                // Check if the current time has been already set in a sub schedule
                foreach ($returnSchedules as $key => $outwardSchedule) {
                    if ($outwardSchedule['outwardTime']==$outwardTimes[$day]) {
                        $alreadySet = true;
                        break;
                    }
                }

                if ($alreadySet) {
                    // Already set the time, we just keep the current day
                    $returnSchedules[$key][$day] = true;
                } else {
                    // Not set already, we create a new sub schedule
                    $returnSchedules[] = [
                        "outwardTime" => (isset($outwardTimes[$day])) ? $outwardTimes[$day] : null,
                        "returnTime" => (isset($returnTimes) && $returnTimes[$day]) ? $returnTimes[$day] : null,
                        $day => true
                    ];
                }
            }
        }
        
        return $returnSchedules;
    }
    
    /**
     * We create the user associate to the solidary demand if the user is not already created
     * We also create the solidaryUser associated if necessary
     *
     * @param Solidary $solidary    The solidary
     * @param Structure $structure  The structure (used for email generation if needed)
     * @return User
     */
    private function solidaryCreateUser(Solidary $solidary, Structure $structure): User
    {
        // We check if the user exist
        $user = $solidary->getUser();
        if (is_null($solidary->getUser())) {
            // no user provided
            if (!is_null($solidary->getEmail())) {
                // email provided
                $user = $this->userRepository->findOneBy(['email'=>$solidary->getEmail()]);
                $solidary->setUser($user);
            } elseif (!is_null($structure->getEmail())) {
                // no email provided => we try the structure email
                $solidary->setEmail($this->userManager->generateSubEmail($structure->getEmail()));
            }
        }
           
        // we set the home address
        $homeAddress = null;
        if ($solidary->getHomeAddress()) {
            $homeAddress = new Address();
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
        } elseif (!is_null($solidary->getUser()) && !is_null($solidary->getUser()->getAddresses())) {
            foreach ($solidary->getUser()->getAddresses() as $address) {
                if ($address->isHome()) {
                    $homeAddress = $address;
                    break;
                }
            }
        }
        if (is_null($homeAddress)) {
            throw new SolidaryException(SolidaryException::NO_HOME_ADDRESS);
        }
     
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
            $user->setNewsSubscription(true);
            
            $homeAddress->setHome(true);
            $user->addAddress($homeAddress);

            $user = $this->userManager->registerUser($user);
        }
        // We also create the solidaryUser associated to the demand
        if (is_null($user->getSolidaryUser())) {
            $solidaryUser = new SolidaryUser();
            if ($solidary->isDriver()) {
                $solidaryUser->setVolunteer(true);
                // we add the userAuthItemAssignment associated
                $authItem = $this->authItemRepository->find(AuthItem::ROLE_SOLIDARY_VOLUNTEER_CANDIDATE);
                $userAuthAssignment = new UserAuthAssignment();
                $userAuthAssignment->setAuthItem($authItem);
                $user->addUserAuthAssignment($userAuthAssignment);
            }
            if ($solidary->isPassenger()) {
                $solidaryUser->setBeneficiary(true);
                // we add the userAuthItemAssignment associated
                $authItem = $this->authItemRepository->find(AuthItem::ROLE_SOLIDARY_BENEFICIARY_CANDIDATE);
                $userAuthAssignment = new UserAuthAssignment();
                $userAuthAssignment->setAuthItem($authItem);
                $user->addUserAuthAssignment($userAuthAssignment);
            }
            $solidaryUser->setAddress($homeAddress);
            $user->setSolidaryUser($solidaryUser);
        } else {
            $solidaryUser = $user->getSolidaryUser();
        }
        
        // We create the solidaryUserStructure associated to the demand
        $structure = null;

        if ($solidary->getStructure()) {
            $structure = $this->structureRepository->find(substr($solidary->getStructure(), strrpos($solidary->getStructure(), '/') + 1));
        } else {
            if (!is_null($this->security->getUser()->getSolidaryStructures()) && count($this->security->getUser()->getSolidaryStructures()) > 0) {
                $structure = $this->security->getUser()->getSolidaryStructures()[0];
            } elseif (!is_null($this->security->getUser()->getSolidaryUser()->getSolidaryUserStructures())
                        && count($this->security->getUser()->getSolidaryUser()->getSolidaryUserStructures()) > 0
                        && !is_null($this->security->getUser()->getSolidaryUser()->getSolidaryUserStructures()[0]->getStructure())) {
                $structure = $this->security->getUser()->getSolidaryUser()->getSolidaryUserStructures()[0]->getStructure();
            } else {
                throw new SolidaryException(SolidaryException::NO_STRUCTURE);
            }
        }

        // we check if the solidary user structure doesn't exists already
        $solidaryUserStructure = null;
        $solidaryUserStructures = $solidaryUser->getSolidaryUserStructures();
        // first we check if the solidaryUser is already linked to a structure
        if (count($solidaryUser->getSolidaryUserStructures()) === 0) {
            $solidaryUserStructure = new SolidaryUserStructure();
            $solidaryUserStructure->setStructure($structure);
            $solidaryUserStructure->setSolidaryUser($solidaryUser);
        } else {
            foreach ($solidaryUserStructures as $currentSolidaryUserStructure) {
                if ($structure->getId() === $currentSolidaryUserStructure->getStructure()->getId()) {
                    $solidaryUserStructure = $currentSolidaryUserStructure;
                    break;
                } else {
                    $solidaryUserStructure = new SolidaryUserStructure();
                    $solidaryUserStructure->setStructure($structure);
                    $solidaryUserStructure->setSolidaryUser($solidaryUser);
                }
            }
        }
        
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

    /**
     * Build a volunteer's planning between two dates.
     *
     * @param \DateTimeInterface $startDate
     * @param \DateTimeInterface $endDate
     * @param integer $solidaryVolunteerId
     * @return array
     */
    public function buildSolidaryVolunteerPlanning(\DateTimeInterface $startDate, \DateTimeInterface  $endDate, int $solidaryVolunteerId): array
    {
        // We get the Volunteer and we check if it's really a volunteer
        $solidaryVolunteer = $this->solidaryUserRepository->find($solidaryVolunteerId);
        
        if (!$solidaryVolunteer->isVolunteer()) {
            throw new SolidaryException(SolidaryException::NO_SOLIDARY_VOLUNTEER);
        }
        
        $solidaryAsks = $this->solidaryAskRepository->findBetweenTwoDates($startDate, $endDate, $solidaryVolunteer);

        $fullPlanning = [];
        $currentDate = $startDate;
        // We make the schedule. Day by day.
        while ($currentDate<=$endDate) {
            $solidaryVolunteerPlanning = new SolidaryVolunteerPlanning();
            $solidaryVolunteerPlanning->setDate($currentDate);
            
            // We check if we found a solidaryAsk for this day
            foreach ($solidaryAsks as $solidaryAsk) {
                /**
                 * @var SolidaryAsk $solidaryAsk
                 */
                if (
                   ($solidaryAsk->getCriteria()->getFrequency()==Criteria::FREQUENCY_PUNCTUAL && $solidaryAsk->getCriteria()->getFromDate()->format("d/m/Y")==$currentDate->format("d/m/Y")) ||
                   ($solidaryAsk->getCriteria()->getFrequency()==Criteria::FREQUENCY_REGULAR && $solidaryAsk->getCriteria()->getFromDate()->format("d/m/Y")<=$currentDate->format("d/m/Y") && $solidaryAsk->getCriteria()->getToDate()->format("d/m/Y")>=$currentDate->format("d/m/Y"))
                ) {

                    // Determine the hour slot
                    $structure = $solidaryAsk->getSolidarySolution()->getSolidary()->getSolidaryUserStructure()->getStructure();
                    $slot = $this->solidaryMatcher->getHourSlot($solidaryAsk->getCriteria()->getFromTime(), $solidaryAsk->getCriteria()->getFromTime(), $structure);

                    $solidaryVolunteerPlanningItem = new SolidaryVolunteerPlanningItem();

                    // The beneficiary
                    $beneficiary = $solidaryAsk->getSolidarySolution()->getSolidary()->getSolidaryUserStructure()->getSolidaryUser()->getUser();
                    $solidaryVolunteerPlanningItem->setBeneficiary($beneficiary->getGivenName()." ".$beneficiary->getFamilyName());


                    // different usefull ids
                    $solidaryVolunteerPlanningItem->setSolidaryId($solidaryAsk->getSolidarySolution()->getSolidary()->getId());
                    $solidaryVolunteerPlanningItem->setSolidarySolutionId($solidaryAsk->getSolidarySolution()->getId());

                    // status
                    $solidaryVolunteerPlanningItem->setStatus($solidaryAsk->getStatus());

                    switch ($slot) {
                        case 'm': $solidaryVolunteerPlanning->setMorningSlot($solidaryVolunteerPlanningItem);break;
                        case 'a': $solidaryVolunteerPlanning->setAfternoonSlot($solidaryVolunteerPlanningItem);break;
                        case 'e': $solidaryVolunteerPlanning->setEveningSlot($solidaryVolunteerPlanningItem);break;
                    }
                }
            }

            $fullPlanning[] = $solidaryVolunteerPlanning;
            
            $currentDate = clone $currentDate;
            $currentDate->modify('+1 day');
        }

        return $fullPlanning;
    }
}
