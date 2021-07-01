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

namespace App\DataFixtures\Service;

use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Ressource\Ad;
use App\Carpool\Service\AdManager;
use App\Community\Entity\Community;
use App\Community\Entity\CommunityUser;
use App\Community\Service\CommunityManager;
use App\Event\Entity\Event;
use App\Geography\Service\GeoSearcher;
use App\User\Entity\User;
use App\Geography\Entity\Address;
use App\Image\Entity\Icon;
use App\Image\Repository\IconRepository;
use App\RelayPoint\Entity\RelayPointType;
use App\User\Service\UserManager;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Data fixtures manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class BasicFixturesManager
{
    const PRICE_KM = 0.06;              // km price
    const FULL_REGISTERED_USERS = 3;
    
    private $entityManager;
    private $userManager;
    private $geoSearcher;
    private $adManager;
    private $communityManager;
    private $iconRepository;
    private $fixturesBasic;

    /**
     * Constructor
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserManager $userManager,
        GeoSearcher $geoSearcher,
        AdManager $adManager,
        CommunityManager $communityManager,
        IconRepository $iconRepository,
        bool $fixturesBasic
    ) {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->geoSearcher = $geoSearcher;
        $this->adManager = $adManager;
        $this->communityManager = $communityManager;
        $this->iconRepository = $iconRepository;
        $this->fixturesBasic = $fixturesBasic;
    }

    /**
     * Clear the database : remove all non essential data
     *
     * @return void
     */
    public function clearBasicData()
    {
        $conn = $this->entityManager->getConnection();
        $sql = "SET FOREIGN_KEY_CHECKS = 0;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        if ($this->fixturesBasic) {
            echo "Clearing basic database... " . PHP_EOL;
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
            TRUNCATE `icon`;
            TRUNCATE `matching`;
            TRUNCATE `message`;
            TRUNCATE `notified`;
            TRUNCATE `payment_profile`;
            TRUNCATE `position`;
            TRUNCATE `proposal`;
            TRUNCATE `proposal_community`;
            TRUNCATE `push_token`;    
            TRUNCATE `recipient`;
            TRUNCATE `refresh_tokens`;
            TRUNCATE `relay_point_type`;
            TRUNCATE `relay_point`;
            TRUNCATE `relay_point_import`;
            TRUNCATE `review`;
            TRUNCATE `territory`;
            TRUNCATE `user`;
            TRUNCATE `user_auth_assignment`;
            TRUNCATE `user_import`;
            TRUNCATE `user_notification`;
            TRUNCATE `waypoint`;";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
        }

        $sql = "
        SET FOREIGN_KEY_CHECKS = 1;";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }

    /**
     * Create a user from an array
     *
     * @param array $tab    The array containing the user informations (model in ../Csv/Users/users.txt)
     * @return void
     */
    public function createUser(array $tab)
    {
        echo "Import user : " . $tab[1] . " " . $tab[2] . PHP_EOL;
        $user = new User();
        $user->setEmail($tab[0]);
        $user->setStatus(User::STATUS_ACTIVE);
        $user->setGender($tab[3]);
        $user->setBirthDate(new \DateTime($tab[4]));
        $user->setGivenName($tab[1]);
        $user->setFamilyName($tab[2]);
        $user->setTelephone($tab[5]);
        $user->setNewsSubscription($tab[9]);
        $user->setPassword(password_hash($tab[6], PASSWORD_BCRYPT));
        $user = $this->userManager->prepareUser($user);
        
        // add role if needed
        if ($tab[8] !== self::FULL_REGISTERED_USERS) {
            $user = $this->userManager->addAuthItem($user, $tab[8]);
        }

        $user = $this->userManager->createAlerts($user, false);
        $user->setValidatedDate(new \DateTime());
        $user->setPhoneValidatedDate(new \DateTime());
        $addresses = $this->geoSearcher->geoCode($tab[7]);
        if (count($addresses)>0) {
            /**
             * @var Address $homeAddress
             */
            $homeAddress = $addresses[0];
            $homeAddress->setHome(true);
            $this->entityManager->persist($homeAddress);
            $user->addAddress($homeAddress);
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Create an Ad from an array
     *
     * @param array $tab    The array containing the ad informations (model in ../Csv/Ads/ads.txt)
     * @return Ad|null
     */
    public function createAd(array $tab)
    {
        echo "Import ad for user " . $tab[0] . PHP_EOL;
        if ($user = $this->userManager->getUserByEmail($tab[0])) {
            $origin = $destination = null;
            $addressesOrigin = $this->geoSearcher->geoCode($tab[5]);
            if (count($addressesOrigin)>0) {
                $origin = new Waypoint();
                $origin->setPosition(0);
                $origin->setDestination(false);
                $origin->setAddress($addressesOrigin[0]);
            } else {
                echo "Wrong origin !" . PHP_EOL;
                return;
            }
            $addressesDestination = $this->geoSearcher->geoCode($tab[6]);
            if (count($addressesDestination)>0) {
                $destination = new Waypoint();
                $destination->setPosition(1);
                $destination->setDestination(true);
                $destination->setAddress($addressesDestination[0]);
            } else {
                echo "Wrong destination !" . PHP_EOL;
                return;
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
                $ad->setOutwardDate($this->getDateFromModifier($tab[7]));
                $ad->setOutwardTime($tab[8]);
            } else {
                $ad->setOutwardDate($this->getDateFromModifier($tab[7]));
                $ad->setOutwardLimitDate($this->getDateFromModifier($tab[9]));
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
                    $ad->setReturnDate($this->getDateFromModifier($tab[9]));
                    $ad->setReturnTime($tab[10]);
                } else {
                    $ad->setReturnDate($ad->getOutwardDate());
                    $ad->setReturnLimitDate($ad->getOutwardLimitDate());
                }
            }
            // we create the proposal and its related entities
            return $this->adManager->createProposalFromAd($ad);
        } else {
            echo "User not found !" . PHP_EOL;
            return null;
        }
    }

    /**
     * Create an event from an array
     *
     * @param array $tab    The array containing the event informations (model in ../Csv/Events/events.txt)
     * @return void
     */
    public function createEvent(array $tab)
    {
        echo "Import event : " . $tab[2] . PHP_EOL;
        if ($user = $this->userManager->getUserByEmail($tab[0])) {
            $event = new Event();
            $event->setStatus(Event::STATUS_ACTIVE);
            $event->setUser($user);
            $addresses = $this->geoSearcher->geoCode($tab[1]);
            if (count($addresses)>0) {
                /**
                 * @var Address $address
                 */
                $address = $addresses[0];
                $this->entityManager->persist($address);
                $event->setAddress($address);
            } else {
                echo "Address not found !" . PHP_EOL;
                return;
            }
            $event->setName($tab[2]);
            $event->setDescription($tab[3]);
            $event->setFullDescription($tab[4]);
            $event->setFromDate(DateTime::createFromFormat("Y-m-d H:i", $tab[5]));
            $event->setToDate(DateTime::createFromFormat("Y-m-d H:i", $tab[6]));
            $event->setUseTime($tab[7] === "1");
            $event->setUrl($tab[8]);
            $event->setPrivate($tab[9] === "1");
            $this->entityManager->persist($event);
            $this->entityManager->flush();
        } else {
            echo "User not found !" . PHP_EOL;
        }
    }

    /**
     * Create a community from an array
     *
     * @param array $tab    The array containing the community informations (model in ../Csv/Communities/communities.txt)
     * @return void
     */
    public function createCommunity(array $tab)
    {
        echo "Import community : " . $tab[2] . PHP_EOL;
        if ($user = $this->userManager->getUserByEmail($tab[0])) {
            $community = new Community();
            $community->setStatus(1);
            $community->setUser($user);
            if ($tab[1] !== "") {
                $addresses = $this->geoSearcher->geoCode($tab[1]);
                if (count($addresses)>0) {
                    /**
                     * @var Address $address
                     */
                    $address = $addresses[0];
                    $this->entityManager->persist($address);
                    $community->setAddress($address);
                } else {
                    echo "Address not found !" . PHP_EOL;
                }
            }
            $community->setName($tab[2]);
            $community->setDescription($tab[3]);
            $community->setFullDescription($tab[4]);
            $community->setMembersHidden($tab[5] === "1");
            $community->setProposalsHidden($tab[6] === "1");
            $community->setValidationType($tab[7]);
            $community->setDomain($tab[8]);
            // we use the save method from communityManager to add the right role to the creator
            $this->communityManager->save($community);
        } else {
            echo "User not found !" . PHP_EOL;
        }
    }

    /**
     * Create a community user from an array
     *
     * @param array $tab    The array containing the community user informations (model in ../Csv/CommunityUsers/communityUsers.txt)
     * @return void
     */
    public function createCommunityUser(array $tab)
    {
        echo "Import user " . $tab[0] . " in community : " . $tab[1] . PHP_EOL;
        if ($user = $this->userManager->getUserByEmail($tab[0])) {
            if ($community = $this->communityManager->exists($tab[1])) {
                $communityUser = new CommunityUser();
                $communityUser->setUser($user);
                $communityUser->setCommunity($community[0]);
                $communityUser->setStatus($tab[2]);
                $this->entityManager->persist($communityUser);
                $this->entityManager->flush();
            } else {
                echo "Community not found !" . PHP_EOL;
            }
        } else {
            echo "User not found !" . PHP_EOL;
        }
    }

    /**
     * Create territories (direct SQL request because of geographical data)
     *
     * @param string $sqlRequest    The sql request for this territory
     * @return void
     */
    public function createTerritories(string $sqlRequest)
    {
        echo "Import a territory" . PHP_EOL;
        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare($sqlRequest);
        $stmt->execute();
    }

    /**
     * Return the current date with the applied time modifier;
     *
     * @param string $modifier  The modifier
     * @return DateTime
     */
    private function getDateFromModifier(string $modifier)
    {
        $date = new DateTime();
        switch ($modifier[0]) {
            case '+': return $date->add(new DateInterval(substr($modifier, 1)));
            case '-': return $date->sub(new DateInterval(substr($modifier, 1)));
        }
        return $date;
    }

    /**
     * Create the icons
     *
     * @param array $tab    The array containing the icons (model in ../Csv/Basic/Icons/icons.txt)
     * @return void
     */
    public function createIcons(array $tab)
    {
        echo "Import icon " . $tab[0] . " - " . $tab[2] . PHP_EOL;
        $icon = new Icon();
        $icon->setId($tab[0]);
        if ($tab[1] !== "") {
            $linkedIcon = $this->iconRepository->find($tab[1]);
            if (!is_null($linkedIcon)) {
                $icon->setPrivateIconLinked($linkedIcon);
            } else {
                echo "Private icon linked not found : ".$tab[1]." !" . PHP_EOL;
            }
        }
        $icon->setName($tab[2]);
        $icon->setFileName($tab[3]);
        $this->entityManager->persist($icon);
        $this->entityManager->flush();
    }

    /**
     * Create the RelayPointTypes
     *
     * @param array $tab    The array containing the RelayPointTypes (model in ../Csv/Basic/RelayPointTypes/relayPointTypes.txt)
     * @return void
     */
    public function createRelayPointTypes(array $tab)
    {
        echo "Import relayPointType " . $tab[0] . " - " . $tab[1] . PHP_EOL;
        $relayPointType = new RelayPointType();
        $relayPointType->setId($tab[0]);
        $relayPointType->setName($tab[1]);
        if ($tab[2] !== "") {
            $icon = $this->iconRepository->find($tab[2]);
            if (!is_null($icon)) {
                $relayPointType->setIcon($icon);
            } else {
                echo "Private icon linked not found : ".$tab[2]." !" . PHP_EOL;
            }
        }
        $this->entityManager->persist($relayPointType);
        $this->entityManager->flush();
    }
}
