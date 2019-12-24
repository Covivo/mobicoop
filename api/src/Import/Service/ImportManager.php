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
use App\Community\Repository\CommunityRepository;
use App\Event\Repository\EventRepository;
use App\Image\Entity\Image;
use App\Image\Service\ImageManager;
use App\Import\Entity\CommunityImport;
use App\Import\Entity\EventImport;
use App\Import\Entity\RelayPointImport;
use App\Import\Entity\UserImport;
use App\Import\Repository\CommunityImportRepository;
use App\Import\Repository\EventImportRepository;
use App\Import\Repository\RelayPointImportRepository;
use App\Import\Repository\UserImportRepository;
use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Repository\RelayPointRepository;
use App\Right\Entity\Role;
use App\Right\Entity\UserRole;
use App\Right\Repository\RoleRepository;
use App\User\Entity\UserNotification;
use App\User\Repository\UserRepository;
use App\User\Service\UserManager;
use Doctrine\Common\Util\ClassUtils;
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
    private $imageManager;
    private $eventRepository;
    private $communityRepository;
    private $userRepository;
    private $communityImportRepository;
    private $eventImportRepository;
    private $relayPointImportRepository;
    private $relayPointRepository;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager,RelayPointRepository $relayPointRepository, RelayPointImportRepository $relayPointImportRepository, EventImportRepository $eventImportRepository, CommunityImportRepository $communityImportRepository, ImageManager $imageManager, UserImportRepository $userImportRepository, ProposalManager $proposalManager, UserManager $userManager, RoleRepository $roleRepository, NotificationRepository $notificationRepository, EventRepository $eventRepository, UserRepository $userRepository, CommunityRepository $communityRepository)
    {
        $this->entityManager = $entityManager;
        $this->userImportRepository = $userImportRepository;
        $this->proposalManager = $proposalManager;
        $this->userManager = $userManager;
        $this->roleRepository = $roleRepository;
        $this->notificationRepository = $notificationRepository;
        $this->imageManager = $imageManager;
        $this->relayPointRepository = $relayPointRepository;

        $this->eventRepository = $eventRepository;
        $this->communityRepository = $communityRepository;
        $this->userRepository = $userRepository;
        $this->communityImportRepository = $communityImportRepository;
        $this->eventImportRepository = $eventImportRepository;
        $this->relayPointImportRepository = $relayPointImportRepository;
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

        // exit;

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


    //Function for import community image from V1
    public function importCommunityImage()
    {
        if( $this->communityImportRepository->findOneBy(array('id' => 1)) == null){
            $this->importCommunityIfNotMigrate();
        }

        $dir = "../public/import/Community/";
        $results = array('importer' => 0,'probleme-id-v1' => 0,'already-import' => 0);
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..' && preg_match('#\.(jpe?g|gif|png)$#i', $file)) {
                        $nameExp = explode('_', $file);
                        if ($link = $this->communityImportRepository->findOneBy(array('communityExternalId' => $nameExp[0]))) {
                            if ($link->getStatus() != 1) {
                                $image = new Image();
                                $image->setCommunity($link->getCommunity());
                                $image->setOriginalName($file);

                                $this->setInfosFile($image, $dir.$file);
                                $this->setFilenamePositionAndCopy($image, $dir.$file, "../public/upload/communities/images/");

                                $results['importer'] ++;

                                //L'image de la relation est importer
                                $link->setStatus(1);

                                $this->entityManager->persist($image);
                                $this->entityManager->persist($link);
                                $this->entityManager->flush();

                                $this->imageManager->generateVersions($image);
                            } else {
                                $results['already-import'] ++;
                            }
                        } else {
                            $results['probleme-id-v1'] ++;
                        }
                    }
                }
                closedir($dh);
            }
        }
        return $results;
    }


    public function importEventImage()
    {
        if( $this->eventImportRepository->findBy(array('id' => 1)) == null){
            $this->importEventIfNotMigrate();
        }
        $dir = "../public/import/Event/";
        $results = array('importer' => 0,'probleme-id-v1' => 0,'already-import' => 0);

        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..' && preg_match('#\.(jpe?g|gif|png)$#i', $file)) {
                        $nameExp = explode('_', $file);
                        if ($link = $this->eventImportRepository->findOneBy(array('eventExternalId' => $nameExp[0]))) {
                            if ($link->getStatus() != 1) {
                                $image = new Image();
                                $image->setEvent($link->getEvent());
                                $image->setOriginalName($file);

                                $this->setInfosFile($image, $dir.$file);
                                $this->setFilenamePositionAndCopy($image, $dir.$file, "../public/upload/events/images/");

                                $results['importer'] ++;

                                //L'image de la relation est importer
                                $link->setStatus(1);

                                $this->entityManager->persist($image);
                                $this->entityManager->persist($link);
                                $this->entityManager->flush();

                                $this->imageManager->generateVersions($image);
                            } else {
                                $results['already-import'] ++;
                            }
                        } else {
                            $results['probleme-id-v1'] ++;
                        }
                    }
                }
                closedir($dh);
            }
        }
        return $results;
    }

    public function importUserImage()
    {
        if( $this->userImportRepository->findBy(array('id' => 1)) == null){
            $this->importUserIfNotMigrate();
        }
        $dir = "../public/import/Avatar/";
        $results = array('importer' => 0,'probleme-id-v1' => 0,'probleme-id-v2' => 0);

        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..' && preg_match('#\.(jpe?g|gif|png)$#i', $file)) {
                        $nameExp = explode('_', $file);
                        if ($link = $this->userImportRepository->findOneBy(array('userExternalId' =>  explode('.', $nameExp[1])[0]))) {
                            $image = new Image();
                            $image->setUser($link->getUser());
                            $image->setOriginalName($file);

                            $this->setInfosFile($image, $dir.$file);
                            $this->setFilenamePositionAndCopy($image, $dir.$file, "../public/upload/users/images/");

                            $results['importer'] ++;

                            $this->entityManager->persist($image);
                            $this->entityManager->flush();

                            $this->imageManager->generateVersions($image);
                        } else {
                            $results['probleme-id-v1'] ++;
                        }
                    }
                }
                closedir($dh);
            }
        }
        return $results;
    }

    public function importRelayImage()
    {
        if( $this->relayPointImportRepository->findBy(array('id' => 1)) == null){
            $this->importRelayIfNotMigrate();
        }
        $dir = "../public/import/RelaisPoint/";
        $results = array('importer' => 0,'probleme-id-v1' => 0,'already-import' => 0);

        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file != '.' && $file != '..' && preg_match('#\.(jpe?g|gif|png)$#i', $file)) {
                        $nameExp = explode('_', $file);
                        if ($link = $this->relayPointImportRepository->findOneBy(array('relayExternalId' => $nameExp[0]))) {
                            if ($link->getStatus() != 1) {
                                $image = new Image();
                                $image->setRelayPoint($link->getRelay());
                                $image->setOriginalName($file);

                                $this->setInfosFile($image, $dir.$file);
                                $this->setFilenamePositionAndCopy($image, $dir.$file, "../public/upload/relaypoints/images/");

                                $results['importer'] ++;

                                //L'image de la relation est importer
                                $link->setStatus(1);

                                $this->entityManager->persist($image);
                                $this->entityManager->persist($link);
                                $this->entityManager->flush();

                                $this->imageManager->generateVersions($image);
                            } else {
                                $results['already-import'] ++;
                            }
                        } else {
                            $results['probleme-id-v1'] ++;
                        }
                    }
                }
                closedir($dh);
            }
        }
        return $results;
    }



    //Set the mandatory infos of a file for the image (width,height,mime type...)
    private function setInfosFile(Image $image, $file)
    {
        $infos = getimagesize($file);

        $image->setMimeType($infos['mime']);
        $image->setWidth($infos[0]);
        $image->setHeight($infos[1]);
        $image->setSize(filesize($file));
    }

    //Copy the file in the good directory for the generates versions function
    private function setFilenamePositionAndCopy(Image $image, $file, $directory)
    {
        $position = $this->imageManager->getNextPosition($image);
        $filename = $this->imageManager->generateFilename($image);
        $filenameExtension = $this->imageManager->generateFilename($image).".".pathinfo($file)['extension'];

        $image->setPosition($position);
        $image->setFileName($filenameExtension);
        $image->setName($filename);

        copy($file, $directory.$filenameExtension);
    }

    //If the databases for import is empty, import data community from csv in pulic/importcsv
    // 0 = community V2
    // 1 = community V1
    private function importCommunityIfNotMigrate()
    {
        if (($handle = fopen("../public/import/csv/community_id_corresp.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $importCommunity = new CommunityImport();

                $importCommunity->setCommunity($this->communityRepository->find($data[0]));
                $importCommunity->setCommunityExternalId($data[1]);
                $importCommunity->setStatus(0);

                $this->entityManager->persist($importCommunity);

            }
            fclose($handle);
            $this->entityManager->flush();
        }
    }

    //If the databases for import is empty, import data Event from csv in pulic/importcsv
    // 0 = Event V2
    // 1 = Event V1
    private function importEventIfNotMigrate()
    {
        if (($handle = fopen("../public/import/csv/event_id_corresp.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $importEvent = new EventImport();

                $importEvent->setEvent($this->eventRepository->find($data[0]));
                $importEvent->setEventExternalId($data[1]);
                $importEvent->setStatus(0);

                $this->entityManager->persist($importEvent);

            }
            fclose($handle);
            $this->entityManager->flush();
        }
    }
    //If the databases for import is empty, import data  Relay point from csv in pulic/importcsv
    // 0 = Relay point V2
    // 1 = Relay point  V1
    private function importRelayIfNotMigrate()
    {
        if (($handle = fopen("../public/import/csv/relay_point_id_corresp.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $importRelay = new RelayPointImport();

                $importRelay->setRelay($this->relayPointRepository->find($data[0]));
                $importRelay->setRelayExternalId($data[1]);
                $importRelay->setStatus(0);

                $this->entityManager->persist($importRelay);

            }
            fclose($handle);
            $this->entityManager->flush();
        }
    }

    //If the databases for import is empty, import Users data from csv in pulic/importcsv
    // 0 = User V2
    // 1 = User V1
    private function importUserIfNotMigrate()
    {
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        if (($handle = fopen("../public/import/csv/user_id_corresp.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $importUser = new UserImport();

                $importUser->setUser($this->userRepository->find($data[0]));
                $importUser->setUserExternalId($data[1]);
                $importUser->setStatus(0);
                $importUser->setOrigin('import-csv');
                $importUser->setCreatedDate(new \DateTime());

                $this->entityManager->persist($importUser);

            }
            fclose($handle);
            $this->entityManager->flush();
        }

    }

}
