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
use App\RelayPoint\Repository\RelayPointRepository;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

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
    private $proposalRepository;
    private $imageManager;
    private $eventRepository;
    private $communityRepository;
    private $userRepository;
    private $communityImportRepository;
    private $eventImportRepository;
    private $relayPointImportRepository;
    private $relayPointRepository;
    private $timeLimit;
    private $memoryLimit;
    private $sqlLog;
    private $directionsBatch;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, ProposalRepository $proposalRepository, RelayPointImportRepository $relayPointImportRepository, EventImportRepository $eventImportRepository, CommunityImportRepository $communityImportRepository, ImageManager $imageManager, UserImportRepository $userImportRepository, ProposalManager $proposalManager, EventRepository $eventRepository, UserRepository $userRepository, CommunityRepository $communityRepository, RelayPointRepository $relayPointRepository, int $timeLimit, string $memoryLimit, bool $sqlLog, int $directionsBatch)
    {
        $this->entityManager = $entityManager;
        $this->userImportRepository = $userImportRepository;
        $this->proposalManager = $proposalManager;
        $this->proposalRepository = $proposalRepository;
        $this->imageManager = $imageManager;
        $this->relayPointRepository = $relayPointRepository;
        $this->eventRepository = $eventRepository;
        $this->communityRepository = $communityRepository;
        $this->userRepository = $userRepository;
        $this->communityImportRepository = $communityImportRepository;
        $this->eventImportRepository = $eventImportRepository;
        $this->relayPointImportRepository = $relayPointImportRepository;
        $this->timeLimit = $timeLimit;
        $this->memoryLimit = $memoryLimit;
        $this->sqlLog = $sqlLog;
        $this->directionsBatch = $directionsBatch;
    }

    /**
     * Treat imported users
     *
     * @param string $origin        The origin of the data
     * @param int|null $massId      The mass id if the import concerns a mass matching
     * @param int|null $lowestId    The lowest user id to import if the import concerns new users to import in an existing db
     * @return array    An empty array (for consistency, as the method can be called from an API get collection route)
     */
    public function treatUserImport(string $origin, ?int $massId=null, ?int $lowestId=null): array
    {
        $this->prepareUserImport($origin, $massId, $lowestId);
        $this->matchUserImport();
        return [];
    }

    /**
     * Treat imported users
     *
     * @param string $origin        The origin of the data
     * @param int|null $massId      The mass id if the import concerns a mass matching
     * @param int|null $lowestId    The lowest user id to import if the import concerns new users to import in an existing db
     * @return void
     */
    private function prepareUserImport(string $origin, ?int $massId=null, ?int $lowestId=null): void
    {
        set_time_limit($this->timeLimit);

        if (!$this->sqlLog) {
            $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        }

        // create user_import rows
        $conn = $this->entityManager->getConnection();
        if (!is_null($massId)) {
            // we select the users that have a related mass_person, and that haven't been imported yet (using the null left join trick)
            $sql = "
            INSERT INTO user_import (user_id,origin,status,created_date,user_external_id)
            SELECT u.id, '" . $origin . $massId . "'," . UserImport::STATUS_IMPORTED . ", '" . (new \DateTime())->format('Y-m-d') . "',u.id FROM user u
            INNER JOIN mass_person mp ON mp.user_id = u.id LEFT JOIN user_import ui ON ui.user_id = u.id WHERE ui.user_id is NULL AND mp.mass_id = " . $massId;
        } elseif (!is_null($lowestId)) {
            $sql = "INSERT INTO user_import (user_id,origin,status,created_date,user_external_id) SELECT id, '" . $origin . "'," . UserImport::STATUS_IMPORTED . ", '" . (new \DateTime())->format('Y-m-d') . "',id FROM user WHERE id>=" . $lowestId;
        } else {
            $sql = "INSERT INTO user_import (user_id,origin,status,created_date,user_external_id) SELECT id, '" . $origin . "'," . UserImport::STATUS_IMPORTED . ", '" . (new \DateTime())->format('Y-m-d') . "',id FROM user";
        }
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();


        // REPAIR

        // update proposal : set private to 0 if initialized to null
        $sql = "UPDATE proposal SET private = 0 WHERE private is null";
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();

        // update criteria : set checks to null where time is not filled
        $sql = "UPDATE criteria SET mon_check = null WHERE mon_check IS NOT NULL and mon_time is null";
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
        $sql = "UPDATE criteria SET tue_check = null WHERE tue_check IS NOT NULL and tue_time is null";
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
        $sql = "UPDATE criteria SET wed_check = null WHERE wed_check IS NOT NULL and wed_time is null";
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
        $sql = "UPDATE criteria SET thu_check = null WHERE thu_check IS NOT NULL and thu_time is null";
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
        $sql = "UPDATE criteria SET fri_check = null WHERE fri_check IS NOT NULL and fri_time is null";
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
        $sql = "UPDATE criteria SET sat_check = null WHERE sat_check IS NOT NULL and sat_time is null";
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
        $sql = "UPDATE criteria SET sun_check = null WHERE sun_check IS NOT NULL and sun_time is null";
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();

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
        if (!is_null($massId)) {
            $sql = "INSERT INTO user_notification (notification_id,user_id,active,created_date)
            SELECT n.id,u.id,IF (u.phone_validated_date IS NULL AND n.medium_id = " . Medium::MEDIUM_SMS . ",0,IF ((u.mobile IS NULL OR u.mobile = 0) AND n.medium_id = " . Medium::MEDIUM_PUSH . ",0,n.user_active_default)),'" . (new \DateTime())->format('Y-m-d') . "'
            FROM user_import i LEFT JOIN user u ON u.id = i.user_id INNER JOIN mass_person mp ON mp.user_id = u.id
            JOIN notification n
            WHERE n.user_editable=1 AND mp.mass_id = " . $massId;
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery();
        } elseif (!is_null($lowestId)) {
            $sql = "INSERT INTO user_notification (notification_id,user_id,active,created_date)
            SELECT n.id,u.id,IF (u.phone_validated_date IS NULL AND n.medium_id = " . Medium::MEDIUM_SMS . ",0,IF ((u.mobile IS NULL OR u.mobile = 0) AND n.medium_id = " . Medium::MEDIUM_PUSH . ",0,n.user_active_default)),'" . (new \DateTime())->format('Y-m-d') . "'
            FROM user_import i LEFT JOIN user u ON u.id = i.user_id
            JOIN notification n
            WHERE n.user_editable=1 and i.user_id>=" . $lowestId;
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery();
        } else {
            $sql = "INSERT INTO user_notification (notification_id,user_id,active,created_date)
            SELECT n.id,u.id,IF (u.phone_validated_date IS NULL AND n.medium_id = " . Medium::MEDIUM_SMS . ",0,IF ((u.mobile IS NULL OR u.mobile = 0) AND n.medium_id = " . Medium::MEDIUM_PUSH . ",0,n.user_active_default)),'" . (new \DateTime())->format('Y-m-d') . "'
            FROM user_import i LEFT JOIN user u ON u.id = i.user_id
            JOIN notification n
            WHERE n.user_editable=1";
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery();
        }

        $q = $this->entityManager
        ->createQuery('UPDATE App\Import\Entity\UserImport u set u.status = :status WHERE u.status=:oldStatus')
        ->setParameters([
            'status'=>UserImport::STATUS_USER_TREATED,
            'oldStatus'=>UserImport::STATUS_USER_PENDING
        ]);
        $q->execute();

        // batch for criterias / direction
        $this->proposalManager->setDirectionsAndDefaultsForImport($this->directionsBatch);

        // update addresses with geojson point data
        $conn = $this->entityManager->getConnection();
        $sql = "UPDATE address SET geo_json = PointFromText(CONCAT('POINT(',longitude,' ',latitude,')'),1) WHERE geo_json IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();

        $q = $this->entityManager
        ->createQuery('UPDATE App\Import\Entity\UserImport u set u.status = :status, u.treatmentUserEndDate=:treatmentDate WHERE u.status=:oldStatus')
        ->setParameters([
            'status'=>UserImport::STATUS_DIRECTION_TREATED,
            'treatmentDate'=>new \DateTime(),
            'oldStatus'=>UserImport::STATUS_USER_TREATED
        ]);
        $q->execute();
    }

    /**
     * Match imported users
     *
     * @return array    The users imported
     */
    private function matchUserImport(): array
    {
        set_time_limit($this->timeLimit);

        // user import is a huge memory consumer !
        ini_set('memory_limit', $this->memoryLimit . 'M');

        if (!$this->sqlLog) {
            $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        }

        // creation of the matchings
        // we create an array of all proposals to treat
        $proposalIds = $this->proposalRepository->findImportedProposals(UserImport::STATUS_DIRECTION_TREATED);

        $this->proposalManager->createMatchingsForProposals($proposalIds);

        // treat the return and opposite
        $this->proposalManager->createLinkedAndOppositesForProposals($proposalIds);
    }


    //Function for import community image from V1
    public function importCommunityImage()
    {
        if ($this->communityImportRepository->findOneBy(array('id' => 1)) == null) {
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
        if ($this->eventImportRepository->findBy(array('id' => 1)) == null) {
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
                            if ($link->getStatus() != 1 && $link->getEvent() != null) {
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
        set_time_limit(7200);
        //if ($this->userImportRepository->findBy(array('id' => 1)) == null) {
        // For Users we always do this because users are always in UserImport at this stage
        // See comments in importUserIfNotMigrate() for more infos
        $this->importUserIfNotMigrate();
        //}
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
        if ($this->relayPointImportRepository->findBy(array('id' => 1)) == null) {
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

    //If the databases for import is empty, import data community from csv in public/importcsv
    // 0 = community V2
    // 1 = community V1
    private function importCommunityIfNotMigrate()
    {
        if (($handle = fopen("../public/import/csv/community_id_corresp.csv", "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
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

    //If the databases for import is empty, import data Event from csv in public/importcsv
    // 0 = Event V2
    // 1 = Event V1
    private function importEventIfNotMigrate()
    {
        if (($handle = fopen("../public/import/csv/event_id_corresp.csv", "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
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
    //If the databases for import is empty, import data  Relay point from csv in public/importcsv
    // 0 = Relay point V2
    // 1 = Relay point  V1
    private function importRelayIfNotMigrate()
    {
        if (($handle = fopen("../public/import/csv/relay_point_id_corresp.csv", "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $relais = $this->relayPointRepository->find(intval($data[0]));
                if ($relais != null) {
                    $importRelay = new RelayPointImport();

                    $importRelay->setRelay($this->relayPointRepository->find(intval($data[0])));
                    $importRelay->setRelayExternalId($data[1]);
                    $importRelay->setStatus(0);

                    $this->entityManager->persist($importRelay);
                }
            }
            fclose($handle);
            $this->entityManager->flush();
        }
    }

    //If the databases for import is empty, import Users data from csv in public/importcsv
    // 0 = User V2
    // 1 = User V1
    private function importUserIfNotMigrate()
    {
        $this->entityManager->getConnection()->getConfiguration()->setSQLLogger(null);
        $conn = $this->entityManager->getConnection();

        if (($handle = fopen("../public/import/csv/user_id_corresp.csv", "r")) !== false) {
            $cpt = 0;
            $query = "";
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                $query .= "update user_import set user_external_id =".$data[1]." where user_id = ".$data[0].";";
                $cpt++;
                if ($cpt==50) {
                    $stmt = $conn->prepare($query);
                    $stmt->executeQuery();
                    $cpt = 0;
                    $query = "";
                }
            }

            if ($query!=="") {
                $stmt = $conn->prepare($query);
                $stmt->executeQuery();
                $cpt = 0;
                $query = "";
            }

            fclose($handle);
        }
    }
}
