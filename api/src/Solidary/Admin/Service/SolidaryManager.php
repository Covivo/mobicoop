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

namespace App\Solidary\Admin\Service;

use ApiPlatform\Core\DataProvider\PaginatorInterface;
use App\Action\Entity\Action;
use App\Auth\Entity\AuthItem;
use App\Auth\Entity\UserAuthAssignment;
use App\Auth\Repository\AuthItemRepository;
use App\Auth\Repository\UserAuthAssignmentRepository;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Proposal;
use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Ressource\Ad;
use App\Carpool\Service\AdManager;
use App\Geography\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;
use App\Solidary\Entity\Solidary;
use App\Solidary\Admin\Exception\SolidaryException;
use App\Solidary\Entity\Need;
use App\Solidary\Entity\Proof;
use App\Solidary\Entity\SolidaryUser;
use App\Solidary\Entity\SolidaryUserStructure;
use App\Solidary\Entity\Structure;
use App\Solidary\Entity\Subject;
use App\Action\Entity\Diary;
use App\Carpool\Entity\Waypoint;
use App\Action\Event\AnimationMadeEvent;
use App\Action\Repository\ActionRepository;
use App\Solidary\Entity\SolidaryMatching;
use App\Solidary\Entity\SolidarySolution;
use App\Solidary\Entity\SolidaryAsk;
use App\Solidary\Entity\SolidaryAskHistory;
use App\Solidary\Admin\Event\SolidaryCreatedEvent;
use App\Solidary\Repository\NeedRepository;
use App\Solidary\Repository\SolidaryRepository;
use App\Solidary\Repository\SolidaryUserRepository;
use App\Solidary\Repository\SolidaryUserStructureRepository;
use App\Solidary\Repository\StructureProofRepository;
use App\Solidary\Repository\StructureRepository;
use App\Solidary\Repository\SubjectRepository;
use App\User\Entity\User;
use App\Carpool\Entity\Matching;
use App\Action\Entity\Animation;
use App\Communication\Entity\Message;
use App\Communication\Entity\Recipient;
use App\Communication\Repository\MessageRepository;
use App\Communication\Service\InternalMessageManager;
use App\Solidary\Repository\SolidaryMatchingRepository;
use App\Solidary\Repository\SolidarySolutionRepository;
use App\User\Admin\Service\UserManager;
use App\User\Repository\UserRepository;
use DateTime;
use DateInterval;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;
use Psr\Log\LoggerInterface;

