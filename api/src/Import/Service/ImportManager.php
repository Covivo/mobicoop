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

namespace App\Import\Service;

use App\Carpool\Service\ProposalManager;
use App\Communication\Entity\Medium;
use App\Communication\Repository\NotificationRepository;
use App\Import\Entity\UserImport;
use App\Import\Repository\UserImportRepository;
use App\Right\Entity\Role;
use App\Right\Entity\UserRole;
use App\Right\Repository\RoleRepository;
use App\User\Entity\UserNotification;
use App\User\Service\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use App\User\Entity\User;

/**
 * Import manager service.
 * Used to import external data into the platform.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class ImportManager
{
    private $entityManager;
    private $userImportRepository;
    private $proposalManager;
    private $userManager;
    private $roleRepository;
    private $notificationRepository;
   
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, UserImportRepository $userImportRepository, ProposalManager $proposalManager, UserManager $userManager, RoleRepository $roleRepository, NotificationRepository $notificationRepository)
    {
        $this->entityManager = $entityManager;
        $this->userImportRepository = $userImportRepository;
        $this->proposalManager = $proposalManager;
        $this->userManager = $userManager;
        $this->roleRepository = $roleRepository;
        $this->notificationRepository = $notificationRepository;
    }

    /**
     * Treat imported users
     *
     * @return array    The users imported
     */
    public function treatUserImport()
    {
        set_time_limit(3600);
        gc_enable();
        
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        // we have to treat all the users that have just been imported
        // first pass : update status before treatment
        $q = $this->entityManager
        ->createQuery('UPDATE App\import\Entity\UserImport u set u.status = :status, u.treatmentUserStartDate=:treatmentDate WHERE u.status=:oldStatus')
        ->setParameters([
            'status'=>UserImport::STATUS_USER_PENDING,
            'treatmentDate'=>new \DateTime(),
            'oldStatus'=>UserImport::STATUS_IMPORTED
        ]);
        $q->execute();

        gc_collect_cycles();

        // second pass : user related treatment
        $batch = 50;    // batch for users
        $pool = 0;
        $qCriteria = $this->entityManager->createQuery('SELECT u FROM App\User\Entity\User u JOIN u.import i WHERE i.status='.UserImport::STATUS_USER_PENDING);
        $iterableResult = $qCriteria->iterate();
        foreach ($iterableResult as $row) {
            $user = $row[0];

            // we treat the role
            if (count($user->getUserRoles()) == 0) {
                // we have to add a role
                $role = $this->roleRepository->find(Role::ROLE_USER_REGISTERED_FULL); // can't be defined outside the loop because of the flush/clear...
                $userRole = new UserRole();
                $userRole->setRole($role);
                $user->addUserRole($userRole);
            }

            // we treat the notifications
            if (count($user->getUserNotifications()) == 0) {
                // we have to create the default user notifications, we don't persist immediately
                $notifications = $this->notificationRepository->findUserEditable(); // can't be defined outside the loop because of the flush/clear...
                foreach ($notifications as $notification) {
                    $userNotification = new UserNotification();
                    $userNotification->setNotification($notification);
                    $userNotification->setActive($notification->isUserActiveDefault());
                    if ($userNotification->getNotification()->getMedium()->getId() == Medium::MEDIUM_SMS && is_null($user->getPhoneValidatedDate())) {
                        // check telephone for sms
                        $userNotification->setActive(false);
                    } elseif ($userNotification->getNotification()->getMedium()->getId() == Medium::MEDIUM_PUSH && is_null($user->getIosAppId()) && is_null($user->getAndroidAppId())) {
                        // check apps for push
                        $userNotification->setActive(false);
                    }
                    $user->addUserNotification($userNotification);
                }
            }
            //$this->entityManager->persist($user);

            // batch
            $pool++;
            if ($pool>=$batch) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                gc_collect_cycles();
                $pool = 0;
            }
        }
        // final flush for pending persists
        $this->entityManager->flush();
        $this->entityManager->clear();
        gc_collect_cycles();

        $q = $this->entityManager
        ->createQuery('UPDATE App\import\Entity\UserImport u set u.status = :status WHERE u.status=:oldStatus')
        ->setParameters([
            'status'=>UserImport::STATUS_USER_TREATED,
            'oldStatus'=>UserImport::STATUS_USER_PENDING
        ]);
        $q->execute();

        // batch for criterias / direction
        $batch = 500;

        $this->proposalManager->setDirectionsAndDefaultsForImport($batch);

        gc_collect_cycles();

        return [];
    }

    /**
     * Match imported users
     *
     * @return array    The users imported
     */
    public function matchUserImport()
    {
        set_time_limit(3600);
        gc_enable();
        
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        // creation of the matchings
        // we create an array of all proposals to treat
        $importedUsers = $this->userImportRepository->findBy(['status'=>UserImport::STATUS_DIRECTION_TREATED]);
        $proposals = [];
        foreach ($importedUsers as $import) {
            foreach ($import->getUser()->getProposals() as $proposal) {
                // we exclude the proposals that have no directions... can happen if no routes are found !
                if (!is_null($proposal->getCriteria()->getDirectionDriver()) || !is_null($proposal->getCriteria()->getDirectionPassenger())) {
                    $proposals[$proposal->getId()] = $proposal;
                }
            }
        }
        $this->proposalManager->createMatchingsForProposals($proposals);

        // treat the return and opposite
        $proposals = $this->proposalManager->createLinkedAndOppositesForProposals($proposals);

        $q = $this->entityManager
        ->createQuery('UPDATE App\import\Entity\UserImport u set u.status = :status WHERE u.status=:oldStatus')
        ->setParameters([
            'status'=>UserImport::STATUS_MATCHING_TREATED,
            'oldStatus'=>UserImport::STATUS_DIRECTION_TREATED
        ]);
        $q->execute();

        return [];
    }
}
