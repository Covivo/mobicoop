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

use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Service\ProposalManager;
use App\Communication\Entity\Medium;
use App\Communication\Repository\NotificationRepository;
use App\Community\Repository\CommunityRepository;
use App\Event\Repository\EventRepository;
use App\Image\Entity\Image;
use App\Image\Service\ImageManager;
use App\Import\Entity\UserImport;
use App\Import\Repository\CommunityImportRepository;
use App\Import\Repository\EventImportRepository;
use App\Import\Repository\RelayPointImportRepository;
use App\Import\Repository\UserImportRepository;
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
    private $proposalRepository;
   
    private $imageManager;
    private $eventRepository;
    private $communityRepository;
    private $userRepository;
    private $communityImportRepository;
    private $eventImportRepository;
    private $relayPointImportRepository;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, ProposalRepository $proposalRepository, RelayPointImportRepository $relayPointImportRepository, EventImportRepository $eventImportRepository, CommunityImportRepository $communityImportRepository, ImageManager $imageManager, UserImportRepository $userImportRepository, ProposalManager $proposalManager, UserManager $userManager, RoleRepository $roleRepository, NotificationRepository $notificationRepository, EventRepository $eventRepository, UserRepository $userRepository, CommunityRepository $communityRepository)
    {
        $this->entityManager = $entityManager;
        $this->userImportRepository = $userImportRepository;
        $this->proposalManager = $proposalManager;
        $this->userManager = $userManager;
        $this->roleRepository = $roleRepository;
        $this->notificationRepository = $notificationRepository;
        $this->proposalRepository = $proposalRepository;
        $this->imageManager = $imageManager;

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
     * @param string $origin    The origin of the data
     * @return array    The users imported
     */
    public function treatUserImport(string $origin)
    {
        set_time_limit(3600);
        //gc_enable();
        
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);

        // create user_import rows
        $conn = $this->entityManager->getConnection();
        $sql = "INSERT INTO user_import (user_id,origin,status,created_date) SELECT id, '" . $origin . "'," . UserImport::STATUS_IMPORTED . ", '" . (new \DateTime())->format('Y-m-d') . "' FROM user";
        $stmt = $conn->prepare($sql);
        $stmt->execute();


        // REPAIR

        // update proposal : set private to 0 if initialized to null
        $sql = "UPDATE proposal SET private = 0 WHERE private is null";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // update criteria : set checks to null where time is not filled
        $sql = "UPDATE criteria SET mon_check = null WHERE mon_check IS NOT NULL and mon_time is null";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $sql = "UPDATE criteria SET tue_check = null WHERE tue_check IS NOT NULL and tue_time is null";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $sql = "UPDATE criteria SET wed_check = null WHERE wed_check IS NOT NULL and wed_time is null";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $sql = "UPDATE criteria SET thu_check = null WHERE thu_check IS NOT NULL and thu_time is null";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $sql = "UPDATE criteria SET fri_check = null WHERE fri_check IS NOT NULL and fri_time is null";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $sql = "UPDATE criteria SET sat_check = null WHERE sat_check IS NOT NULL and sat_time is null";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $sql = "UPDATE criteria SET sun_check = null WHERE sun_check IS NOT NULL and sun_time is null";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // we have to treat all the users that have just been imported
        // first pass : update status before treatment
        $q = $this->entityManager
        ->createQuery('UPDATE App\Import\Entity\UserImport u set u.status = :status, u.treatmentUserStartDate=:treatmentDate WHERE u.status=:oldStatus')
        ->setParameters([
            'status'=>UserImport::STATUS_USER_PENDING,
            'treatmentDate'=>new \DateTime(),
            'oldStatus'=>UserImport::STATUS_IMPORTED
        ]);
        $q->execute();

        // create user_notification rows
        $sql = "INSERT INTO user_notification (notification_id,user_id,active,created_date)
        SELECT n.id,u.id,IF (u.phone_validated_date IS NULL AND n.medium_id = " . Medium::MEDIUM_SMS . ",0,IF (u.ios_app_id IS NULL AND u.android_app_id IS NULL AND n.medium_id = " . Medium::MEDIUM_PUSH . ",0,n.user_active_default)),'" . (new \DateTime())->format('Y-m-d') . "'
        FROM user_import i LEFT JOIN user u ON u.id = i.user_id
        JOIN notification n
        WHERE n.user_editable=1";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $q = $this->entityManager
        ->createQuery('UPDATE App\Import\Entity\UserImport u set u.status = :status WHERE u.status=:oldStatus')
        ->setParameters([
            'status'=>UserImport::STATUS_USER_TREATED,
            'oldStatus'=>UserImport::STATUS_USER_PENDING
        ]);
        $q->execute();

        // batch for criterias / direction
        $batch = 50;
        $this->proposalManager->setDirectionsAndDefaultsForImport($batch);

        // update addresses with geojson point data
        $conn = $this->entityManager->getConnection();
        $sql = "UPDATE address SET geo_json = PointFromText(CONCAT('POINT(',longitude,' ',latitude,')'),1) WHERE geo_json IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $q = $this->entityManager
        ->createQuery('UPDATE App\Import\Entity\UserImport u set u.status = :status, u.treatmentUserEndDate=:treatmentDate WHERE u.status=:oldStatus')
        ->setParameters([
            'status'=>UserImport::STATUS_DIRECTION_TREATED,
            'treatmentDate'=>new \DateTime(),
            'oldStatus'=>UserImport::STATUS_USER_TREATED
        ]);
        $q->execute();

        //gc_collect_cycles();

        return [];
    }

    /**
     * Match imported users
     *
     * @return array    The users imported
     */
    public function matchUserImport()
    {
        set_time_limit(50000);
        
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
                
        // creation of the matchings
        // we create an array of all proposals to treat
        $proposalIds = $this->proposalRepository->findImportedProposals(UserImport::STATUS_DIRECTION_TREATED);

        $this->proposalManager->createMatchingsForProposals($proposalIds);

        // treat the return and opposite
        $proposals = $this->proposalManager->createLinkedAndOppositesForProposals($proposalIds);

        return [];
    }


    //Function for import community image from V1
    public function importCommunityImage()
    {
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
}