/**
 * Solidary manager in admin context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class SolidaryManager
{
    /**
     * @var User $poster
     */
    private $poster;
    private $entityManager;
    private $userManager;
    private $adManager;
    private $internalMessageManager;
    private $userRepository;
    private $structureProofRepository;
    private $solidaryUserRepository;
    private $structureRepository;
    private $solidaryUserStructureRepository;
    private $proposalRepository;
    private $authItemRepository;
    private $userAuthAssignmentRepository;
    private $subjectRepository;
    private $needRepository;
    private $eventDispatcher;
    private $solidaryRepository;
    private $actionRepository;
    private $solidaryMatchingRepository;
    private $solidarySolutionRepository;
    private $messageRepository;
    private $solidaryTransportMatcher;
    private $solidaryBeneficiaryManager;
    private $logger;


    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        Security $security,
        UserManager $userManager,
        AdManager $adManager,
        InternalMessageManager $internalMessageManager,
        UserRepository $userRepository,
        StructureProofRepository $structureProofRepository,
        SolidaryUserRepository $solidaryUserRepository,
        StructureRepository $structureRepository,
        SolidaryUserStructureRepository $solidaryUserStructureRepository,
        ProposalRepository $proposalRepository,
        AuthItemRepository $authItemRepository,
        UserAuthAssignmentRepository $userAuthAssignmentRepository,
        SubjectRepository $subjectRepository,
        NeedRepository $needRepository,
        EventDispatcherInterface $eventDispatcher,
        SolidaryRepository $solidaryRepository,
        ActionRepository $actionRepository,
        SolidaryMatchingRepository $solidaryMatchingRepository,
        SolidarySolutionRepository $solidarySolutionRepository,
        MessageRepository $messageRepository,
        SolidaryTransportMatcher $solidaryTransportMatcher,
        SolidaryBeneficiaryManager $solidaryBeneficiaryManager
    ) {
        $this->logger = $logger;
        $this->poster = $security->getUser();
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->adManager = $adManager;
        $this->internalMessageManager = $internalMessageManager;
        $this->userRepository = $userRepository;
        $this->structureProofRepository = $structureProofRepository;
        $this->solidaryUserRepository = $solidaryUserRepository;
        $this->structureRepository = $structureRepository;
        $this->solidaryUserStructureRepository = $solidaryUserStructureRepository;
        $this->proposalRepository = $proposalRepository;
        $this->authItemRepository = $authItemRepository;
        $this->userAuthAssignmentRepository = $userAuthAssignmentRepository;
        $this->subjectRepository = $subjectRepository;
        $this->needRepository = $needRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->solidaryRepository = $solidaryRepository;
        $this->actionRepository = $actionRepository;
        $this->solidaryMatchingRepository = $solidaryMatchingRepository;
        $this->solidarySolutionRepository = $solidarySolutionRepository;
        $this->messageRepository = $messageRepository;
        $this->solidaryTransportMatcher = $solidaryTransportMatcher;
        $this->solidaryBeneficiaryManager = $solidaryBeneficiaryManager;
    }

    /**
     * Get a Solidary record
     *
     * @param int $id  The solidary id
     * @return array|null The solidary record
     */
    public function getSolidary(int $id)
    {
        $solidary = $this->solidaryRepository->find($id);

        // link potential outward and return solidaryMatchings that would have not been made yet (as the link between Matchings are made after the SolidaryMatchings)
        $this->solidaryMatchingRepository->linkRelatedSolidaryMatchings($solidary->getId());

        // create schedules
        $schedules = [];
        $days = [ 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ];
        foreach ($days as $num => $day) {
            $this->treatDay($solidary->getProposal(), $num, $day, $schedules);
        }
        $solidary->setAdminschedules($schedules);

        if ($solidary->getAdminfrequency() == Criteria::FREQUENCY_FLEXIBLE) {
            // set min and max time for flexible proposal
            // for a flexible proposal, we have only one schedule, with all days checked
            $solidary->setAdminoutwardMinTime($solidary->getProposal()->getCriteria()->getMonMinTime());
            $solidary->setAdminoutwardTime($solidary->getProposal()->getCriteria()->getMonTime());
            $solidary->setAdminoutwardMaxTime($solidary->getProposal()->getCriteria()->getMonMaxTime());
            if ($solidary->getProposal()->getProposalLinked()) {
                $solidary->setAdminreturnMinTime($solidary->getProposal()->getProposalLinked()->getCriteria()->getMonMinTime());
                $solidary->setAdminreturnTime($solidary->getProposal()->getProposalLinked()->getCriteria()->getMonTime());
                $solidary->setAdminreturnMaxTime($solidary->getProposal()->getProposalLinked()->getCriteria()->getMonMaxTime());
            }
        } elseif ($solidary->getAdminfrequency() == Criteria::FREQUENCY_PUNCTUAL) {
            // set time for punctual proposal
            $solidary->setAdminoutwardMinTime($solidary->getProposal()->getCriteria()->getMinTime());
            $solidary->setAdminoutwardTime($solidary->getProposal()->getCriteria()->getFromTime());
            $solidary->setAdminoutwardMaxTime($solidary->getProposal()->getCriteria()->getMaxTime());
            if ($solidary->getProposal()->getProposalLinked()) {
                $solidary->setAdminreturnMinTime($solidary->getProposal()->getProposalLinked()->getCriteria()->getMinTime());
                $solidary->setAdminreturnTime($solidary->getProposal()->getProposalLinked()->getCriteria()->getFromTime());
                $solidary->setAdminreturnMaxTime($solidary->getProposal()->getProposalLinked()->getCriteria()->getMaxTime());
            }
        }

        // set operator informations
        $diaries = $this->solidaryRepository->getDiaries($solidary);
        if (count($diaries)>0) {
            foreach ($diaries as $diary) {
                /**
                 * @var Diary $diary
                 */
                if ($diary->getAction()->getId() === Action::SOLIDARY_CREATE && $diary->getAuthor()->getId() !== $diary->getUser()->getId()) {
                    $solidary->setAdminoperatorGivenName($diary->getAuthor()->getGivenName());
                    $solidary->setAdminoperatorFamilyName($diary->getAuthor()->getFamilyName());
                    $solidary->setAdminoperatorAvatar($diary->getAuthor()->getAvatar());
                }
            }
        }

        // set proofs
        $solidary->setAdminproofs($this->solidaryBeneficiaryManager->getProofsForSolidaryUserStructure($solidary->getSolidaryUserStructure(), $solidary->getSolidaryUserStructure()->getStructure()));

        // set carpools and transporters
        $carpools = [
            'outward' => [],
            'return' => []
        ];
        $volunteers = [
            'outward' => [],
            'return' => []
        ];
        foreach ($solidary->getSolidaryMatchings() as $solidaryMatching) {
            /**
             * @var SolidaryMatching $solidaryMatching
             */
            if ($solidaryMatching->getMatching()) {
                // carpool matching
                $carpool = [
                    'matchingId' => $solidaryMatching->getId(),
                    'carpoolerId' => $solidaryMatching->getMatching()->getProposalOffer()->getUser()->getId(),
                    'carpoolerGivenName' => $solidaryMatching->getMatching()->getProposalOffer()->getUser()->getGivenName(),
                    'carpoolerFamilyName' => $solidaryMatching->getMatching()->getProposalOffer()->getUser()->getFamilyName(),
                    'carpoolerAvatar' => $solidaryMatching->getMatching()->getProposalOffer()->getUser()->getAvatar(),
                    'frequency' => $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->getFrequency(),
                    // type is used to determine if the carpool has only an outward or also a return
                    'type' => $solidaryMatching->getType() ? ($solidaryMatching->getType() == Proposal::TYPE_ONE_WAY ? 'oneway' : 'roundtrip') : ($solidaryMatching->getMatching()->getProposalOffer()->getType() == Proposal::TYPE_ONE_WAY ? 'oneway' : 'roundtrip'),
                    'passenger' => $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->isPassenger(),
                    'driver' => $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->isDriver(),
                    'solidaryExclusive' => $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->isSolidaryExclusive(),
                    'fromDate' => $solidaryMatching->getMatching()->getCriteria()->getFromDate(),
                    'fromTime' => $solidaryMatching->getMatching()->getCriteria()->getFromTime(),
                    'toDate' => $solidaryMatching->getMatching()->getCriteria()->getToDate(),
                    'carpoolerFromDate' => $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->getFromDate(),
                    'carpoolerFromTime' => $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->getFromTime(),
                    'carpoolerToDate' => $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->getToDate()
                ];
                if ($solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                    $carpool['carpoolerSchedule'] = [
                        'mon' => $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->isMonCheck() ? $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->getMonTime() : false,
                        'tue' => $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->isTueCheck() ? $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->getTueTime() : false,
                        'wed' => $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->isWedCheck() ? $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->getWedTime() : false,
                        'thu' => $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->isThuCheck() ? $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->getThuTime() : false,
                        'fri' => $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->isFriCheck() ? $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->getFriTime() : false,
                        'sat' => $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->isSatCheck() ? $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->getSatTime() : false,
                        'sun' => $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->isSunCheck() ? $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->getSunTime() : false
                    ];
                    $carpool['schedule'] = [
                        'mon' => $solidaryMatching->getMatching()->getCriteria()->isMonCheck() ? $solidaryMatching->getMatching()->getCriteria()->getMonTime() : false,
                        'tue' => $solidaryMatching->getMatching()->getCriteria()->isTueCheck() ? $solidaryMatching->getMatching()->getCriteria()->getTueTime() : false,
                        'wed' => $solidaryMatching->getMatching()->getCriteria()->isWedCheck() ? $solidaryMatching->getMatching()->getCriteria()->getWedTime() : false,
                        'thu' => $solidaryMatching->getMatching()->getCriteria()->isThuCheck() ? $solidaryMatching->getMatching()->getCriteria()->getThuTime() : false,
                        'fri' => $solidaryMatching->getMatching()->getCriteria()->isFriCheck() ? $solidaryMatching->getMatching()->getCriteria()->getFriTime() : false,
                        'sat' => $solidaryMatching->getMatching()->getCriteria()->isSatCheck() ? $solidaryMatching->getMatching()->getCriteria()->getSatTime() : false,
                        'sun' => $solidaryMatching->getMatching()->getCriteria()->isSunCheck() ? $solidaryMatching->getMatching()->getCriteria()->getSunTime() : false
                    ];
                }
                foreach ($solidaryMatching->getMatching()->getProposalOffer()->getWaypoints() as $waypoint) {
                    /**
                     * @var Waypoint $waypoint
                     */
                    if ($waypoint->getPosition() == 0) {
                        $carpool['origin'] = $waypoint->getAddress()->jsonSerialize();
                        if ($solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                            /**
                             * @var DateTime $destinationTime
                             */
                            $destinationTime = clone $solidaryMatching->getMatching()->getProposalOffer()->getCriteria()->getFromTime();
                            $destinationTime->add(new \DateInterval('PT' . $solidaryMatching->getMatching()->getOriginalDuration() . 'S'));
                            $carpool['destinationTime'] = $destinationTime;
                        }
                    }
                    if ($waypoint->isDestination()) {
                        $carpool['destination'] = $waypoint->getAddress()->jsonSerialize();
                    }
                    $carpool['detourDuration'] = $solidaryMatching->getMatching()->getDetourDuration();
                    $carpool['detourDistance'] = $solidaryMatching->getMatching()->getDetourDistance();
                }
                if ($solidaryMatching->getMatching()->getProposalOffer()->getType() !== Proposal::TYPE_RETURN) {
                    $carpools['outward'][] = $carpool;
                } else {
                    $carpools['return'][] = $carpool;
                }
            } else {
                // volunteer matching
                $volunteer = [
                    'matchingId' => $solidaryMatching->getId(),
                    'volunteerId' => $solidaryMatching->getSolidaryUser()->getUser()->getId(),
                    'volunteerGivenName' => $solidaryMatching->getSolidaryUser()->getUser()->getGivenName(),
                    'volunteerFamilyName' => $solidaryMatching->getSolidaryUser()->getUser()->getFamilyName(),
                    'volunteerAvatar' => $solidaryMatching->getSolidaryUser()->getUser()->getAvatar(),
                    'centerPoint' => $solidaryMatching->getSolidaryUser()->getAddress()->jsonSerialize(),
                    'maxDistance' => $solidaryMatching->getSolidaryUser()->getMaxDistance(),
                    // type is used to determine if the journey has only an outward or also a return
                    'type' => $solidaryMatching->getType() == Proposal::TYPE_ONE_WAY ? 'oneway' : 'roundtrip',
                    'mMinTime' => $solidaryMatching->getSolidaryUser()->getMMinTime(),'mMaxTime' => $solidaryMatching->getSolidaryUser()->getMMaxTime(),
                    'aMinTime' => $solidaryMatching->getSolidaryUser()->getAMinTime(),'aMaxTime' => $solidaryMatching->getSolidaryUser()->getAMaxTime(),
                    'eMinTime' => $solidaryMatching->getSolidaryUser()->getEMinTime(),'eMaxTime' => $solidaryMatching->getSolidaryUser()->getEMaxTime(),
                ];
                // original schedule for the volunteer
                $volunteer['volunteerSchedule'] = [
                    'mMon' => $solidaryMatching->getSolidaryUser()->hasMMon(),'aMon' => $solidaryMatching->getSolidaryUser()->hasAMon(),'eMon' => $solidaryMatching->getSolidaryUser()->hasEMon(),
                    'mTue' => $solidaryMatching->getSolidaryUser()->hasMTue(),'aTue' => $solidaryMatching->getSolidaryUser()->hasATue(),'eTue' => $solidaryMatching->getSolidaryUser()->hasETue(),
                    'mWed' => $solidaryMatching->getSolidaryUser()->hasMWed(),'aWed' => $solidaryMatching->getSolidaryUser()->hasAWed(),'eWed' => $solidaryMatching->getSolidaryUser()->hasEWed(),
                    'mThu' => $solidaryMatching->getSolidaryUser()->hasMThu(),'aThu' => $solidaryMatching->getSolidaryUser()->hasAThu(),'eThu' => $solidaryMatching->getSolidaryUser()->hasEThu(),
                    'mFri' => $solidaryMatching->getSolidaryUser()->hasMFri(),'aFri' => $solidaryMatching->getSolidaryUser()->hasAFri(),'eFri' => $solidaryMatching->getSolidaryUser()->hasEFri(),
                    'mSat' => $solidaryMatching->getSolidaryUser()->hasMSat(),'aSat' => $solidaryMatching->getSolidaryUser()->hasASat(),'eSat' => $solidaryMatching->getSolidaryUser()->hasESat(),
                    'mSun' => $solidaryMatching->getSolidaryUser()->hasMSun(),'aSun' => $solidaryMatching->getSolidaryUser()->hasASun(),'eSun' => $solidaryMatching->getSolidaryUser()->hasESun(),
                ];
                // computed schedule for the volunteer regarding the matching criteria
                $volunteer['schedule'] = [
                    'mMon' => false, 'aMon' => false, 'eMon' => false,
                    'mTue' => false, 'aTue' => false, 'eTue' => false,
                    'mWed' => false, 'aWed' => false, 'eWed' => false,
                    'mThu' => false, 'aThu' => false, 'eThu' => false,
                    'mFri' => false, 'aFri' => false, 'eFri' => false,
                    'mSat' => false, 'aSat' => false, 'eSat' => false,
                    'mSun' => false, 'aSun' => false, 'eSun' => false,
                ];
                if ($solidaryMatching->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                    // regular schedule
                    if (
                        $solidaryMatching->getCriteria()->isMonCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getMonMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getMMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getMonMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getMMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['mMon'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isMonCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getMonMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getAMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getMonMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getAMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['aMon'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isMonCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getMonMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getEMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getMonMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getEMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['eMon'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isTueCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getTueMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getMMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getTueMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getMMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['mTue'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isTueCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getTueMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getAMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getTueMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getAMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['aTue'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isTueCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getTueMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getEMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getTueMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getEMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['eTue'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isWedCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getWedMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getMMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getWedMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getMMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['mWed'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isWedCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getWedMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getAMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getWedMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getAMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['aWed'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isWedCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getWedMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getEMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getWedMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getEMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['eWed'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isThuCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getThuMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getMMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getThuMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getMMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['mThu'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isThuCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getThuMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getAMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getThuMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getAMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['aThu'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isThuCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getThuMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getEMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getThuMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getEMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['eThu'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isFriCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getFriMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getMMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getFriMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getMMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['mFri'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isFriCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getFriMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getAMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getFriMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getAMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['aFri'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isFriCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getFriMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getEMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getFriMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getEMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['eFri'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isSatCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getSatMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getMMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getSatMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getMMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['mSat'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isSatCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getSatMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getAMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getSatMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getAMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['aSat'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isSatCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getSatMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getEMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getSatMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getEMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['eSat'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isSunCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getSunMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getMMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getSunMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getMMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['mSun'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isSunCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getSunMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getAMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getSunMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getAMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['aSun'] = true;
                    }
                    if (
                        $solidaryMatching->getCriteria()->isSunCheck() &&
                        strtotime($solidaryMatching->getCriteria()->getSunMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getEMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getSunMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getEMinTime()->format('H:i:s'))
                        ) {
                        $volunteer['schedule']['eSun'] = true;
                    }
                } else {
                    // punctual, we search the corresponding day
                    $key = '';
                    switch ($solidaryMatching->getCriteria()->getFromDate()->format('w')) {
                        case 0: $key = 'Sun'; break;
                        case 1: $key = 'Mon'; break;
                        case 2: $key = 'Tue'; break;
                        case 3: $key = 'Wed'; break;
                        case 4: $key = 'Thu'; break;
                        case 5: $key = 'Fri'; break;
                        case 6: $key = 'Sat'; break;
                    }
                    if (
                        strtotime($solidaryMatching->getCriteria()->getMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getMMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getMMinTime()->format('H:i:s'))
                    ) {
                        $volunteer['schedule']['m'.$key] = true;
                    }
                    if (
                        strtotime($solidaryMatching->getCriteria()->getMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getAMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getAMinTime()->format('H:i:s'))
                    ) {
                        $volunteer['schedule']['a'.$key] = true;
                    }
                    if (
                        strtotime($solidaryMatching->getCriteria()->getMinTime()->format('H:i:s')) < strtotime($solidaryMatching->getSolidaryUser()->getEMaxTime()->format('H:i:s')) &&
                        strtotime($solidaryMatching->getCriteria()->getMaxTime()->format('H:i:s')) >= strtotime($solidaryMatching->getSolidaryUser()->getEMinTime()->format('H:i:s'))
                    ) {
                        $volunteer['schedule']['e'.$key] = true;
                    }
                }
 
                if ($solidaryMatching->getType() !== Proposal::TYPE_RETURN) {
                    $volunteers['outward'][] = $volunteer;
                } else {
                    $volunteers['return'][] = $volunteer;
                }
            }
        }

        $solidary->setAdmincarpools($carpools);
        $solidary->setAdmintransporters($volunteers);

        // set solutions and threads
        $solutions = [
            'drivers' => [],
            'threads' => []
        ];
        foreach ($solidary->getSolidarySolutions() as $solution) {
            /**
             * @var SolidarySolution $solution
             */
            // set drivers
            if ($solution->getSolidaryMatching()->getSolidaryUser()) {
                // solution is a transporter
                $solutions['drivers'][] = [
                    'id' => $solution->getId(),
                    'matchingId' => $solution->getSolidaryMatching()->getId(),
                    'type' => SolidarySolution::TRANSPORTER,
                    'givenName' => $solution->getSolidaryMatching()->getSolidaryUser()->getUser()->getGivenName(),
                    'familyName' => $solution->getSolidaryMatching()->getSolidaryUser()->getUser()->getFamilyName(),
                    'telephone' => $solution->getSolidaryMatching()->getSolidaryUser()->getUser()->getTelephone(),
                    'avatar' => $solution->getSolidaryMatching()->getSolidaryUser()->getUser()->getAvatar(),
                    'userId' => $solution->getSolidaryMatching()->getSolidaryUser()->getUser()->getId(),
                    'status' => $solution->getSolidaryAsk() ? $solution->getSolidaryAsk()->getStatus() : SolidaryAsk::STATUS_ASKED,
                    'contacted' => $solution->getSolidaryAsk() ? true : false,
                    'centerPoint' => $solution->getSolidaryMatching()->getSolidaryUser()->getAddress()->jsonSerialize(),
                    'maxDistance' => $solution->getSolidaryMatching()->getSolidaryUser()->getMaxDistance(),
                    'mMinTime' => $solution->getSolidaryMatching()->getSolidaryUser()->getMMinTime(),'mMaxTime' => $solution->getSolidaryMatching()->getSolidaryUser()->getMMaxTime(),
                    'aMinTime' => $solution->getSolidaryMatching()->getSolidaryUser()->getAMinTime(),'aMaxTime' => $solution->getSolidaryMatching()->getSolidaryUser()->getAMaxTime(),
                    'eMinTime' => $solution->getSolidaryMatching()->getSolidaryUser()->getEMinTime(),'eMaxTime' => $solution->getSolidaryMatching()->getSolidaryUser()->getEMaxTime(),
                    'mMon' => $solution->getSolidaryMatching()->getSolidaryUser()->hasMMon(),'aMon' => $solution->getSolidaryMatching()->getSolidaryUser()->hasAMon(),'eMon' => $solution->getSolidaryMatching()->getSolidaryUser()->hasEMon(),
                    'mTue' => $solution->getSolidaryMatching()->getSolidaryUser()->hasMTue(),'aTue' => $solution->getSolidaryMatching()->getSolidaryUser()->hasATue(),'eTue' => $solution->getSolidaryMatching()->getSolidaryUser()->hasETue(),
                    'mWed' => $solution->getSolidaryMatching()->getSolidaryUser()->hasMWed(),'aWed' => $solution->getSolidaryMatching()->getSolidaryUser()->hasAWed(),'eWed' => $solution->getSolidaryMatching()->getSolidaryUser()->hasEWed(),
                    'mThu' => $solution->getSolidaryMatching()->getSolidaryUser()->hasMThu(),'aThu' => $solution->getSolidaryMatching()->getSolidaryUser()->hasAThu(),'eThu' => $solution->getSolidaryMatching()->getSolidaryUser()->hasEThu(),
                    'mFri' => $solution->getSolidaryMatching()->getSolidaryUser()->hasMFri(),'aFri' => $solution->getSolidaryMatching()->getSolidaryUser()->hasAFri(),'eFri' => $solution->getSolidaryMatching()->getSolidaryUser()->hasEFri(),
                    'mSat' => $solution->getSolidaryMatching()->getSolidaryUser()->hasMSat(),'aSat' => $solution->getSolidaryMatching()->getSolidaryUser()->hasASat(),'eSat' => $solution->getSolidaryMatching()->getSolidaryUser()->hasESat(),
                    'mSun' => $solution->getSolidaryMatching()->getSolidaryUser()->hasMSun(),'aSun' => $solution->getSolidaryMatching()->getSolidaryUser()->hasASun(),'eSun' => $solution->getSolidaryMatching()->getSolidaryUser()->hasESun(),
                ];
            } elseif ($solution->getSolidaryMatching()->getMatching()) {
                // solution is a carpooler
                $asolution = [
                    'id' => $solution->getId(),
                    'matchingId' => $solution->getSolidaryMatching()->getId(),
                    'type' => SolidarySolution::CARPOOLER,
                    'givenName' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser()->getGivenName(),
                    'familyName' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser()->getFamilyName(),
                    'telephone' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser()->getTelephone(),
                    'avatar' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser()->getAvatar(),
                    'userId' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser()->getId(),
                    'way' => $solution->getSolidaryMatching()->getMatching()->getProposalRequest()->getType(),
                    'status' => $solution->getSolidaryAsk() ? $solution->getSolidaryAsk()->getStatus() : SolidaryAsk::STATUS_ASKED,
                    'contacted' => $solution->getSolidaryAsk() ? true : false,
                    'frequency' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->getFrequency(),
                    'carpoolerProposalType' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getType() == Proposal::TYPE_ONE_WAY ? 'oneway' : 'roundtrip',
                    'proposalType' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked() ? 'roundtrip' : 'oneway',
                    'passenger' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->isPassenger(),
                    'driver' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->isDriver(),
                    'solidaryExclusive' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->isSolidaryExclusive()
                ];
                foreach ($solution->getSolidaryMatching()->getMatching()->getProposalRequest()->getWaypoints() as $waypoint) {
                    /**
                     * @var Waypoint $waypoint
                     */
                    if ($waypoint->getPosition() == 0) {
                        $asolution['origin'] = $waypoint->getAddress()->jsonSerialize();
                    }
                    if ($waypoint->isDestination()) {
                        $asolution['destination'] = $waypoint->getAddress()->jsonSerialize();
                    }
                }
                $way = "outward";
                if ($solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getType() == Proposal::TYPE_RETURN) {
                    $way = "return";
                }
                $asolution[$way] = [
                    'fromDate' => $solution->getSolidaryMatching()->getMatching()->getCriteria()->getFromDate(),
                    'fromTime' => $solution->getSolidaryMatching()->getMatching()->getCriteria()->getFromTime(),
                    'toDate' => $solution->getSolidaryMatching()->getMatching()->getCriteria()->getToDate(),
                    'carpoolerFromDate' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->getFromDate(),
                    'carpoolerFromTime' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->getFromTime(),
                    'carpoolerToDate' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->getToDate()
                ];
                if ($solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                    $asolution['schedule'][$way] = [
                        'mon' => $solution->getSolidaryMatching()->getMatching()->getCriteria()->isMonCheck() ? $solution->getSolidaryMatching()->getMatching()->getCriteria()->getMonTime() : false,
                        'tue' => $solution->getSolidaryMatching()->getMatching()->getCriteria()->isTueCheck() ? $solution->getSolidaryMatching()->getMatching()->getCriteria()->getTueTime() : false,
                        'wed' => $solution->getSolidaryMatching()->getMatching()->getCriteria()->isWedCheck() ? $solution->getSolidaryMatching()->getMatching()->getCriteria()->getWedTime() : false,
                        'thu' => $solution->getSolidaryMatching()->getMatching()->getCriteria()->isThuCheck() ? $solution->getSolidaryMatching()->getMatching()->getCriteria()->getThuTime() : false,
                        'fri' => $solution->getSolidaryMatching()->getMatching()->getCriteria()->isFriCheck() ? $solution->getSolidaryMatching()->getMatching()->getCriteria()->getFriTime() : false,
                        'sat' => $solution->getSolidaryMatching()->getMatching()->getCriteria()->isSatCheck() ? $solution->getSolidaryMatching()->getMatching()->getCriteria()->getSatTime() : false,
                        'sun' => $solution->getSolidaryMatching()->getMatching()->getCriteria()->isSunCheck() ? $solution->getSolidaryMatching()->getMatching()->getCriteria()->getSunTime() : false
                    ];
                    $asolution['carpoolerSchedule'][$way] = [
                        'mon' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->isMonCheck() ? $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->getMonTime() : false,
                        'tue' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->isTueCheck() ? $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->getTueTime() : false,
                        'wed' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->isWedCheck() ? $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->getWedTime() : false,
                        'thu' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->isThuCheck() ? $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->getThuTime() : false,
                        'fri' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->isFriCheck() ? $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->getFriTime() : false,
                        'sat' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->isSatCheck() ? $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->getSatTime() : false,
                        'sun' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->isSunCheck() ? $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->getSunTime() : false
                    ];
                }
                foreach ($solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getWaypoints() as $waypoint) {
                    /**
                     * @var Waypoint $waypoint
                     */
                    if ($waypoint->getPosition() == 0) {
                        $asolution[$way]['origin'] = $waypoint->getAddress()->jsonSerialize();
                        if ($solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                            /**
                             * @var DateTime $destinationTime
                             */
                            $destinationTime = clone $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getCriteria()->getFromTime();
                            $destinationTime->add(new \DateInterval('PT' . $solution->getSolidaryMatching()->getMatching()->getOriginalDuration() . 'S'));
                            $asolution[$way]['destinationTime'] = $destinationTime;
                        }
                    }
                    if ($waypoint->isDestination()) {
                        $asolution[$way]['destination'] = $waypoint->getAddress()->jsonSerialize();
                    }
                }
                // check for a matching return
                if ($solution->getSolidaryMatching()->getSolidaryMatchingLinked()) {
                    if ($way == 'outward') {
                        $way = 'return';
                    } else {
                        $way = 'outward';
                    }
                    $asolution[$way] = [
                        'fromDate' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->getFromDate(),
                        'fromTime' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->getFromTime(),
                        'toDate' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->getToDate(),
                        'carpoolerFromDate' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->getFromDate(),
                        'carpoolerFromTime' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->getFromTime(),
                        'carpoolerToDate' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->getToDate()
                    ];
                    if ($solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                        $asolution['schedule'][$way] = [
                            'mon' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->isMonCheck() ? $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->getMonTime() : false,
                            'tue' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->isTueCheck() ? $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->getTueTime() : false,
                            'wed' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->isWedCheck() ? $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->getWedTime() : false,
                            'thu' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->isThuCheck() ? $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->getThuTime() : false,
                            'fri' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->isFriCheck() ? $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->getFriTime() : false,
                            'sat' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->isSatCheck() ? $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->getSatTime() : false,
                            'sun' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->isSunCheck() ? $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getCriteria()->getSunTime() : false
                        ];
                        $asolution['carpoolerSchedule'][$way] = [
                            'mon' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->isMonCheck() ? $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->getMonTime() : false,
                            'tue' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->isTueCheck() ? $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->getTueTime() : false,
                            'wed' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->isWedCheck() ? $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->getWedTime() : false,
                            'thu' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->isThuCheck() ? $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->getThuTime() : false,
                            'fri' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->isFriCheck() ? $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->getFriTime() : false,
                            'sat' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->isSatCheck() ? $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->getSatTime() : false,
                            'sun' => $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->isSunCheck() ? $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->getSunTime() : false
                        ];
                    }
                    foreach ($solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getWaypoints() as $waypoint) {
                        /**
                         * @var Waypoint $waypoint
                         */
                        if ($waypoint->getPosition() == 0) {
                            $asolution[$way]['origin'] = $waypoint->getAddress()->jsonSerialize();
                            if ($solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                                /**
                                 * @var DateTime $destinationTime
                                 */
                                $destinationTime = clone $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getProposalOffer()->getCriteria()->getFromTime();
                                $destinationTime->add(new \DateInterval('PT' . $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getMatching()->getOriginalDuration() . 'S'));
                                $asolution[$way]['destinationTime'] = $destinationTime;
                            }
                        }
                        if ($waypoint->isDestination()) {
                            $asolution[$way]['destination'] = $waypoint->getAddress()->jsonSerialize();
                        }
                    }
                } elseif ($solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()) {
                    // no matching return, but the driver has a non matching return trip
                    if ($way == 'outward') {
                        $way = 'return';
                    } else {
                        $way = 'outward';
                    }
                    $asolution[$way] = [
                        'fromDate' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->getFromDate(),
                        'fromTime' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->getFromTime(),
                        'toDate' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->getToDate()
                    ];
                    if ($solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                        $asolution['schedule'][$way] = [
                            'mon' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->isMonCheck() ? $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->getMonTime() : false,
                            'tue' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->isTueCheck() ? $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->getTueTime() : false,
                            'wed' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->isWedCheck() ? $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->getWedTime() : false,
                            'thu' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->isThuCheck() ? $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->getThuTime() : false,
                            'fri' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->isFriCheck() ? $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->getFriTime() : false,
                            'sat' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->isSatCheck() ? $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->getSatTime() : false,
                            'sun' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->isSunCheck() ? $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->getSunTime() : false
                        ];
                    }
                    foreach ($solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getWaypoints() as $waypoint) {
                        /**
                         * @var Waypoint $waypoint
                         */
                        if ($waypoint->getPosition() == 0) {
                            $asolution[$way]['origin'] = $waypoint->getAddress()->jsonSerialize();
                            if ($solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                                /**
                                 * @var DateTime $destinationTime
                                 */
                                $destinationTime = clone $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getProposalLinked()->getCriteria()->getFromTime();
                                // for the destination time, we have to use the outward time as there is no matching for the return...
                                $destinationTime->add(new \DateInterval('PT' . $solution->getSolidaryMatching()->getMatching()->getOriginalDuration() . 'S'));
                                $asolution[$way]['destinationTime'] = $destinationTime;
                            }
                        }
                        if ($waypoint->isDestination()) {
                            $asolution[$way]['destination'] = $waypoint->getAddress()->jsonSerialize();
                        }
                    }
                }
                $solutions['drivers'][] = $asolution;
            }
            // set threads
            $solutions['threads'][$solution->getId()] = $this->getThreadForSolution($solution);
        }
        // group outward and return (only one should have a thread => we use the same thread)
        foreach ($solidary->getSolidarySolutions() as $solution) {
            /**
             * @var SolidarySolution $solution
             */
            if ($solutions['threads'][$solution->getId()] == [] && $solution->getSolidarySolutionLinked() && isset($solutions['threads'][$solution->getSolidarySolutionLinked()->getId()]) && $solutions['threads'][$solution->getSolidarySolutionLinked()->getId()] !== []) {
                $solutions['threads'][$solution->getId()] = $solutions['threads'][$solution->getSolidarySolutionLinked()->getId()];
            }
        }

        $solidary->setAdminsolutions($solutions);

        // set diary
        $diaries = [];
        foreach ($solidary->getDiaries() as $diary) {
            /**
             * @var Diary $diary
             */
            $diaries[] = [
                'action' => $diary->getAction()->getName(),
                'comment' => $diary->getComment(),
                'progression' => $diary->getProgression(),
                'authorGivenName' => $diary->getAuthor()->getGivenName(),
                'authorFamilyName' => $diary->getAuthor()->getFamilyName(),
                'authorAvatar' => $diary->getAuthor()->getAvatar(),
                'userId' => $diary->getUser()->getId(),
                'givenName' => $diary->getUser()->getGivenName(),
                'familyName' => $diary->getUser()->getFamilyName(),
                'avatar' => $diary->getUser()->getAvatar(),
                'date' => $diary->getCreatedDate()
            ];
        }
        $solidary->setAdmindiary($diaries);

        // check if the solidary is deeply editable => the journey can be updated without side effects (matchnigs, asks...)
        $solidary->setAdmineditable(true);
        // there's already a solution ? => not editable
        if (count($solidary->getAdminsolutions()['drivers'])>0) {
            $solidary->setAdmineditable(false);
        }
        // there are already solidary matchings ? => not editable
        if (count($solidary->getSolidaryMatchings())>0) {
            $solidary->setAdmineditable(false);
        }

        return $solidary;
    }

    /**
     * Internal method used to get the regular schedules for a given proposal and a given day
     *
     * @param Proposal $proposal    The proposal
     * @param integer $num          The number of the day in the week
     * @param string $day           The shorten name of the day (3 letters)
     * @param array $schedules      The resulting schedules (passed by reference)
     * @return void
     */
    private function treatDay(Proposal $proposal, int $num, string $day, array &$schedules)
    {
        $checkMethod = "is".$day."Check";
        $timeMethod = "get".$day."Time";
        $outwardChecked = $proposal->getCriteria()->$checkMethod() && $proposal->getCriteria()->$timeMethod();
        $returnChecked =
            $proposal->getType() == Proposal::TYPE_OUTWARD &&
            $proposal->getProposalLinked() &&
            $proposal->getProposalLinked()->getCriteria()->$checkMethod() &&
            $proposal->getProposalLinked()->getCriteria()->$timeMethod();
        
        if ($outwardChecked || $returnChecked) {
            $foundSchedule = false;
            foreach ($schedules as $key => $schedule) {
                if ($outwardChecked && $returnChecked) {
                    if (
                        $schedule['outwardTime'] == $proposal->getCriteria()->$timeMethod() &&
                        $schedule['returnTime'] == $proposal->getProposalLinked()->getCriteria()->$timeMethod()
                    ) {
                        $schedules[$key][strtolower($day)] = true;
                        $foundSchedule = true;
                        break;
                    }
                } elseif ($outwardChecked) {
                    if ($schedule['outwardTime'] == $proposal->getCriteria()->$timeMethod() && $schedule['returnTime'] == null) {
                        $schedules[$key][strtolower($day)] = true;
                        $foundSchedule = true;
                        break;
                    }
                } elseif ($returnChecked) {
                    if ($schedule['returnTime'] == $proposal->getProposalLinked()->getCriteria()->$timeMethod() && $schedule['outwardTime'] == null) {
                        $schedules[$key][strtolower($day)] = true;
                        $foundSchedule = true;
                        break;
                    }
                }
            }
            if (!$foundSchedule) {
                $schedules[] = $this->createSchedule($num, $proposal->getCriteria()->$timeMethod(), $returnChecked ? $proposal->getProposalLinked()->getCriteria()->$timeMethod() : null);
            }
        }
    }

    /**
     * Internal method used to build a schedule array
     *
     * @param int $num                      The number of the day in the week
     * @param \DateTime|null $outwardTime   The outward time
     * @param \DateTime|null $returnTime    The return time
     * @return array                        The schedule
     */
    private function createSchedule(int $num, ?\DateTime $outwardTime = null, ?\DateTime $returnTime = null)
    {
        return [
            'mon' => $num == 0,
            'tue' => $num == 1,
            'wed' => $num == 2,
            'thu' => $num == 3,
            'fri' => $num == 4,
            'sat' => $num == 5,
            'sun' => $num == 6,
            'outwardTime' => $outwardTime,
            'returnTime' => $returnTime
        ];
    }

    /**
     * Get a full message thread for a given SolidarySolution
     *
     * @param SolidarySolution $solution    The solidary solution
     * @return array                        The thread, as a list of messages ordered by date desc
     */
    private function getThreadForSolution(SolidarySolution $solution)
    {
        $thread = [];
        // if the solution has no associated ask, there are no messages !
        if (!$solution->getSolidaryAsk()) {
            return $thread;
        }

        // we will loop through the ask histories to find the first ask history with an associated message
        // then, from this first message, we will be able to get the whole thread
        foreach ($solution->getSolidaryAsk()->getSolidaryAskHistories() as $solidaryAskHistory) {
            /**
             * @var SolidaryAskHistory $solidaryAskHistory
             */
            if ($solidaryAskHistory->getMessage()) {
                $completeThread = $this->internalMessageManager->getCompleteThread($solidaryAskHistory->getMessage()->getId());
                // we complete the messages with all the necessary informations
                foreach ($completeThread as $message) {
                    /**
                     * @var Message $message
                     */
                    $thread[] = [
                        'posterId' => $message->getUser()->getId(),
                        'posterGivenName' => $message->getUser()->getGivenName(),
                        'posterFamilyName' => $message->getUser()->getFamilyName(),
                        'posterAvatar' => $message->getUser()->getAvatar(),
                        'delegateGivenName' => $message->getUserDelegate() ? $message->getUserDelegate()->getGivenName() : null,
                        'delegateFamilyName' => $message->getUserDelegate() ? $message->getUserDelegate()->getFamilyName() : null,
                        'delegateAvatar' => $message->getUserDelegate() ? $message->getUserDelegate()->getAvatar() : null,
                        'recipientGivenName' => $message->getRecipients()[0]->getUser()->getGivenName(),
                        'recipientFamilyName' => $message->getRecipients()[0]->getUser()->getFamilyName(),
                        'recipientAvatar' => $message->getRecipients()[0]->getUser()->getAvatar(),
                        'text' => $message->getText(),
                        'date' => $message->getCreatedDate()
                    ];
                }
                break;
            }
        }
        return $thread;
    }

    /**
     * Get Solidary records
     *
     * @param PaginatorInterface $solidaries  The solidary objects
     * @return array|null The solidary records
     */
    public function getSolidaries(PaginatorInterface $solidaries)
    {
        return $solidaries;
    }

    /**
     * Get manually available triggered actions
     *
     * @return array
     */
    public function getActions()
    {
        return $this->actionRepository->getSolidaryActions();
    }

    /**
     * Add a solidary record.
     *
     * @param Solidary      $solidary               The solidary to add
     * @param array         $fields                 The fields
     * @return Solidary     The solidary created
     */
    public function addSolidary(Solidary $solidary, array $fields)
    {
        // To create a new Solidary record, we need the following steps :
        // 1. create a SolidaryUser if the beneficiary is not already a solidary user
        // 2. create a SolidaryUserStructure, reflecting the status of the beneficiary within the structure (if the beneficiary has not already a SolidaryRecord in that structure)
        // 3. create SolidaryProofs if needed, linked to the SolidaryUserStructure
        // 4. prepare the SolidaryRecord with the Subject, which must be related to the proposal
        // 5. create a SolidaryRecord, linked to the SolidaryUserStructure, the Subject and Needs
        // 6. create a Proposal, linked with the SolidaryRecord, reflecting the journey needed for the beneficiary

        // Note : we create the solidary record before the proposal, therefor the proposal for the solidary record is null
        // it's because we need to link Matchings with SolidaryMatchnings, this is done using events during the matching process that we can't modify for consistency reasons !
        
        // first we perform some checkings !

        // check beneficiary
        if (!isset($fields['beneficiary'])) {
            throw new SolidaryException(SolidaryException::BENEFICIARY_REQUIRED);
        }

        // check structure
        if (!isset($fields['structure'])) {
            throw new SolidaryException(SolidaryException::STRUCTURE_REQUIRED);
        }
        if (!$structure = $this->structureRepository->find($fields['structure'])) {
            throw new SolidaryException(sprintf(SolidaryException::STRUCTURE_NOT_FOUND, $fields['structure']));
        }

        // check journey
        if (!isset($fields['origin'])) {
            throw new SolidaryException(SolidaryException::ORIGIN_REQUIRED);
        }
        if (!isset($fields['destination']) && (!isset($fields['destinationAny']) || !$fields['destinationAny'])) {
            throw new SolidaryException(SolidaryException::DESTINATION_REQUIRED);
        }

        // check frequency
        if (!isset($fields['regular'])) {
            throw new SolidaryException(SolidaryException::FREQUENCY_REQUIRED);
        }
        if ($fields['regular'] && !isset($fields['regularSchedules'])) {
            throw new SolidaryException(SolidaryException::REGULAR_SCHEDULES_REQUIRED);
        }
        if ($fields['regular'] && !isset($fields['regularDateChoice'])) {
            throw new SolidaryException(SolidaryException::REGULAR_DATE_CHOICE_REQUIRED);
        }
        if (!$fields['regular'] && !isset($fields['punctualOutwardDateChoice'])) {
            throw new SolidaryException(SolidaryException::PUNCTUAL_OUTWARD_DATE_CHOICE_REQUIRED);
        }
        if (!$fields['regular'] && !in_array($fields['punctualOutwardDateChoice'], Solidary::PUNCTUAL_OUTWARD_DATE_CHOICES)) {
            throw new SolidaryException(SolidaryException::PUNCTUAL_OUTWARD_DATE_CHOICE_INVALID);
        }
        if (!$fields['regular'] && !isset($fields['punctualOutwardTimeChoice'])) {
            throw new SolidaryException(SolidaryException::PUNCTUAL_OUTWARD_TIME_CHOICE_REQUIRED);
        }
        if (!$fields['regular'] && !in_array($fields['punctualOutwardTimeChoice'], Solidary::PUNCTUAL_TIME_CHOICES)) {
            throw new SolidaryException(SolidaryException::PUNCTUAL_OUTWARD_TIME_CHOICE_INVALID);
        }

        // 1 - create the SolidaryUser
        $beneficiary = null;
        $newUser = false;
        if (isset($fields['beneficiary']['id'])) {
            // check beneficiary is a valid user
            if (!$beneficiary = $this->userRepository->find($fields['beneficiary']['id'])) {
                throw new SolidaryException(sprintf(SolidaryException::BENEFICIARY_NOT_FOUND, $fields['beneficiary']['id']));
            }
            // check if beneficiary informations have been updated
            if (isset($fields['beneficiary']['givenName']) && $fields['beneficiary']['givenName'] != $beneficiary->getGivenName()) {
                $beneficiary->setGivenName($fields['beneficiary']['givenName']);
            }
            if (isset($fields['beneficiary']['familyName']) && $fields['beneficiary']['familyName'] != $beneficiary->getFamilyName()) {
                $beneficiary->setFamilyName($fields['beneficiary']['familyName']);
            }
            if (isset($fields['beneficiary']['email']) && $fields['beneficiary']['email'] != $beneficiary->getEmail()) {
                $beneficiary->setEmail($fields['beneficiary']['email']);
            }
            if (isset($fields['beneficiary']['telephone']) && $fields['beneficiary']['telephone'] != $beneficiary->getTelephone()) {
                $beneficiary->setTelephone($fields['beneficiary']['telephone']);
            }
            if (isset($fields['beneficiary']['gender']) && $fields['beneficiary']['gender'] != $beneficiary->getGender()) {
                $beneficiary->setGender($fields['beneficiary']['gender']);
            }
            if (isset($fields['beneficiary']['birthDate']) && $fields['beneficiary']['birthDate'] != $beneficiary->getBirthDate()) {
                $beneficiary->setBirthDate(new DateTime($fields['beneficiary']['birthDate']));
            }
            if (isset($fields['beneficiary']['newsSubscription']) && $fields['beneficiary']['newsSubscription'] != $beneficiary->hasNewsSubscription()) {
                $beneficiary->setNewsSubscription($fields['beneficiary']['newsSubscription']);
            }
            // check if beneficiary home address has been updated
            if (isset($fields['beneficiary']['homeAddress'])) {
                // we search the original home address
                $homeAddress = null;
                foreach ($beneficiary->getAddresses() as $address) {
                    if ($address->isHome()) {
                        $homeAddress = $address;
                        break;
                    }
                }
                if (!is_null($homeAddress)) {
                    // we have to update each field...
                    /**
                    * @var Address $homeAddress
                    */
                    $updated = false;
                    if (isset($fields['beneficiary']['homeAddress']['streetAddress']) && $homeAddress->getStreetAddress() != $fields['beneficiary']['homeAddress']['streetAddress']) {
                        $updated = true;
                        $homeAddress->setStreetAddress($fields['beneficiary']['homeAddress']['streetAddress']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['postalCode']) && $homeAddress->getPostalCode() != $fields['beneficiary']['homeAddress']['postalCode']) {
                        $updated = true;
                        $homeAddress->setPostalCode($fields['beneficiary']['homeAddress']['postalCode']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['addressLocality']) && $homeAddress->getAddressLocality() != $fields['beneficiary']['homeAddress']['addressLocality']) {
                        $updated = true;
                        $homeAddress->setAddressLocality($fields['beneficiary']['homeAddress']['addressLocality']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['addressCountry']) && $homeAddress->getAddressCountry() != $fields['beneficiary']['homeAddress']['addressCountry']) {
                        $updated = true;
                        $homeAddress->setAddressCountry($fields['beneficiary']['homeAddress']['addressCountry']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['latitude']) && $homeAddress->getLatitude() != $fields['beneficiary']['homeAddress']['latitude']) {
                        $updated = true;
                        $homeAddress->setLatitude($fields['beneficiary']['homeAddress']['latitude']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['longitude']) && $homeAddress->getLongitude() != $fields['beneficiary']['homeAddress']['longitude']) {
                        $updated = true;
                        $homeAddress->setLongitude($fields['beneficiary']['homeAddress']['longitude']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['houseNumber']) && $homeAddress->getHouseNumber() != $fields['beneficiary']['homeAddress']['houseNumber']) {
                        $updated = true;
                        $homeAddress->setHouseNumber($fields['beneficiary']['homeAddress']['houseNumber']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['subLocality']) && $homeAddress->getSubLocality() != $fields['beneficiary']['homeAddress']['subLocality']) {
                        $updated = true;
                        $homeAddress->setSubLocality($fields['beneficiary']['homeAddress']['subLocality']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['localAdmin']) && $homeAddress->getLocalAdmin() != $fields['beneficiary']['homeAddress']['localAdmin']) {
                        $updated = true;
                        $homeAddress->setLocalAdmin($fields['beneficiary']['homeAddress']['localAdmin']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['county']) && $homeAddress->getCounty() != $fields['beneficiary']['homeAddress']['county']) {
                        $updated = true;
                        $homeAddress->setCounty($fields['beneficiary']['homeAddress']['county']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['macroCounty']) && $homeAddress->getMacroCounty() != $fields['beneficiary']['homeAddress']['macroCounty']) {
                        $updated = true;
                        $homeAddress->setMacroCounty($fields['beneficiary']['homeAddress']['macroCounty']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['region']) && $homeAddress->getRegion() != $fields['beneficiary']['homeAddress']['region']) {
                        $updated = true;
                        $homeAddress->setRegion($fields['beneficiary']['homeAddress']['region']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['macroRegion']) && $homeAddress->getMacroRegion() != $fields['beneficiary']['homeAddress']['macroRegion']) {
                        $updated = true;
                        $homeAddress->setMacroRegion($fields['beneficiary']['homeAddress']['macroRegion']);
                    }
                    if (isset($fields['beneficiary']['homeAddress']['countryCode']) && $homeAddress->getCountryCode() != $fields['beneficiary']['homeAddress']['countryCode']) {
                        $updated = true;
                        $homeAddress->setCountryCode($fields['beneficiary']['homeAddress']['countryCode']);
                    }
                    if ($updated) {
                        $this->entityManager->persist($homeAddress);
                    }
                }
            }
        } else {
            // new user
            $beneficiary = $this->userManager->createUserFromArray($fields['beneficiary']);
            $newUser = true;
        }
        // add the solidary role if not already granted
        $authItem = $this->authItemRepository->find($structure->hasBeneficiaryAutoApproval() ? AuthItem::ROLE_SOLIDARY_BENEFICIARY : AuthItem::ROLE_SOLIDARY_BENEFICIARY_CANDIDATE);
        if (!$this->userAuthAssignmentRepository->findByAuthItemAndUser($authItem, $beneficiary)) {
            $userAuthAssignment = new UserAuthAssignment();
            $userAuthAssignment->setAuthItem($authItem);
            $beneficiary->addUserAuthAssignment($userAuthAssignment);
        }
        $this->entityManager->persist($beneficiary);

        // check if the beneficiary is already a SolidaryUser
        $solidaryUser = null;
        if (
            (isset($fields['beneficiary']['id']) && !$solidaryUser = $this->solidaryUserRepository->findByUserId($fields['beneficiary']['id'])) ||
            $newUser
            ) {
            // not already a solidary user, we need to create a new one
            $solidaryUser = new SolidaryUser();
            $solidaryUser->setBeneficiary(true);
            $solidaryUser->setUser($beneficiary);
            $solidaryUser->setAddress($beneficiary->getHomeAddress());
            $solidaryUser->setMaxDistance(SolidaryUser::DEFAULT_MAX_DISTANCE);
        } else {
            // already a solidary user, we ensure that it is beneficiary
            $solidaryUser->setBeneficiary(true);
        }
        $this->entityManager->persist($solidaryUser);
        
        // 2 - create the SolidaryUserStructure
        $solidaryUserStructure = null;
        $createSolidaryUserStructure = false;
        if ($solidaryUser->getId() && !$solidaryUserStructure = $this->solidaryUserStructureRepository->findByStructureAndSolidaryUser($structure->getId(), $solidaryUser->getId())) {
            $createSolidaryUserStructure = true;
        } elseif (!$solidaryUser->getId()) {
            $createSolidaryUserStructure = true;
        }

        if ($createSolidaryUserStructure) {
            // no SolidaryUserStructureFound, we need to create a new one
            $solidaryUserStructure = new SolidaryUserStructure();
            $solidaryUserStructure->setSolidaryUser($solidaryUser);
            $solidaryUserStructure->setStructure($structure);
            $solidaryUserStructure->setStatus($structure->hasBeneficiaryAutoApproval() ? SolidaryUserStructure::STATUS_ACCEPTED : SolidaryUserStructure::STATUS_PENDING);
            $this->entityManager->persist($solidaryUserStructure);
        }

        $solidary->setSolidaryUserStructure($solidaryUserStructure);
        
        // 3 - create the proofs
        // we only set the basic proofs, not the files that would be sent on a separate call
        if (isset($fields['proofs'])) {
            foreach ($fields['proofs'] as $aproof) {
                if (!isset($aproof['id'])) {
                    throw new SolidaryException(SolidaryException::STRUCTURE_PROOF_ID_REQUIRED);
                }
                // if (!isset($aproof['value'])) {
                //     throw new SolidaryException(sprintf(SolidaryException::STRUCTURE_PROOF_VALUE_REQUIRED, $aproof['id']));
                // }
                if (!$structureProof = $this->structureProofRepository->find($aproof['id'])) {
                    throw new SolidaryException(sprintf(SolidaryException::STRUCTURE_PROOF_NOT_FOUND, $aproof['id']));
                }
                if (is_null($aproof['value']) && $structureProof->isMandatory()) {
                    throw new SolidaryException(SolidaryException::STRUCTURE_PROOF_VALUE_REQUIRED);
                }
                // we skip null values (meaning the proof is not mandatory and has been omitted during the edition)
                if (!$structureProof->isFile() && !is_null($aproof['value'])) {
                    $proof = new Proof();
                    $proof->setStructureProof($structureProof);
                    $proof->setValue($aproof['value']);
                    $solidaryUserStructure->addProof($proof);
                    $this->entityManager->persist($proof);
                }
            }
        }

        // 4 - prepare the subject
        if (isset($fields['adminsubject'])) {
            if ($subject = $this->subjectRepository->find($fields['adminsubject'])) {
                $solidary->setSubject($subject);
            } else {
                throw new SolidaryException(sprintf(SolidaryException::SUBJECT_NOT_FOUND, $fields['adminsubject']));
            }
        }
        if (isset($fields['additionalSubject'])) {
            $subject = new Subject();
            $subject->setLabel($fields['additionalSubject']);
            $subject->setStructure($structure);
            $subject->setPrivate(true);
            $this->entityManager->persist($subject);
            $solidary->setSubject($subject);
        }

        // 5 - create the SolidaryRecord

        // set original frequency
        $solidary->setFrequency($fields['regular'] ? Criteria::FREQUENCY_REGULAR : Criteria::FREQUENCY_PUNCTUAL);

        // set status
        $solidary->setStatus(Solidary::STATUS_ASKED);

        // set progression
        $solidary->setProgression(0);

        // create needs
        if (isset($fields['needs'])) {
            foreach ($fields['needs'] as $need) {
                if ($need = $this->needRepository->find($need)) {
                    $solidary->addNeed($need);
                } else {
                    throw new SolidaryException(sprintf(SolidaryException::NEED_NOT_FOUND, $need));
                }
            }
        }
        if (isset($fields['additionalNeed'])) {
            $need = new Need();
            $need->setLabel($fields['additionalNeed']);
            $need->setPrivate(true);
            // set the solidary as origin
            $need->setSolidary($solidary);
            $this->entityManager->persist($need);
            $solidary->addNeed($need);
        }
        
        // persist the solidary record
        $this->entityManager->persist($solidary);

        // we need to flush here as we are now about to post the ad => the users need to be persisted
        $this->entityManager->flush();
        
        // 6 - create the proposal
        $params = [
            'origin' => $fields['origin'],
            'destination' => isset($fields['destinationAny']) && $fields['destinationAny'] ? null : $fields['destination'],
            'regular' => $fields['regular'],
            'poster' => $this->poster->getId(),
            'beneficiary' => $beneficiary->getId(),
            'subject' => $subject->getId(),
            'solidary' => $solidary
        ];
        if (isset($fields['punctualOutwardMinDate'])) {
            $params['punctualOutwardMinDate'] = $fields['punctualOutwardMinDate'];
        }
        if (isset($fields['punctualOutwardMaxDate'])) {
            $params['punctualOutwardMaxDate'] = $fields['punctualOutwardMaxDate'];
        }
        if (isset($fields['punctualOutwardMinTime'])) {
            $params['punctualOutwardMinTime'] = $fields['punctualOutwardMinTime'];
        }
        if (isset($fields['punctualOutwardDateChoice'])) {
            $params['punctualOutwardDateChoice'] = $fields['punctualOutwardDateChoice'];
            $solidary->setPunctualOutwardDateChoice($fields['punctualOutwardDateChoice']);
        }
        if (isset($fields['punctualOutwardTimeChoice'])) {
            $params['punctualOutwardTimeChoice'] = $fields['punctualOutwardTimeChoice'];
            $solidary->setPunctualOutwardTimeChoice($fields['punctualOutwardTimeChoice']);
        }
        if (isset($fields['punctualReturnDateChoice'])) {
            $params['punctualReturnDateChoice'] = $fields['punctualReturnDateChoice'];
            $solidary->setPunctualReturnDateChoice($fields['punctualReturnDateChoice']);
        }
        if (isset($fields['punctualReturnDate'])) {
            $params['punctualReturnDate'] = $fields['punctualReturnDate'];
        }
        if (isset($fields['punctualReturnTime'])) {
            $params['punctualReturnTime'] = $fields['punctualReturnTime'];
        }
        if (isset($fields['regularDateChoice'])) {
            $solidary->setRegularDateChoice($fields['regularDateChoice']);
        }
        if (isset($fields['regularMinDate'])) {
            $params['regularMinDate'] = $fields['regularMinDate'];
        }
        if (isset($fields['regularMaxDate'])) {
            $params['regularMaxDate'] = $fields['regularMaxDate'];
        }
        if (isset($fields['regularSchedules'])) {
            $params['regularSchedules'] = $fields['regularSchedules'];
        }
        $ad = $this->createAdFromArray($params, $this->getTimeAndMarginForStructure($structure));
        $solidary->setProposal($this->proposalRepository->find($ad->getId()));

        // 7. solidary transport matching
        $this->solidaryTransportMatcher->match($solidary);

        $this->entityManager->persist($solidary);
        $this->entityManager->flush();

        // link potential outward and return solidaryMatchings that would have not been made yet (as the link between Matchings are made after the SolidaryMatchings)
        $this->solidaryMatchingRepository->linkRelatedSolidaryMatchings($solidary->getId());
        
        // send an event to warn that a SolidaryRecord has been created
        $event = new SolidaryCreatedEvent($this->poster, $solidary);
        $this->eventDispatcher->dispatch(SolidaryCreatedEvent::NAME, $event);

        // read the solidary record again to get the last data (events should have update it !)
        return $this->solidaryRepository->find($solidary->getId());
    }

    /**
     * Create a new SolidaryMatching from a carpool Matching
     *
     * @param Matching  $matching   The matching
     * @param Solidary  $solidary   The solidary record
     * @return void
     */
    public function createSolidaryMatchingFromCarpoolMatching(Matching $matching, Solidary $solidary)
    {
        $solidaryMatching = new SolidaryMatching();
        $solidaryMatching->setMatching($matching);
        $solidaryMatching->setSolidary($solidary);
        $solidaryMatching->setType($matching->getProposalRequest()->getType());
        $solidary->addSolidaryMatching($solidaryMatching);
        $this->entityManager->persist($solidaryMatching);
        $this->entityManager->persist($solidary);
        $this->entityManager->flush();
        $this->logger->info("SolidaryManager : persist the SolidaryMatching for matching " . $matching->getId() . " | " . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
    }

    /**
     * Patch a solidary record.
     *
     * @param Solidary      $solidary               The solidary to update
     * @param array         $fields                 The updated fields
     * @return Solidary     The solidary updated
     */
    public function patchSolidary(Solidary $solidary, array $fields)
    {
        // check if a new animation has been made
        if (array_key_exists('animation', $fields)) {
            return $this->addAnimation($solidary, $fields['animation']);
        }

        // check if a new driver has been selected
        if (array_key_exists('solution', $fields)) {
            return $this->addSolution($solidary, $fields['solution']);
        }

        // check if a new message has been sent
        if (array_key_exists('message', $fields)) {
            return $this->addMessage($solidary, $fields['message']);
        }

        // persist the solidary record
        $this->entityManager->persist($solidary);
        $this->entityManager->flush();

        return $solidary;
    }

    /**
     * Add an animation to a solidary record.
     *
     * @param Solidary      $solidary               The solidary to update
     * @param array         $animation              The animation fields
     * @return Solidary     The solidary updated
     */
    private function addAnimation(Solidary $solidary, array $animation)
    {
        if (!array_key_exists('action', $animation)) {
            throw new SolidaryException(SolidaryException::SOLIDARY_ACTION_REQUIRED);
        }
        if (!array_key_exists('user', $animation)) {
            throw new SolidaryException(SolidaryException::SOLIDARY_ACTION_USER_REQUIRED);
        }
        if (!$action = $this->actionRepository->find($animation['action'])) {
            throw new SolidaryException(sprintf(SolidaryException::SOLIDARY_ACTION_NOT_FOUND, $animation['action']));
        }
        if (!$user = $this->userRepository->find($animation['user'])) {
            throw new SolidaryException(sprintf(SolidaryException::SOLIDARY_ACTION_USER_NOT_FOUND, $animation['user']));
        }
        $newAnimation = new Animation();
        $newAnimation->setSolidary($solidary);
        $newAnimation->setAction($action);
        $newAnimation->setUser($user);
        $newAnimation->setAuthor($this->poster);
        if (array_key_exists('comment', $animation)) {
            $newAnimation->setComment(nl2br(strip_tags($animation['comment'])));
        }
        // if (array_key_exists('message', $animation)) {
        //     // there's a message associated with the animation, we need to build a Message object
        //     $message = new Message();
        //     $message->setText($animation['message']);
        //     if (array_key_exists('messageDelegated', $animation) && $animation['messageDelegated'] == 1) {
        //         // message sent as delegated ('in the name of')
        //         $message->setUser($solidary->getSolidaryUserStructure()->getSolidaryUser()->getUser());
        //         $message->setUserDelegate($this->poster);
        //     } else {
        //         $message->setUser($this->poster);
        //     }
        //     $newAnimation->setMessage($message);
        // }
        if (array_key_exists('progression', $animation)) {
            if ($animation['progression']>0) {
                $newAnimation->setProgression($animation['progression']);
            }
        }
        // send event to warn that an animation has been made
        $event = new AnimationMadeEvent($newAnimation);
        $this->eventDispatcher->dispatch(AnimationMadeEvent::NAME, $event);

        // check again for progression to set the solidary progression
        if (array_key_exists('progression', $animation)) {
            if ($animation['progression']>0) {
                $solidary->setProgression($animation['progression']);
                $this->entityManager->persist($solidary);
                $this->entityManager->flush();
            }
        }
        
        // we don't go further, the event subscribers have done the job to persist the data, although we need a complete solidary !
        return $this->getSolidary($solidary->getId());
    }

    /**
     * Add a solution to a solidary record.
     *
     * @param Solidary      $solidary               The solidary to update
     * @param array         $solution               The solution fields
     * @return Solidary     The solidary updated
     */
    private function addSolution(Solidary $solidary, array $solution)
    {
        if (!array_key_exists('matching', $solution)) {
            throw new SolidaryException(SolidaryException::SOLIDARY_SOLUTION_MATCHING_REQUIRED);
        }
        if (!$solidaryMatching = $this->solidaryMatchingRepository->find($solution['matching'])) {
            throw new SolidaryException(sprintf(SolidaryException::SOLIDARY_SOLUTION_MATCHING_NOT_FOUND, $solution['matching']));
        }
        if (!array_key_exists('carpooler', $solution) && !array_key_exists('transporter', $solution)) {
            throw new SolidaryException(SolidaryException::SOLIDARY_SOLUTION_ROLE_REQUIRED);
        }
        if (array_key_exists('carpooler', $solution) && !$user = $this->userRepository->find($solution['carpooler'])) {
            throw new SolidaryException(sprintf(SolidaryException::SOLIDARY_SOLUTION_USER_NOT_FOUND, $solution['carpooler']));
        }
        if (array_key_exists('transporter', $solution) && !$user = $this->userRepository->find($solution['transporter'])) {
            throw new SolidaryException(sprintf(SolidaryException::SOLIDARY_SOLUTION_USER_NOT_FOUND, $solution['transporter']));
        }

        // create solution
        $solidarySolution = new SolidarySolution();
        $solidarySolution->setSolidaryMatching($solidaryMatching);
        $solidarySolution->setSolidary($solidaryMatching->getSolidary());
        $solidary->addSolidarySolution($solidarySolution);
        $this->entityManager->persist($solidarySolution);

        // if auto link a possible return solution
        if ($solidaryMatching->getSolidaryMatchingLinked()) {
            $solidarySolutionLinked = new SolidarySolution();
            $solidarySolutionLinked->setSolidaryMatching($solidaryMatching->getSolidaryMatchingLinked());
            $solidarySolutionLinked->setSolidary($solidaryMatching->getSolidary());
            $solidary->addSolidarySolution($solidarySolutionLinked);
            $this->entityManager->persist($solidarySolutionLinked);
        }

        // if no auto link, uncomment to link possible outward/return solution
        // foreach ($solidaryMatching->getSolidary()->getSolidarySolutions() as $solution) {
        //     /**
        //      * @var SolidarySolution $solution
        //      */
        //     if ($solution->getSolidaryMatching()->getSolidaryMatchingLinked() &&  $solution->getSolidaryMatching()->getSolidaryMatchingLinked()->getId() == $solidaryMatching->getId()) {
        //         $solidarySolution->setSolidarySolutionLinked($solution);
        //     }
        // }
        
        $this->entityManager->flush();
        
        // we need a complete solidary
        return $this->getSolidary($solidary->getId());
    }

    /**
     * Add a message to a solidary record.
     *
     * @param Solidary      $solidary               The solidary to update
     * @param array         $message                The message fields
     * @return Solidary     The solidary updated
     */
    private function addMessage(Solidary $solidary, array $message)
    {
        if (!array_key_exists('solution', $message)) {
            throw new SolidaryException(SolidaryException::SOLIDARY_MESSAGE_SOLUTION_REQUIRED);
        }
        if (!$solidarySolution = $this->solidarySolutionRepository->find($message['solution'])) {
            throw new SolidaryException(sprintf(SolidaryException::SOLIDARY_MESSAGE_SOLUTION_NOT_FOUND, $message['solution']));
        }
        if (!array_key_exists('text', $message)) {
            throw new SolidaryException(SolidaryException::SOLIDARY_MESSAGE_TEXT_REQUIRED);
        }

        // create new message
        // first we check if there's already a solidaryAsk related with the solution
        $solidaryAsk = $solidarySolution->getSolidaryAsk();
        $firstMessage = null;
        if (!$solidaryAsk) {
            // create the solidaryAsk
            $solidaryAsk = new SolidaryAsk();
            $solidaryAsk->setStatus(SolidaryAsk::STATUS_ASKED);
            $solidaryAsk->setSolidarySolution($solidarySolution);
            // uncomment and complete for full mode
            // if ($solidarySolution->getSolidaryMatching()->getMatching()) {
            //     // the solution is a carpooler, we need to create an associated Ask
            //     $ask = new Ask();
            //     ...
            //     $solidaryAsk->setAsk($ask);
            // }
            $this->entityManager->persist($solidaryAsk);
        } else {
            $firstMessage = $this->messageRepository->findFirstForSolidaryAsk($solidaryAsk);
        }
        
        // create the message and recipient
        $newMessage = new Message();
        $newMessage->setText(nl2br(strip_tags($message['text'])));
        $newMessage->setUserDelegate($this->poster);
        $newMessage->setUser($solidarySolution->getSolidary()->getSolidaryUserStructure()->getSolidaryUser()->getUser());
        if ($firstMessage) {
            $newMessage->setMessage($firstMessage);
        }
        $recipient = new Recipient();
        $recipient->setStatus(Recipient::STATUS_PENDING);
        if ($solidarySolution->getSolidaryMatching()->getMatching()) {
            // the solution is a carpooler
            $recipient->setUser($solidarySolution->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser());
        } else {
            // the solution is a volunteer
            $recipient->setUser($solidarySolution->getSolidaryMatching()->getSolidaryUser()->getUser());
        }
        $newMessage->addRecipient($recipient);
        
        // create the solidaryAskHistory
        $solidaryAskHistory = new SolidaryAskHistory();
        $solidaryAskHistory->setStatus($solidaryAsk->getStatus());
        $solidaryAskHistory->setMessage($newMessage);
        $solidaryAsk->addSolidaryAskHistory($solidaryAskHistory);

        $this->entityManager->persist($solidaryAskHistory);
        $this->entityManager->persist($newMessage);
        $this->entityManager->flush();

        // we need a complete solidary
        return $this->getSolidary($solidarySolution->getSolidary()->getId());
    }

    /**
     * Delete a solidary record.
     *
     * @param Solidary      $solidary  The solidary to delete
     * @return void
     */
    public function deleteSolidary(Solidary $solidary)
    {
        $this->entityManager->remove($solidary);
        $this->entityManager->flush();
    }

    /**
     * Create an Ad from an array
     *
     * The array can contain the following informations :
     * - origin (mandatory)
     * - destination (mandatory, value can be null though)
     * - regular (T/F)
     * - punctualOutwardDateChoice :    1 = chosen outward date, 2 = in the next 7 days, 3 = in the next 15 days, 4 = in the next 30 days
     * - punctualOutwardTimeChoice :    1 = chosen outward time, 2 = between mMin and mMax, 3 = between aMin and aMax, 4 = between eMin and eMax
     * - punctualOutwardMinDate :       computed outward min date
     * - punctualOutwardMinTime :       computed outward min time
     * - punctualOutwardMaxDate :       computed outward max date
     * - punctualReturnDateChoice :     1 = no return, 2 = one hour later, 3 = 2 hours later, 4 = 3 hours later, 5 = chosen return date and time
     * - punctualReturnDate :           computed return date
     * - punctualReturnTime :           computed return time
     * - regularMinDate :               chosen regular min date
     * - regularDateChoice :            1 = for a week, 2 = for a month, 3 = till a given date
     * - regularMaxDate :               computed regular max date
     * - regularSchedules [
     *      days [
     *          1,2,3...7
     *      ],
     *      outwardTime,
     *      returnTime
     * ]
     *
     * @param array $aad                The ad informations as an array
     * @param array $times              The time ranges (depends on the structure)
     * @return Ad                       The Ad object
     */
    private function createAdFromArray(array $aad, array $times): Ad
    {
        $ad = new Ad();

        // users
        $ad->setPosterId($aad['poster']);
        $ad->setUserId($aad['beneficiary']);

        // subject
        $ad->setSubjectId($aad['subject']);

        // origin & destination
        $origin = new Address();
        $destination = null;
        
        if (isset($aad['origin']['houseNumber'])) {
            $origin->setHouseNumber($aad['origin']['houseNumber']);
        }
        if (isset($aad['origin']['street'])) {
            $origin->setStreet($aad['origin']['street']);
        }
        if (isset($aad['origin']['streetAddress'])) {
            $origin->setStreetAddress($aad['origin']['streetAddress']);
        }
        if (isset($aad['origin']['postalCode'])) {
            $origin->setPostalCode($aad['origin']['postalCode']);
        }
        if (isset($aad['origin']['subLocality'])) {
            $origin->setSubLocality($aad['origin']['subLocality']);
        }
        if (isset($aad['origin']['addressLocality'])) {
            $origin->setAddressLocality($aad['origin']['addressLocality']);
        }
        if (isset($aad['origin']['localAdmin'])) {
            $origin->setLocalAdmin($aad['origin']['localAdmin']);
        }
        if (isset($aad['origin']['county'])) {
            $origin->setCounty($aad['origin']['county']);
        }
        if (isset($aad['origin']['macroCounty'])) {
            $origin->setMacroCounty($aad['origin']['macroCounty']);
        }
        if (isset($aad['origin']['region'])) {
            $origin->setRegion($aad['origin']['region']);
        }
        if (isset($aad['origin']['macroRegion'])) {
            $origin->setMacroRegion($aad['origin']['macroRegion']);
        }
        if (isset($aad['origin']['addressCountry'])) {
            $origin->setAddressCountry($aad['origin']['addressCountry']);
        }
        if (isset($aad['origin']['countryCode'])) {
            $origin->setCountryCode($aad['origin']['countryCode']);
        }
        if (isset($aad['origin']['latitude'])) {
            $origin->setLatitude($aad['origin']['latitude']);
        }
        if (isset($aad['origin']['longitude'])) {
            $origin->setLongitude($aad['origin']['longitude']);
        }
        
        if ($aad['destination']) {
            $destination = new Address();
            if (isset($aad['destination']['houseNumber'])) {
                $destination->setHouseNumber($aad['destination']['houseNumber']);
            }
            if (isset($aad['destination']['street'])) {
                $destination->setStreet($aad['destination']['street']);
            }
            if (isset($aad['destination']['streetAddress'])) {
                $destination->setStreetAddress($aad['destination']['streetAddress']);
            }
            if (isset($aad['destination']['postalCode'])) {
                $destination->setPostalCode($aad['destination']['postalCode']);
            }
            if (isset($aad['destination']['subLocality'])) {
                $destination->setSubLocality($aad['destination']['subLocality']);
            }
            if (isset($aad['destination']['addressLocality'])) {
                $destination->setAddressLocality($aad['destination']['addressLocality']);
            }
            if (isset($aad['destination']['localAdmin'])) {
                $destination->setLocalAdmin($aad['destination']['localAdmin']);
            }
            if (isset($aad['destination']['county'])) {
                $destination->setCounty($aad['destination']['county']);
            }
            if (isset($aad['destination']['macroCounty'])) {
                $destination->setMacroCounty($aad['destination']['macroCounty']);
            }
            if (isset($aad['destination']['region'])) {
                $destination->setRegion($aad['destination']['region']);
            }
            if (isset($aad['destination']['macroRegion'])) {
                $destination->setMacroRegion($aad['destination']['macroRegion']);
            }
            if (isset($aad['destination']['addressCountry'])) {
                $destination->setAddressCountry($aad['destination']['addressCountry']);
            }
            if (isset($aad['destination']['countryCode'])) {
                $destination->setCountryCode($aad['destination']['countryCode']);
            }
            if (isset($aad['destination']['latitude'])) {
                $destination->setLatitude($aad['destination']['latitude']);
            }
            if (isset($aad['destination']['longitude'])) {
                $destination->setLongitude($aad['destination']['longitude']);
            }
        } else {
            $ad->setNoDestination(true);
            $destination = clone $origin;
        }

        $ad->setOutwardWaypoints([clone $origin, clone $destination]);
        $ad->setReturnWaypoints([clone $destination, clone $origin]);
        
        // role
        $ad->setRole(Ad::ROLE_PASSENGER);
        
        // we set the ad as a solidary ad
        $ad->setSolidary(true);
        $ad->setSolidaryRecord($aad['solidary']);

        // initialize to one way for now
        $ad->setOneWay(true);

        // frequency set to punctual for now
        $ad->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
        if ($aad['regular']) {
            if (!isset($aad['regularMinDate'])) {
                throw new SolidaryException(SolidaryException::REGULAR_MIN_DATE_REQUIRED);
            }
            if (!isset($aad['regularMaxDate'])) {
                throw new SolidaryException(SolidaryException::REGULAR_MAX_DATE_REQUIRED);
            }
            $ad->setFrequency(Criteria::FREQUENCY_REGULAR);
            $ad->setOutwardDate(new DateTime($aad['regularMinDate']));
            $ad->setOutwardLimitDate(new DateTime($aad['regularMaxDate']));
            $ad->setSchedule($aad['regularSchedules']);
            // check if there are returns
            $return = false;
            foreach ($aad['regularSchedules'] as $schedule) {
                if ($schedule['returnTime']) {
                    $return = true;
                    break;
                }
            }
            if ($return) {
                $ad->setOneWay(false);
                $ad->setReturnDate(new DateTime($aad['regularMinDate']));
                $ad->setReturnLimitDate(new DateTime($aad['regularMaxDate']));
            }
        } else {
            $ad->setStrictDate(true);
            $ad->setOutwardDate(new DateTime($aad['punctualOutwardMinDate']));
            $schedule = null;
            switch ($aad['punctualOutwardDateChoice']) {
                case Solidary::PUNCTUAL_OUTWARD_DATE_CHOICE_DATE:
                    break;
                case Solidary::PUNCTUAL_OUTWARD_DATE_CHOICE_7:
                case Solidary::PUNCTUAL_OUTWARD_DATE_CHOICE_15:
                case Solidary::PUNCTUAL_OUTWARD_DATE_CHOICE_30:
                    // transform to regular trip as the date is flexible
                    $ad->setFrequency(Criteria::FREQUENCY_REGULAR);
                    $schedule = ['mon' => true, 'tue' => true, 'wed' => true, 'thu' => true, 'fri' => true, 'sat' => true, 'sun' => true];
                    $ad->setOutwardLimitDate(new DateTime($aad['punctualOutwardMaxDate']));
                    break;
            }
            switch ($aad['punctualOutwardTimeChoice']) {
                case Solidary::PUNCTUAL_TIME_CHOICE_TIME:
                    if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $ad->setOutwardTime($aad['punctualOutwardMinTime']);
                    } else {
                        $schedule['outwardTime'] = $aad['punctualOutwardMinTime'];
                    }
                    break;
                case Solidary::PUNCTUAL_TIME_CHOICE_M:
                    if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $ad->setOutwardTime($times['mTime']);
                    } else {
                        $schedule['outwardTime'] = $times['mTime'];
                    }
                    $ad->setMarginduration($times['mMargin']);
                    break;
                case Solidary::PUNCTUAL_TIME_CHOICE_A:
                    if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $ad->setOutwardTime($times['aTime']);
                    } else {
                        $schedule['outwardTime'] = $times['aTime'];
                    }
                    $ad->setMarginduration($times['aMargin']);
                    break;
                case Solidary::PUNCTUAL_TIME_CHOICE_E:
                    if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $ad->setOutwardTime($times['eTime']);
                    } else {
                        $schedule['outwardTime'] = $times['eTime'];
                    }
                    $ad->setMarginduration($times['eMargin']);
                    break;
            }
            switch ($aad['punctualReturnDateChoice']) {
                case Solidary::PUNCTUAL_RETURN_DATE_CHOICE_NULL:
                    break;
                case Solidary::PUNCTUAL_RETURN_DATE_CHOICE_1:
                    $ad->setOneWay(false);
                    // add 1 hour to outward time
                    $now = new DateTime();
                    if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $now->setTime((int)substr($ad->getOutwardTime(), 0, 2), (int)substr($ad->getOutwardTime(), 3, 2));
                        $now->add(new DateInterval('PT1H'));
                        $ad->setReturnTime($now->format('H:i'));
                        $ad->setReturnDate($ad->getOutwardDate());
                    } else {
                        $now->setTime((int)substr($schedule['outwardTime'], 0, 2), (int)substr($schedule['outwardTime'], 3, 2));
                        $now->add(new DateInterval('PT1H'));
                        $schedule['returnTime'] = $now->format('H:i');
                    }
                    break;
                case Solidary::PUNCTUAL_RETURN_DATE_CHOICE_2:
                    $ad->setOneWay(false);
                    // add 2 hours to outward time
                    $now = new DateTime();
                    if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $now->setTime((int)substr($ad->getOutwardTime(), 0, 2), (int)substr($ad->getOutwardTime(), 3, 2));
                        $now->add(new DateInterval('PT2H'));
                        $ad->setReturnTime($now->format('H:i'));
                        $ad->setReturnDate($ad->getOutwardDate());
                    } else {
                        $now->setTime((int)substr($schedule['outwardTime'], 0, 2), (int)substr($schedule['outwardTime'], 3, 2));
                        $now->add(new DateInterval('PT2H'));
                        $schedule['returnTime'] = $now->format('H:i');
                    }
                    break;
                case Solidary::PUNCTUAL_RETURN_DATE_CHOICE_3:
                    $ad->setOneWay(false);
                    // add 3 hours to outward time
                    $now = new DateTime();
                    if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        $now->setTime((int)substr($ad->getOutwardTime(), 0, 2), (int)substr($ad->getOutwardTime(), 3, 2));
                        $now->add(new DateInterval('PT3H'));
                        $ad->setReturnTime($now->format('H:i'));
                        $ad->setReturnDate($ad->getOutwardDate());
                    } else {
                        $now->setTime((int)substr($schedule['outwardTime'], 0, 2), (int)substr($schedule['outwardTime'], 3, 2));
                        $now->add(new DateInterval('PT3H'));
                        $schedule['returnTime'] = $now->format('H:i');
                    }
                    break;
                case Solidary::PUNCTUAL_RETURN_DATE_CHOICE_DATE:
                    $ad->setOneWay(false);
                    // chosen return date and time => only punctual
                    $ad->setReturnDate(new DateTime($aad['punctualReturnDate']));
                    $ad->setReturnTime($aad['punctualReturnTime']);
                    break;
            }
            if ($ad->getFrequency() == Criteria::FREQUENCY_REGULAR) {
                $ad->setSchedule([$schedule]);
            }
        }

        return $this->adManager->createAd($ad);
    }

    private function getTimeAndMarginForStructure(Structure $structure)
    {
        $now = new Datetime();
        $mMinTime = clone $now;
        $mMaxTime = clone $now;
        $aMinTime = clone $now;
        $aMaxTime = clone $now;
        $eMinTime = clone $now;
        $eMaxTime = clone $now;
        $mMinTime->setTime($structure->getMMinTime()->format('H'), $structure->getMMinTime()->format('i'));
        $mMaxTime->setTime($structure->getMMaxTime()->format('H'), $structure->getMMaxTime()->format('i'));
        $aMinTime->setTime($structure->getAMinTime()->format('H'), $structure->getAMinTime()->format('i'));
        $aMaxTime->setTime($structure->getAMaxTime()->format('H'), $structure->getAMaxTime()->format('i'));
        $eMinTime->setTime($structure->getEMinTime()->format('H'), $structure->getEMinTime()->format('i'));
        $eMaxTime->setTime($structure->getEMaxTime()->format('H'), $structure->getEMaxTime()->format('i'));
        $mMargin = ($mMaxTime->getTimestamp() - $mMinTime->getTimestamp()) / 2;
        $aMargin = ($aMaxTime->getTimestamp() - $aMinTime->getTimestamp()) / 2;
        $eMargin = ($eMaxTime->getTimestamp() - $eMinTime->getTimestamp()) / 2;
        return [
            'mTime' => $mMinTime->add(new DateInterval('PT'.$mMargin.'S'))->format('H:i'),
            'aTime' => $aMinTime->add(new DateInterval('PT'.$aMargin.'S'))->format('H:i'),
            'eTime' => $eMinTime->add(new DateInterval('PT'.$eMargin.'S'))->format('H:i'),
            'mMargin' => $mMargin,
            'aMargin' => $aMargin,
            'eMargin' => $eMargin
        ];
    }
}
