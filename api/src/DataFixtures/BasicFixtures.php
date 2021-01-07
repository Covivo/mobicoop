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

namespace App\DataFixtures;

use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Ressource\Ad;
use App\Carpool\Service\AdManager;
use App\Carpool\Service\ProposalManager;
use App\Geography\Service\GeoSearcher;
use App\User\Entity\User;
use App\Geography\Entity\Address;
use App\User\Service\UserManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Symfony\Component\Finder\Finder;

class BasicFixtures extends Fixture implements FixtureGroupInterface
{
    const PRICE_KM = 0.06;              // km price
    const BATCH = 50;                   // batch number for multi treatment

    private $userManager;
    private $geoSearcher;
    private $adManager;
    private $proposalManager;

    public function __construct(UserManager $userManager, GeoSearcher $geoSearcher, AdManager $adManager, ProposalManager $proposalManager)
    {
        $this->userManager = $userManager;
        $this->geoSearcher = $geoSearcher;
        $this->adManager = $adManager;
        $this->proposalManager = $proposalManager;
    }


    public function load(ObjectManager $manager)
    {
        // clear database
        echo "Clearing database... " . PHP_EOL;
        $conn = $manager->getConnection();
        $sql = "SET FOREIGN_KEY_CHECKS = 0;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $sql = "
        TRUNCATE `address`;
        TRUNCATE `address_territory`;
        TRUNCATE `ask`;
        TRUNCATE `ask_history`;
        TRUNCATE `block`;
        TRUNCATE `campaign`;
        TRUNCATE `car`;
        TRUNCATE `carpool_item`;
        TRUNCATE `carpool_payment`;
        TRUNCATE `carpool_payment_carpool_item`;
        TRUNCATE `carpool_proof`;
        TRUNCATE `community`;
        TRUNCATE `community_import`;
        TRUNCATE `community_security`;
        TRUNCATE `community_user`;
        TRUNCATE `criteria`;
        TRUNCATE `delivery`;
        TRUNCATE `diary`;
        TRUNCATE `direction`;
        TRUNCATE `direction_territory`;
        TRUNCATE `event`;
        TRUNCATE `event_import`;
        TRUNCATE `matching`;
        TRUNCATE `message`;
        TRUNCATE `notified`;
        TRUNCATE `operate`;
        TRUNCATE `payment_profile`;
        TRUNCATE `position`;
        TRUNCATE `proof`;
        TRUNCATE `proposal`;
        TRUNCATE `proposal_community`;
        TRUNCATE `push_token`;    
        TRUNCATE `recipient`;
        TRUNCATE `refresh_tokens`;
        TRUNCATE `relay_point`;
        TRUNCATE `relay_point_import`;
        TRUNCATE `review`;
        TRUNCATE `solidary`;
        TRUNCATE `solidary_ask`;
        TRUNCATE `solidary_ask_history`;
        TRUNCATE `solidary_matching`;
        TRUNCATE `solidary_need`;
        TRUNCATE `solidary_solution`;
        TRUNCATE `solidary_user`;
        TRUNCATE `solidary_user_need`;
        TRUNCATE `solidary_user_structure`;
        TRUNCATE `structure`;
        TRUNCATE `structure_need`;
        TRUNCATE `structure_proof`;
        TRUNCATE `structure_territory`;
        TRUNCATE `user`;
        TRUNCATE `user_auth_assignment`;
        TRUNCATE `user_import`;
        TRUNCATE `user_notification`;
        TRUNCATE `waypoint`;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $sql = "
        SET FOREIGN_KEY_CHECKS = 1;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        // load users info from csv file
        $finder = new Finder();
        $finder->in(__DIR__ . '/Csv/Basic/Users/');
        $finder->name('*.csv');
        $finder->files();
        foreach ($finder as $file) {
            echo "Importing : {$file->getBasename()} " . PHP_EOL;
            if ($file = fopen($file, "a+")) {
                while ($tab = fgetcsv($file, 4096, ';')) {
                    // create the user
                    echo "Import user : " . $tab[1] . " " . $tab[2] . PHP_EOL;
                    $user = new User();
                    $user->setId($tab[0]);
                    $user->setStatus(User::STATUS_ACTIVE);
                    $user->setGender($tab[4]);
                    $user->setBirthDate(new \DateTime($tab[5]));
                    $user->setGivenName($tab[1]);
                    $user->setFamilyName($tab[2]);
                    $user->setEmail($tab[3]);
                    $user->setTelephone($tab[6]);
                    $user->setPassword(password_hash($tab[7], PASSWORD_ARGON2I));
                    $user = $this->userManager->prepareUser($user);
                    $user = $this->userManager->createAlerts($user, false);
                    $user->setValidatedDate(new \DateTime());
                    $user->setPhoneValidatedDate(new \DateTime());
                    $addresses = $this->geoSearcher->geoCode($tab[8]);
                    if (count($addresses)>0) {
                        /**
                         * @var Address $homeAddress
                         */
                        $homeAddress = $addresses[0];
                        $homeAddress->setHome(true);
                        $manager->persist($homeAddress);
                        $user->addAddress($homeAddress);
                    }
                    $manager->persist($user);
                    $manager->flush();
                }
            }
        }

        // load ads info from csv file
        $finder = new Finder();
        $finder->in(__DIR__ . '/Csv/Basic/Ads/');
        $finder->name('*.csv');
        $finder->files();
        foreach ($finder as $file) {
            echo "Importing : {$file->getBasename()} " . PHP_EOL;
            if ($file = fopen($file, "a+")) {
                while ($tab = fgetcsv($file, 4096, ';')) {
                    // create the ad
                    echo "Import ad for user #" . $tab[0] . PHP_EOL;
                    if ($user = $this->userManager->getUser($tab[0])) {
                        $origin = $destination = null;
                        $addressesOrigin = $this->geoSearcher->geoCode($tab[5]);
                        if (count($addressesOrigin)>0) {
                            $origin = new Waypoint();
                            $origin->setPosition(0);
                            $origin->setDestination(false);
                            $origin->setAddress($addressesOrigin[0]);
                        } else {
                            echo "Wrong origin !" . PHP_EOL;
                            continue;
                        }
                        $addressesDestination = $this->geoSearcher->geoCode($tab[6]);
                        if (count($addressesDestination)>0) {
                            $destination = new Waypoint();
                            $destination->setPosition(1);
                            $destination->setDestination(true);
                            $destination->setAddress($addressesDestination[0]);
                        } else {
                            echo "Wrong destination !" . PHP_EOL;
                            continue;
                        }
                        $ad = new Ad();
                        $ad->setUser($user);
                        $ad->setUserId($user->getId());
                        $ad->setSearch($tab[1] == "1");
                        $ad->setOneWay($tab[2] == "1");
                        $ad->setFrequency($tab[3]);
                        $ad->setRole($tab[4]);
                        $ad->setPriceKm(self::PRICE_KM);
                        $ad->setOutwardDriverPrice(0);
                        
                        if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                            $ad->setOutwardDate(new \DateTime($tab[7]));
                            $ad->setOutwardTime($tab[8]);
                        } else {
                            $ad->setOutwardDate(new \DateTime($tab[7]));
                            $ad->setOutwardLimitDate(new \DateTime($tab[9]));
                            $schedules = [];
                            if ($tab[11] == "1") {
                                $schedules[] = [
                                    'mon' => 1,
                                    'outwardTime' => $tab[12],
                                    'returnTime' => $tab[13]
                                ];
                            }
                            if ($tab[14] == "1") {
                                $schedules[] = [
                                    'tue' => 1,
                                    'outwardTime' => $tab[15],
                                    'returnTime' => $tab[16]
                                ];
                            }
                            if ($tab[17] == "1") {
                                $schedules[] = [
                                    'wed' => 1,
                                    'outwardTime' => $tab[18],
                                    'returnTime' => $tab[19]
                                ];
                            }
                            if ($tab[20] == "1") {
                                $schedules[] = [
                                    'thu' => 1,
                                    'outwardTime' => $tab[21],
                                    'returnTime' => $tab[22]
                                ];
                            }
                            if ($tab[23] == "1") {
                                $schedules[] = [
                                    'fri' => 1,
                                    'outwardTime' => $tab[24],
                                    'returnTime' => $tab[25]
                                ];
                            }
                            if ($tab[26] == "1") {
                                $schedules[] = [
                                    'sat' => 1,
                                    'outwardTime' => $tab[27],
                                    'returnTime' => $tab[28]
                                ];
                            }
                            if ($tab[29] == "1") {
                                $schedules[] = [
                                    'sun' => 1,
                                    'outwardTime' => $tab[30],
                                    'returnTime' => $tab[31]
                                ];
                            }
                            $ad->setSchedule($schedules);
                        }
                        
                        $ad->setOutwardWaypoints([$origin->getAddress()->jsonSerialize(),$destination->getAddress()->jsonSerialize()]);

                        if (!$ad->isOneWay()) {
                            if ($ad->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                                $ad->setReturnDate(new \DateTime($tab[9]));
                                $ad->setReturnTime($tab[10]);
                            } else {
                                $ad->setReturnDate($ad->getOutwardDate());
                                $ad->setReturnLimitDate($ad->getOutwardLimitDate());
                            }
                        }
                        // we create the proposal and its related entities
                        $ad = $this->adManager->createProposalFromAd($ad);
                    }
                }
            }
        }
        echo "Creating directions and matchings... ";
        // we compute the directions and default values for the generated proposals
        $this->proposalManager->setDirectionsAndDefaultsForAllCriterias(self::BATCH);

        // we generate the matchings
        $this->proposalManager->createMatchingsForAllProposals();
        echo "Done !" . PHP_EOL;
    }

    public static function getGroups(): array
    {
        return ['basic'];
    }
}
