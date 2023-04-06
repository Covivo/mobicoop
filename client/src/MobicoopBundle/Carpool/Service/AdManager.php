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
 */

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Symfony\Component\Security\Core\Security;

/**
 * Ad management service.
 */
class AdManager
{
    private $dataProvider;

    private $security;

    /**
     * Constructor.
     *
     * @throws \ReflectionException
     */
    public function __construct(DataProvider $dataProvider, Security $security)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Ad::class, Ad::RESOURCE_NAME);
        $this->dataProvider->setFormat(DataProvider::RETURN_OBJECT);
        $this->security = $security;
    }

    /**
     * Get an ad and its results.
     *
     * @param int $id The ad id
     * @param null|array    The filters to apply to the results
     *
     * @return null|Ad
     */
    public function getAd(int $id, ?array $filters = null)
    {
        if ($data = $this->dataProvider->getItem($id, $filters)) {
            return $data->getValue();
        }

        return null;
    }

    /**
     * Get an ad and its results from its external id.
     *
     * @param string $id The ad external id
     * @param null|array    The filters to apply to the results
     *
     * @return null|Ad
     */
    public function getAdFromExternalId(string $id, ?array $filters = null)
    {
        if ($data = $this->dataProvider->getSpecialItem($id, 'external', $filters)) {
            return $data->getValue();
        }

        return null;
    }

    /**
     * Get full ad data.
     *
     * @return null|Ad
     */
    public function getFullAd(int $id)
    {
        if ($data = $this->dataProvider->getSpecialItem($id, 'full')) {
            return $data->getValue();
        }

        return null;
    }

    /**
     * Claim an Ad (useful for login or register after an anonymous search).
     *
     * @param int $id The Ad id to claim
     *
     * @return bool Result of the claim
     */
    public function claimAd(int $id)
    {
        $ad = new Ad($id);
        if ($response = $this->dataProvider->putSpecial($ad, null, 'claim')) {
            return 200 == $response->getCode();
        }

        return false;
    }

    /**
     * Create an ad. The ad can be a search.
     *
     * @param array $data The data used to create the ad
     *
     * @return Ad
     */
    public function createAd(array $data)
    {
        $ad = $this->mapAd($data);
        // creation of the ad
        $response = $this->dataProvider->post($ad);
        if (201 != $response->getCode()) {
            return $response->getValue();
        }

        return $response->getValue();
    }

    /**
     * Get all results for a search.
     *
     * @param array      $origin         The origin address
     * @param array      $destination    The destination address
     * @param \Datetime  $date           The date in a Datetime object
     * @param \Datetime  $time           The time in a Datetime object
     * @param bool       $regular        The trip is regular
     * @param null|bool  $strictDate     Strict date
     * @param bool       $strictPunctual Strictly punctual
     * @param bool       $strictRegular  Strictly regular
     * @param null|int   $role           Role (driver and/or passenger)
     * @param null|int   $userId         User id of the requester (to exclude its own results)
     * @param int        $communityId    Community id of the requester (to get only results from that community)
     * @param null|array $filters        Filters and order choices
     *
     * @return null|Ad the matchings found or null if not found
     */
    public function getResultsForSearch(
        array $origin,
        array $destination,
        ?\Datetime $date,
        ?\Datetime $time,
        bool $regular,
        ?bool $strictDate = null,
        ?bool $strictPunctual = null,
        ?bool $strictRegular = null,
        ?int $role = null,
        ?int $userId = null,
        ?int $communityId = null,
        ?array $filters = null
    ) {
        // we set the params
        $params = [
            'origin' => $origin,
            'destination' => $destination,
            'waypoints' => [],
            'outwardDate' => $date,
            'regular' => $regular,
            'search' => true,
            'communities' => [],
        ];
        if (!is_null($strictDate)) {
            $params['strictDate'] = $strictDate;
        }
        if (!is_null($time)) {
            $params['outwardTime'] = $time;
        }
        if (!is_null($strictPunctual)) {
            $params['strictPunctual'] = $strictPunctual;
        }
        if (!is_null($strictRegular)) {
            $params['strictRegular'] = $strictRegular;
        }
        if (!is_null($role)) {
            $params['driver'] = 1 == $role || 3 == $role;
            $params['passenger'] = 2 == $role || 3 == $role;
        }
        if (!is_null($userId)) {
            $params['userId'] = $userId;
        }
        if (!is_null($communityId)) {
            $params['communities'] = [$communityId];
        }
        if (!is_null($filters)) {
            $params['filters'] = $filters;
        }

        return $this->createAd($params);
    }

    /**
     * Create an ask from an ad result.
     *
     * @param array     $data   The data used to create the ask
     * @param null|bool $formal If the ask is formal
     *
     * @return ad
     */
    public function createAsk(array $params, ?bool $formal = false)
    {
        if (!isset($params['regular']) || !isset($params['adId']) || !isset($params['matchingId'])) {
            return null;
        }

        $ad = new Ad();

        // role
        $ad->setRole($params['driver'] ? ($params['passenger'] ? Ad::ROLE_DRIVER_OR_PASSENGER : Ad::ROLE_DRIVER) : Ad::ROLE_PASSENGER);

        // ad
        $ad->setAdId($params['adId']);

        // matching
        $ad->setMatchingId($params['matchingId']);

        if ($params['regular'] && $formal) {
            // formal regular ask, we have to transform the outward and return schedule to a unique schedule array
            if (!isset($params['outwardSchedule']) && !isset($params['returnSchedule'])) {
                return null;
            }
            $schedule = [];
            $days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
            foreach ($days as $day) {
                if (isset($params['outwardSchedule'][$day.'Time']) && !is_null($params['outwardSchedule'][$day.'Time'])) {
                    if (isset($params['returnSchedule'][$day.'Time']) && !is_null($params['returnSchedule'][$day.'Time'])) {
                        $schedule[] = [
                            'outwardTime' => $params['outwardSchedule'][$day.'Time'],
                            'returnTime' => $params['returnSchedule'][$day.'Time'],
                            $day => true,
                        ];
                    } else {
                        $schedule[] = [
                            'outwardTime' => $params['outwardSchedule'][$day.'Time'],
                            'returnTime' => '',
                            $day => true,
                        ];
                    }
                } elseif (isset($params['returnSchedule'][$day.'Time']) && !is_null($params['returnSchedule'][$day.'Time'])) {
                    $schedule[] = [
                        'outwardTime' => '',
                        'returnTime' => $params['returnSchedule'][$day.'Time'],
                        $day => true,
                    ];
                }
            }
            $ad->setSchedule($schedule);
            $ad->setOutwardDate(\DateTime::createFromFormat('Y-m-d', $params['fromDate']));
            $ad->setOutwardLimitdate(\DateTime::createFromFormat('Y-m-d', $params['toDate']));
        } elseif (!$params['regular'] && $params['time']) {
            $ad->setOutwardTime($params['time']);
        }

        // creation of the ad ask
        if ($formal) {
            $response = $this->dataProvider->post($ad, 'ask');
        } else {
            $response = $this->dataProvider->post($ad, 'contact');
        }

        return $response->getValue();
    }

    /**
     * Get an ad and its results by its related Ask.
     *
     * @param int $askId  Id of the related Ask
     * @param int $userId The user that make the request
     *
     * @return null|Ad
     */
    public function getAdAsk(int $askId, int $userId)
    {
        if ($data = $this->dataProvider->getSpecialItem($askId, 'ask', ['userId' => $userId], true)) {
            return $data->getValue();
        }

        return null;
    }

    /**
     * Update an Ask via the Ad.
     *
     * @param int $ad     The ad to update
     * @param int $userId The user that make the request
     *
     * @return null|Ad
     */
    public function updateAdAsk(Ad $ad, int $userId)
    {
        if ($data = $this->dataProvider->putSpecial($ad, null, 'ask', ['userId' => $userId], true)) {
            return $data->getValue();
        }

        return null;
    }

    /**
     * Update an Ad.
     *
     * @param null|Ad $ad - the current ad before update
     *
     * @throws \Exception
     *
     * @return array|object
     */
    public function updateAd(array $data, Ad $ad = null)
    {
        $ad = $this->mapAd($data, $ad);
        if ($data = $this->dataProvider->put($ad, null, ['mail_search_link' => $data['mailSearchLink']])) {
            return $data->getValue();
        }

        return null;
    }

    /**
     * Delete an Ad.
     *
     * @param int   $id   The id of the ad to delete
     * @param array $data
     *
     * @return bool the result of the deletion
     */
    public function deleteAd(int $id, ?array $data)
    {
        if ($this->dataProvider->delete($id, $data)) {
            return 'success';
        }

        return null;
    }

    /**
     * Map data json array to and Ad.
     *
     * @param Ad $ad - the current Ad before update
     *
     * @throws \Exception
     */
    public function mapAd(array $data, Ad $ad = null): Ad
    {
        if (is_null($ad)) {
            $ad = new Ad();
        }

        /**
         * @var User $poster
         */
        $poster = $this->security->getUser();

        if (!is_null($poster) && isset($data['userDelegated']) && $data['userDelegated'] != $poster->getId()) {
            $data['userId'] = $data['userDelegated'];
            $data['posterId'] = $poster->getId();
        } elseif (!is_null($poster)) {
            $data['userId'] = $poster->getId();
        }
        if (!isset($data['outwardDate']) || '' == $data['outwardDate']) {
            //$data['outwardDate'] = new \DateTime();
        } elseif (is_string($data['outwardDate'])) {
            $data['outwardDate'] = \DateTime::createFromFormat('Y-m-d', $data['outwardDate']);
        }
        if (isset($data['returnDate']) && is_string($data['returnDate']) && '' != $data['returnDate']) {
            $data['returnDate'] = \DateTime::createFromFormat('Y-m-d', $data['returnDate']);
            $ad->setOneWay(false); // only for punctual journey
        } else {
            if (!isset($data['regular']) || !$data['regular']) {
                $ad->setOneWay(true); // only for punctual journey
            }
        }

        // one-way for regular
        if (isset($data['regular']) && $data['regular'] && isset($data['schedules'])) {
            $ad->setOneWay(true);
            foreach ($data['schedules'] as $schedule) {
                if (isset($schedule['returnTime'])) {
                    $ad->setOneWay(false);
                }
            }
        }
        if (isset($data['id'])) {
            $ad->setId($data['id']);
            $ad->setAdId($data['id']);
            $ad->setProposalId($data['id']);
        }

        if (isset($data['paused'])) {
            $ad->setPaused($data['paused']);
        }

        // the ad is a search ?
        if (isset($data['search'])) {
            $ad->setSearch($data['search']);
        }

        // role
        if (isset($data['driver']) || isset($data['passenger'])) {
            $ad->setRole(isset($data['driver']) && $data['driver']
                ? (isset($data['passenger']) && $data['passenger']
                    ? Ad::ROLE_DRIVER_OR_PASSENGER
                    : Ad::ROLE_DRIVER)
                : Ad::ROLE_PASSENGER);
        }

        // frequency
        if (isset($data['regular'])) {
            $ad->setFrequency($data['regular'] ? Ad::FREQUENCY_REGULAR : Ad::FREQUENCY_PUNCTUAL);
        }

        // outward waypoints
        if (isset($data['origin'], $data['waypoints'])) {
            $outwardWaypoints[] = $data['origin'];
            $returnWaypoints = null;
            foreach ($data['waypoints'] as $waypoint) {
                if ($waypoint['visible']) {
                    $outwardWaypoints[] = $waypoint['address'];
                }
            }
            $outwardWaypoints[] = $data['destination'];

            if (!$ad->isOneWay()) {
                $returnWaypoints = array_reverse($outwardWaypoints, false);
            }
            $ad->setOutwardWaypoints($outwardWaypoints);
            $ad->setReturnWaypoints($returnWaypoints);
        }

        // date and time
        if (Ad::FREQUENCY_REGULAR == $ad->getFrequency()) {
            if (isset($data['fromDate'])) {
                $ad->setOutwardDate(\DateTime::createFromFormat('Y-m-d', $data['fromDate']));
            } else {
                $ad->setOutwardDate(new \Datetime());
            }
            if (isset($data['toDate'])) {
                $ad->setOutwardLimitdate(\DateTime::createFromFormat('Y-m-d', $data['toDate']));
            }
            if (isset($data['schedules'])) {
                $ad->setSchedule($data['schedules']);
            }
        } elseif (isset($data['outwardDate'])) {
            $ad->setOutwardDate($data['outwardDate']);
            $ad->setOutwardTime(isset($data['outwardTime']) ? $data['outwardTime']->format('H:i') : null);
            if (isset($data['returnDate'], $data['returnTime'])) {
                $ad->setOneWay(false);
                $ad->setReturnDate($data['returnDate']);
                $ad->setReturnTime($data['returnTime']);
            }
        }

        if (isset($data['strictDate'])) {
            $ad->setStrictDate($data['strictDate']);
        }
        if (isset($data['strictPunctual'])) {
            $ad->setStrictPunctual($data['strictPunctual']);
        }
        if (isset($data['strictRegular'])) {
            $ad->setStrictRegular($data['strictRegular']);
        }

        // prices
        if (isset($data['priceKm'])) {
            $ad->setPriceKm($data['priceKm']);
        }
        if (isset($data['outwardDriverPrice'])) {
            $ad->setOutwardDriverPrice($data['outwardDriverPrice']);
        }
        if (isset($data['returnDriverPrice'])) {
            $ad->setReturnDriverPrice($data['returnDriverPrice']);
        }
        if (isset($data['outwardPassengerPrice'])) {
            $ad->setOutwardPassengerPrice($data['outwardPassengerPrice']);
        }
        if (isset($data['returnPassengerPrice'])) {
            $ad->setReturnPassengerPrice($data['returnPassengerPrice']);
        }

        // seats
        if (isset($data['seatsDriver'])) {
            $ad->setSeatsDriver($data['seatsDriver']);
        }
        if (isset($data['seatsPassenger'])) {
            $ad->setSeatsPassenger($data['seatsPassenger']);
        }

        // luggage
        if (isset($data['luggage'])) {
            $ad->setLuggage($data['luggage']);
        }

        // bike
        if (isset($data['bike'])) {
            $ad->setBike($data['bike']);
        }

        // backseats
        if (isset($data['backSeats'])) {
            $ad->setBackSeats($data['backSeats']);
        }
        // solidary
        if (isset($data['solidary'])) {
            $ad->setSolidary($data['solidary']);
        }

        // solidary exclusive
        if (isset($data['solidaryExclusive'])) {
            $ad->setSolidaryExclusive($data['solidaryExclusive']);
        }

        // avoid motorway
        if (isset($data['avoidMotorway'])) {
            $ad->setSolidary($data['avoidMotorway']);
        }

        // avoid toll
        if (isset($data['avoidToll'])) {
            $ad->setSolidary($data['avoidToll']);
        }

        // message
        if (isset($data['message'])) {
            $ad->setComment($data['message']);
        }

        // user
        if (isset($data['userId'])) {
            $ad->setUserId($data['userId']);
        }
        // we check if the ad is posted for another user (delegation)
        if (isset($data['posterId'])) {
            $ad->setPosterId($data['posterId']);
        }

        // communities
        if (isset($data['communities'])) {
            $ad->setCommunities($data['communities']);
        }

        // event
        if (
            (isset($data['origin']['event']['id']) && null != $data['origin']['event']['id'])
            || (isset($data['destination']['event']['id']) && null != $data['destination']['event']['id'])
            ) {
            if (isset($data['origin']['event']['id']) && null != $data['origin']['event']['id']) {
                $eventId = $data['origin']['event']['id'];
            } elseif (isset($data['destination']['event']['id']) && null != $data['destination']['event']['id']) {
                $eventId = $data['destination']['event']['id'];
            }
            $ad->setEventId($eventId);
        }
        if (isset($data['eventId'])) {
            $ad->setEventId($data['eventId']);
        }

        // filters
        if (isset($data['filters'])) {
            $ad->setFilters($data['filters']);
        }

        if (isset($data['cancellationMessage'])) {
            $ad->setCancellationMessage($data['cancellationMessage']);
        }

        if (isset($data['deletionMessage'])) {
            $ad->setDeletionMessage($data['deletionMessage']);
        }

        if (isset($data['deleterId'])) {
            $ad->setDeleterId($data['deleterId']);
        }

        return $ad;
    }

    public function cleanOrphanUserProposals()
    {
        $ad = new Ad();
        if ($response = $this->dataProvider->post($ad, 'cleanOrphans')) {
            return $response->getValue();
        }

        return null;
    }
}
