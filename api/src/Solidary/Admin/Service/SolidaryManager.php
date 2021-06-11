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
use App\Solidary\Entity\SolidaryMatching;
use App\Solidary\Entity\SolidarySolution;
use App\Solidary\Event\SolidaryCreatedEvent;
use App\Solidary\Repository\NeedRepository;
use App\Solidary\Repository\SolidaryRepository;
use App\Solidary\Repository\SolidaryUserRepository;
use App\Solidary\Repository\SolidaryUserStructureRepository;
use App\Solidary\Repository\StructureProofRepository;
use App\Solidary\Repository\StructureRepository;
use App\Solidary\Repository\SubjectRepository;
use App\User\Entity\User;
use App\Carpool\Entity\Matching;
use App\User\Admin\Service\UserManager;
use App\User\Repository\UserRepository;
use DateTime;
use DateInterval;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Security;

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

    /**
     * Constructor
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        Security $security,
        UserManager $userManager,
        AdManager $adManager,
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
        SolidaryRepository $solidaryRepository
    ) {
        $this->poster = $security->getUser();
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->adManager = $adManager;
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

        // set solutions
        $solutions = [];
        foreach ($solidary->getSolidarySolutions() as $solution) {
            /**
             * @var SolidarySolution $solution
             */
            if ($solution->getSolidaryMatching()->getSolidaryUser()) {
                $solutions[] = [
                    'id' => $solution->getId(),
                    'type' => SolidarySolution::TRANSPORTER,
                    'givenName' => $solution->getSolidaryMatching()->getSolidaryUser()->getUser()->getGivenName(),
                    'familyName' => $solution->getSolidaryMatching()->getSolidaryUser()->getUser()->getFamilyName(),
                    'telephone' => $solution->getSolidaryMatching()->getSolidaryUser()->getUser()->getTelephone(),
                    'avatar' => $solution->getSolidaryMatching()->getSolidaryUser()->getUser()->getAvatar(),
                    'userId' => $solution->getSolidaryMatching()->getSolidaryUser()->getUser()->getId()
                ];
            } elseif ($solution->getSolidaryMatching()->getMatching()) {
                $solutions[] = [
                    'id' => $solution->getId(),
                    'type' => SolidarySolution::CARPOOLER,
                    'givenName' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser()->getGivenName(),
                    'familyName' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser()->getFamilyName(),
                    'telephone' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser()->getTelephone(),
                    'avatar' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser()->getAvatar(),
                    'userId' => $solution->getSolidaryMatching()->getMatching()->getProposalOffer()->getUser()->getId()
                ];
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
                'progression' => $diary->getProgression(),
                'authorGivenName' => $diary->getAuthor()->getGivenName(),
                'authorFamilyName' => $diary->getAuthor()->getFamilyName(),
                'authorAvatar' => $diary->getAuthor()->getAvatar(),
                'givenName' => $diary->getUser()->getGivenName(),
                'familyName' => $diary->getUser()->getFamilyName(),
                'avatar' => $diary->getUser()->getAvatar(),
                'date' => $diary->getCreatedDate()
            ];
        }
        $solidary->setAdmindiary($diaries);

        return $solidary;
    }

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

    private function createSchedule($num, $outwardTime = null, $returnTime = null)
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
        // 5. create a Proposal, reflecting the journey needed for the beneficiary
        // 6. create a SolidaryRecord, linked to the SolidaryUserStructure, the Proposal, the Subject and Needs


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

        // we need to flush here has we are now about to post the ad => the users need to be persisted
        $this->entityManager->flush();
        
        // 5 - create the proposal
        $params = [
            'origin' => $fields['origin'],
            'destination' => isset($fields['destinationAny']) && $fields['destinationAny'] ? null : $fields['destination'],
            'regular' => $fields['regular'],
            'poster' => $this->poster->getId(),
            'beneficiary' => $beneficiary->getId(),
            'subject' => $subject->getId()
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


        // 6 - create the SolidaryRecord

        // set original frequency
        $solidary->setFrequency($fields['regular'] ? Criteria::FREQUENCY_REGULAR : Criteria::FREQUENCY_PUNCTUAL);

        // set status
        $solidary->setStatus(Solidary::STATUS_ASKED);

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

        // create the solidary matchings
        foreach ($solidary->getProposal()->getMatchingOffers() as $matchingOffer) {
            /**
             * @var Matching $matchingOffer
             */
            $solidaryMatching = new SolidaryMatching();
            $solidaryMatching->setMatching($matchingOffer);
            $solidaryMatching->setSolidary($solidary);
            $solidary->addSolidaryMatching($solidaryMatching);
            $this->entityManager->persist($solidaryMatching);
        }
        
        // persist the solidary record
        $this->entityManager->persist($solidary);
        $this->entityManager->flush();

        // send an event to warn that a SolidaryRecord has been created
        $event = new SolidaryCreatedEvent($beneficiary, $this->poster, $solidary);
        $this->eventDispatcher->dispatch(SolidaryCreatedEvent::NAME, $event);

        // read the solidary record again to get the last data (events should have update it !)
        return $this->solidaryRepository->find($solidary->getId());
    }

    /**
     * Patch a solidary record.
     *
     * @param Solidary      $solidary               The solidary to update
     * @param array         $fields                 The updated fields
     * @return Solidary     The solidary updated
     */
    public function patchSolidary(solidary $solidary, array $fields)
    {
        // persist the solidary record
        $this->entityManager->persist($solidary);
        $this->entityManager->flush();

        return $solidary;
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
            $destination = clone $origin;
        }

        $ad->setOutwardWaypoints([clone $origin, clone $destination]);
        $ad->setReturnWaypoints([clone $destination, clone $origin]);
        
        // role
        $ad->setRole(Ad::ROLE_PASSENGER);
        
        // we set the ad as a solidary ad
        $ad->setSolidary(true);

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
