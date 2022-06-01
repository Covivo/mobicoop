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
 */

namespace App\DataFixtures\Service;

use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Waypoint;
use App\Carpool\Ressource\Ad;
use App\Carpool\Service\AdManager;
use App\Community\Entity\Community;
use App\Community\Entity\CommunityUser;
use App\Community\Service\CommunityManager;
use App\Event\Entity\Event;
use App\Event\Repository\EventRepository;
use App\Gamification\Repository\BadgeRepository;
use App\Geography\Entity\Address;
use App\Geography\Service\Geocoder\MobicoopGeocoder;
use App\Geography\Service\Point\AddressAdapter;
use App\Geography\Service\Point\MobicoopGeocoderPointProvider;
use App\Image\Entity\Icon;
use App\Image\Entity\Image;
use App\Image\Repository\IconRepository;
use App\Image\Service\ImageManager;
use App\MassCommunication\Repository\CampaignRepository;
use App\RelayPoint\Entity\RelayPointType;
use App\RelayPoint\Repository\RelayPointRepository;
use App\RelayPoint\Repository\RelayPointTypeRepository;
use App\User\Entity\User;
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
    private const PRICE_KM = 0.06;
    private const FULL_REGISTERED_USERS = 3;

    private const SOURCE_IMAGE_PATH = __DIR__.'/../File/Basic/Images/';
    private const DESTINATION_IMAGE_PATH = __DIR__.'/../../../public/upload/';
    private const DESTINATION_IMAGE_DIRECTORY_EVENT = 'events/images/';
    private const DESTINATION_IMAGE_DIRECTORY_COMMUNITY = 'communities/images/';
    private const DESTINATION_IMAGE_DIRECTORY_RELAY_POINT = 'relaypoints/images/';
    private const DESTINATION_IMAGE_DIRECTORY_RELAY_POINT_TYPE = 'relaypointtypes/images/';
    private const DESTINATION_IMAGE_DIRECTORY_USER = 'users/images/';
    private const DESTINATION_IMAGE_DIRECTORY_CAMPAIGN = 'masscomunication/images/';
    private const DESTINATION_IMAGE_DIRECTORY_BADGE = 'badges/images/';

    private $entityManager;
    private $userManager;
    private $pointProvider;
    private $adManager;
    private $communityManager;
    private $iconRepository;
    private $fixturesBasic;
    private $eventRepository;
    private $relayPointRepository;
    private $relayPointTypeRepository;
    private $campaignRepository;
    private $badgeRepository;
    private $imageManager;

    /**
     * Constructor.
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        UserManager $userManager,
        MobicoopGeocoder $mobicoopGeocoder,
        AdManager $adManager,
        CommunityManager $communityManager,
        IconRepository $iconRepository,
        EventRepository $eventRepository,
        RelayPointRepository $relayPointRepository,
        RelayPointTypeRepository $relayPointTypeRepository,
        CampaignRepository $campaignRepository,
        BadgeRepository $badgeRepository,
        ImageManager $imageManager,
        bool $fixturesBasic
    ) {
        $this->entityManager = $entityManager;
        $this->userManager = $userManager;
        $this->pointProvider = new MobicoopGeocoderPointProvider($mobicoopGeocoder);
        $this->adManager = $adManager;
        $this->communityManager = $communityManager;
        $this->iconRepository = $iconRepository;
        $this->fixturesBasic = $fixturesBasic;
        $this->eventRepository = $eventRepository;
        $this->relayPointRepository = $relayPointRepository;
        $this->relayPointTypeRepository = $relayPointTypeRepository;
        $this->campaignRepository = $campaignRepository;
        $this->badgeRepository = $badgeRepository;
        $this->imageManager = $imageManager;
    }

    /**
     * Clear the database : remove all non essential data.
     */
    public function clearBasicData()
    {
        $this->entityManager->getConnection()->beginTransaction();
        $conn = $this->entityManager->getConnection();
        $sql = 'SET FOREIGN_KEY_CHECKS = 0;';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();

        if ($this->fixturesBasic) {
            echo 'Clearing basic database... '.PHP_EOL;
            $sql = '
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
            TRUNCATE `image`;
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
            TRUNCATE `waypoint`;';
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery();
        }

        $sql = '
        SET FOREIGN_KEY_CHECKS = 1;';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();
        $this->entityManager->getConnection()->commit();
    }

    /**
     * Create a user from an array.
     *
     * @param array $tab The array containing the user informations (model in ../Csv/Users/users.txt)
     */
    public function createUser(array $tab)
    {
        echo 'Import user : '.$tab[1].' '.$tab[2].PHP_EOL;
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
        if (self::FULL_REGISTERED_USERS !== $tab[8]) {
            $user = $this->userManager->addAuthItem($user, $tab[8]);
        }

        $user = $this->userManager->createAlerts($user, false);
        $user->setValidatedDate(new \DateTime());
        $points = $this->pointProvider->search($tab[7]);
        if (count($points) > 0) {
            /**
             * @var Address $homeAddress
             */
            $homeAddress = AddressAdapter::pointToAddress($points[0]);
            $homeAddress->setHome(true);
            $this->entityManager->persist($homeAddress);
            $user->addAddress($homeAddress);
        }
        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    /**
     * Create an Ad from an array.
     *
     * @param array $tab The array containing the ad informations (model in ../Csv/Ads/ads.txt)
     */
    public function createAd(array $tab): ?Ad
    {
        echo 'Import ad for user '.$tab[0].PHP_EOL;
        if ($user = $this->userManager->getUserByEmail($tab[0])) {
            $origin = $destination = null;
            $pointsOrigin = $this->pointProvider->search($tab[5]);
            if (count($pointsOrigin) > 0) {
                $origin = new Waypoint();
                $origin->setPosition(0);
                $origin->setDestination(false);
                $origin->setAddress(AddressAdapter::pointToAddress($pointsOrigin[0]));
            } else {
                echo 'Wrong origin !'.PHP_EOL;

                return null;
            }
            $pointsDestination = $this->pointProvider->search($tab[6]);
            if (count($pointsDestination) > 0) {
                $destination = new Waypoint();
                $destination->setPosition(1);
                $destination->setDestination(true);
                $destination->setAddress(AddressAdapter::pointToAddress($pointsDestination[0]));
            } else {
                echo 'Wrong destination !'.PHP_EOL;

                return null;
            }
            $ad = new Ad();
            $ad->setUser($user);
            $ad->setUserId($user->getId());
            $ad->setSearch('1' == $tab[1]);
            $ad->setOneWay('1' == $tab[2]);
            $ad->setFrequency($tab[3]);
            $ad->setRole($tab[4]);
            $ad->setPriceKm(self::PRICE_KM);
            $ad->setOutwardDriverPrice(0);

            if (Criteria::FREQUENCY_PUNCTUAL == $ad->getFrequency()) {
                $ad->setOutwardDate($this->getDateFromModifier($tab[7]));
                $ad->setOutwardTime($tab[8]);
            } else {
                $ad->setOutwardDate($this->getDateFromModifier($tab[7]));
                $ad->setOutwardLimitDate($this->getDateFromModifier($tab[9]));
                $schedules = [];
                if ('1' == $tab[11]) {
                    $schedules[] = [
                        'mon' => 1,
                        'outwardTime' => $tab[12],
                        'returnTime' => $tab[13],
                    ];
                }
                if ('1' == $tab[14]) {
                    $schedules[] = [
                        'tue' => 1,
                        'outwardTime' => $tab[15],
                        'returnTime' => $tab[16],
                    ];
                }
                if ('1' == $tab[17]) {
                    $schedules[] = [
                        'wed' => 1,
                        'outwardTime' => $tab[18],
                        'returnTime' => $tab[19],
                    ];
                }
                if ('1' == $tab[20]) {
                    $schedules[] = [
                        'thu' => 1,
                        'outwardTime' => $tab[21],
                        'returnTime' => $tab[22],
                    ];
                }
                if ('1' == $tab[23]) {
                    $schedules[] = [
                        'fri' => 1,
                        'outwardTime' => $tab[24],
                        'returnTime' => $tab[25],
                    ];
                }
                if ('1' == $tab[26]) {
                    $schedules[] = [
                        'sat' => 1,
                        'outwardTime' => $tab[27],
                        'returnTime' => $tab[28],
                    ];
                }
                if ('1' == $tab[29]) {
                    $schedules[] = [
                        'sun' => 1,
                        'outwardTime' => $tab[30],
                        'returnTime' => $tab[31],
                    ];
                }
                $ad->setSchedule($schedules);
            }

            $ad->setOutwardWaypoints([$origin->getAddress()->jsonSerialize(), $destination->getAddress()->jsonSerialize()]);

            if (!$ad->isOneWay()) {
                if (Criteria::FREQUENCY_PUNCTUAL == $ad->getFrequency()) {
                    $ad->setReturnDate($this->getDateFromModifier($tab[9]));
                    $ad->setReturnTime($tab[10]);
                } else {
                    $ad->setReturnDate($ad->getOutwardDate());
                    $ad->setReturnLimitDate($ad->getOutwardLimitDate());
                }
            }
            // we create the proposal and its related entities
            return $this->adManager->createProposalFromAd($ad);
        }
        echo 'User not found !'.PHP_EOL;

        return null;
    }

    /**
     * Create an event from an array.
     *
     * @param array $tab The array containing the event informations (model in ../Csv/Events/events.txt)
     */
    public function createEvent(array $tab)
    {
        echo 'Import event : '.$tab[3].PHP_EOL;
        if ($user = $this->userManager->getUserByEmail($tab[1])) {
            $event = new Event();
            $event->setStatus(Event::STATUS_ACTIVE);
            $event->setUser($user);
            $points = $this->pointProvider->search($tab[2]);
            if (count($points) > 0) {
                /**
                 * @var Address $address
                 */
                $address = AddressAdapter::pointToAddress($points[0]);
                $this->entityManager->persist($address);
                $event->setAddress($address);
            } else {
                echo 'Address not found !'.PHP_EOL;

                return;
            }
            $event->setId($tab[0]);
            $event->setName($tab[3]);
            $event->setDescription($tab[4]);
            $event->setFullDescription($tab[5]);
            $event->setFromDate(DateTime::createFromFormat('Y-m-d H:i', $tab[6]));
            $event->setToDate(DateTime::createFromFormat('Y-m-d H:i', $tab[7]));
            $event->setUseTime('1' === $tab[8]);
            $event->setUrl($tab[9]);
            $event->setPrivate('1' === $tab[10]);
            $this->entityManager->persist($event);
            $this->entityManager->flush();
        } else {
            echo 'User not found !'.PHP_EOL;
        }
    }

    /**
     * Create a community from an array.
     *
     * @param array $tab The array containing the community informations (model in ../Csv/Communities/communities.txt)
     */
    public function createCommunity(array $tab)
    {
        echo 'Import community : '.$tab[2].PHP_EOL;
        if ($user = $this->userManager->getUserByEmail($tab[0])) {
            $community = new Community();
            $community->setStatus(1);
            $community->setUser($user);
            if ('' !== $tab[1]) {
                $points = $this->pointProvider->search($tab[1]);
                if (count($points) > 0) {
                    /**
                     * @var Address $address
                     */
                    $address = AddressAdapter::pointToAddress($points[0]);
                    $this->entityManager->persist($address);
                    $community->setAddress($address);
                } else {
                    echo 'Address not found !'.PHP_EOL;
                }
            }
            $community->setName($tab[2]);
            $community->setDescription($tab[3]);
            $community->setFullDescription($tab[4]);
            $community->setMembersHidden('1' === $tab[5]);
            $community->setProposalsHidden('1' === $tab[6]);
            $community->setValidationType($tab[7]);
            $community->setDomain($tab[8]);
            // we use the save method from communityManager to add the right role to the creator
            $this->communityManager->save($community);
        } else {
            echo 'User not found !'.PHP_EOL;
        }
    }

    /**
     * Create a community user from an array.
     *
     * @param array $tab The array containing the community user informations (model in ../Csv/CommunityUsers/communityUsers.txt)
     */
    public function createCommunityUser(array $tab)
    {
        echo 'Import user '.$tab[0].' in community : '.$tab[1].PHP_EOL;
        if ($user = $this->userManager->getUserByEmail($tab[0])) {
            if ($community = $this->communityManager->exists($tab[1])) {
                $communityUser = new CommunityUser();
                $communityUser->setUser($user);
                $communityUser->setCommunity($community);
                $communityUser->setStatus($tab[2]);
                $this->entityManager->persist($communityUser);
                $this->entityManager->flush();
            } else {
                echo 'Community not found !'.PHP_EOL;
            }
        } else {
            echo 'User not found !'.PHP_EOL;
        }
    }

    /**
     * Create territory (direct SQL request because of geographical data).
     *
     * @param string $sqlRequest The sql request for this territory
     */
    public function createTerritory(string $sqlRequest)
    {
        echo 'Import a territory'.PHP_EOL;
        $conn = $this->entityManager->getConnection();
        $stmt = $conn->prepare($sqlRequest);
        $stmt->executeQuery();
    }

    /**
     * Create the icons.
     *
     * @param array $tab The array containing the icons (model in ../Csv/Basic/Icons/icons.txt)
     */
    public function createIcons(array $tab)
    {
        echo 'Import icon '.$tab[0].' - '.$tab[2].PHP_EOL;
        $icon = new Icon();
        $icon->setId($tab[0]);
        if ('' !== $tab[1]) {
            $linkedIcon = $this->iconRepository->find($tab[1]);
            if (!is_null($linkedIcon)) {
                $icon->setPrivateIconLinked($linkedIcon);
            } else {
                echo 'Private icon linked not found : '.$tab[1].' !'.PHP_EOL;
            }
        }
        $icon->setName($tab[2]);
        $icon->setFileName($tab[3]);
        $this->entityManager->persist($icon);
        $this->entityManager->flush();
    }

    /**
     * Create a RelayPointType.
     *
     * @param array $tab The array containing the RelayPointType (model in ../Csv/Basic/RelayPointTypes/relayPointTypes.txt)
     */
    public function createRelayPointType(array $tab)
    {
        echo 'Import relayPointType '.$tab[0].' - '.$tab[1].PHP_EOL;
        $relayPointType = new RelayPointType();
        $relayPointType->setId($tab[0]);
        $relayPointType->setName($tab[1]);
        if ('' !== $tab[2]) {
            $icon = $this->iconRepository->find($tab[2]);
            if (!is_null($icon)) {
                $relayPointType->setIcon($icon);
            } else {
                echo 'Private icon linked not found : '.$tab[2].' !'.PHP_EOL;
            }
        }
        $this->entityManager->persist($relayPointType);
        $this->entityManager->flush();
    }

    /**
     * Create an Image.
     *
     * @param array $tab The array containing the Image (model in ../Csv/Basic/Images/images.txt)
     */
    public function createImage(array $tab)
    {
        echo 'Import Image '.$tab[0].PHP_EOL;
        $image = new Image();
        $owner = null;
        $destinationDirectory = '';
        $file = self::SOURCE_IMAGE_PATH.$tab[0];
        if (is_file($file)) {
            if ('' !== $tab[1]) {
                if ($owner = $this->eventRepository->find($tab[1])) {
                    $image->setEvent($owner);
                    $destinationDirectory = self::DESTINATION_IMAGE_DIRECTORY_EVENT;
                } else {
                    echo 'Event not found for image '.$tab[0];

                    return;
                }
            }
            if ('' !== $tab[2]) {
                if ($owner = $this->communityManager->getCommunity($tab[2])) {
                    $image->setCommunity($owner);
                    $destinationDirectory = self::DESTINATION_IMAGE_DIRECTORY_COMMUNITY;
                } else {
                    echo 'Community not found for image '.$tab[0];

                    return;
                }
            }
            if ('' !== $tab[3]) {
                if ($owner = $this->relayPointRepository->find($tab[3])) {
                    $image->setRelayPoint($owner);
                    $destinationDirectory = self::DESTINATION_IMAGE_DIRECTORY_RELAY_POINT;
                } else {
                    echo 'RelayPoint not found for image '.$tab[0];

                    return;
                }
            }
            if ('' !== $tab[4]) {
                if ($owner = $this->relayPointTypeRepository->find($tab[4])) {
                    $image->setRelayPointType($owner);
                    $destinationDirectory = self::DESTINATION_IMAGE_DIRECTORY_RELAY_POINT_TYPE;
                } else {
                    echo 'RelayPointType not found for image '.$tab[0];

                    return;
                }
            }
            if ('' !== $tab[5]) {
                if ($owner = $this->userManager->getUser($tab[5])) {
                    $image->setUser($owner);
                    ${$destinationDirectory} = self::DESTINATION_IMAGE_DIRECTORY_USER;
                } else {
                    echo 'User not found for image '.$tab[0];

                    return;
                }
            }
            if ('' !== $tab[6]) {
                if ($owner = $this->campaignRepository->find($tab[6])) {
                    $image->setCampaign($owner);
                    $destinationDirectory = self::DESTINATION_IMAGE_DIRECTORY_CAMPAIGN;
                } else {
                    echo 'Campaign not found for image '.$tab[0];

                    return;
                }
            }
            if ('' !== $tab[7]) {
                if ($owner = $this->badgeRepository->find($tab[7])) {
                    $image->setBadgeIcon($owner);
                    $destinationDirectory = self::DESTINATION_IMAGE_DIRECTORY_BADGE;
                } else {
                    echo 'Badge not found for image '.$tab[0];

                    return;
                }
            }
            if ('' !== $tab[8]) {
                if ($owner = $this->badgeRepository->find($tab[8])) {
                    $image->setBadgeDecoratedIcon($owner);
                    $destinationDirectory = self::DESTINATION_IMAGE_DIRECTORY_BADGE;
                } else {
                    echo 'Badge not found for image '.$tab[0];

                    return;
                }
            }
            if ('' !== $tab[9]) {
                if ($owner = $this->badgeRepository->find($tab[9])) {
                    $image->setBadgeImage($owner);
                    $destinationDirectory = self::DESTINATION_IMAGE_DIRECTORY_BADGE;
                } else {
                    echo 'Badge not found for image '.$tab[0];

                    return;
                }
            }
            if ('' !== $tab[10]) {
                if ($owner = $this->badgeRepository->find($tab[10])) {
                    $image->setBadgeImageLight($owner);
                    $destinationDirectory = self::DESTINATION_IMAGE_DIRECTORY_BADGE;
                } else {
                    echo 'Badge not found for image '.$tab[0];

                    return;
                }
            }

            if ($owner && '' !== $destinationDirectory) {
                $image->setName($owner->getName());
                $image->setOriginalName($tab[0]);
                $image->setFileName($this->imageManager->generateFilename($image));
                $image->setPosition(1);

                $infos = getimagesize($file);

                $image->setMimeType($infos['mime']);
                $image->setWidth($infos[0]);
                $image->setHeight($infos[1]);
                $image->setSize(filesize($file));

                if (!copy($file, self::DESTINATION_IMAGE_PATH.$destinationDirectory.$image->getFileName())) {
                    echo 'File copy failed !'.PHP_EOL;

                    return;
                }

                $this->entityManager->persist($image);
                $this->entityManager->flush();
            }
        } else {
            echo 'File '.$file.' not found !'.PHP_EOL;

            return;
        }
    }

    /**
     * Return the current date with the applied time modifier;.
     *
     * @param string $modifier The modifier
     */
    private function getDateFromModifier(string $modifier): DateTime
    {
        $date = new DateTime();

        switch ($modifier[0]) {
            case '+': return $date->add(new DateInterval(substr($modifier, 1)));

            case '-': return $date->sub(new DateInterval(substr($modifier, 1)));
        }

        return $date;
    }
}
