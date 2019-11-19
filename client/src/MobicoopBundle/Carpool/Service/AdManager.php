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

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Service;

use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Criteria;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;

/**
 * Ad management service.
 */
class AdManager
{
    private $dataProvider;
    
    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Ad::class);
    }

    /**
     * Get an ad and its results
     *
     * @param int $id The ad id
     * @return void
     */
    public function getAd(int $id)
    {
        if ($data = $this->dataProvider->getItem($id)) {
            return $data->getValue();
        }
        return null;
    }

    /**
     * Create an ad. The ad can be a search.
     *
     * @param array $data   The data posted by the user
     * @param User|null $poster  The poster of the ad
     * @return Ad
     */
    public function createAd(array $data, ?User $poster=null)
    {
        $ad = new Ad();

        // the ad is a search ?
        if (isset($data['search']) && $data['search']) {
            $ad->setSearch(true);
        }

        // role
        $ad->setRole($data['driver'] ? ($data['passenger'] ? Ad::ROLE_DRIVER_OR_PASSENGER : ad::ROLE_DRIVER) : Ad::ROLE_PASSENGER);

        // oneway ?
        if (isset($data['oneway']) && $data['oneway']) {
            $ad->setOneWay(true);
        }

        // frequency
        $ad->setFrequency($data['regular'] ? Criteria::FREQUENCY_REGULAR : Criteria::FREQUENCY_PUNCTUAL);

        // outward waypoints
        $outwardsWaypoints[] = $data['origin'];
        foreach ($data['waypoints'] as $waypoint) {
            if ($waypoint['visible']) {
                $outwardsWaypoints[] = $waypoint;
            }
        }
        $outwardsWaypoints[] = $data['destination'];
        $ad->setOutwardWaypoints($outwardsWaypoints);

        // date and time
        if ($ad->getFrequency() == Criteria::FREQUENCY_REGULAR) {
            if (isset($data['fromDate'])) {
                $ad->setOutwardDate(\DateTime::createFromFormat('Y-m-d', $data['fromDate']));
            } else {
                $ad->setOutwardDate(new \Datetime());
            }
            if (isset($data['toDate'])) {
                $ad->setOutwardLimitdate(\DateTime::createFromFormat('Y-m-d', $data['toDate']));
            }
            $ad->setSchedule($data['schedules']);
        } else {
            $ad->setOutwardDate(\DateTime::createFromFormat('Y-m-d', $data['outwardDate']));
            $ad->setOutwardTime($data['outwardTime'] ? \DateTime::createFromFormat('H:i', $data['outwardTime']): null);
        }

        if (isset($data["strictDate"])) {
            $ad->setStrictDate($data["strictDate"]);
        }
        if (isset($data["strictPunctual"])) {
            $ad->setStrictPunctual($data["strictPunctual"]);
        }
        if (isset($data["strictRegular"])) {
            $ad->setStrictRegular($data["strictRegular"]);
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
        $ad->setSeats($data['seats']);

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
            $ad->setSolidary($data['solidaryExclusive']);
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

        // we check if the ad is posted for another user (delegation)
        if (isset($data['user'])) {
            $ad->setUserId($data['user']);
            $ad->setPosterId($poster->getId());
        } elseif ($poster) {
            $ad->setUserId($poster->getId());
        }

        // communities
        if (isset($data['communities'])) {
            $ad->setCommunities($data['communities']);
        }

        // event
        if (isset($data['eventId'])) {
            $ad->setEventId($data['eventId']);
        }
        
        // creation of the ad
        $response = $this->dataProvider->post($ad);
        if ($response->getCode() != 201) {
            return $response->getValue();
        }

        return $response->getValue();
    }

    /**
     * Get all results for a search.
     *
     * @param array $origin               The origin address
     * @param array $destination          The destination address
     * @param \Datetime $date               The date and time in a Datetime object
     * @param int $frequency                The frequency of the trip
     * @param integer $regularLifeTime      The lifetime of a regular trip in years
     * @param boolean|null $strictDate      Strict date
     * @param boolean|null $useTime         Use the time part of the date
     * @param boolean $strictPunctual       Strictly punctual
     * @param boolean $strictRegular        Strictly regular
     * @param integer $role                 Role (driver and/or passenger)
     * @param integer $userId               User id of the requester (to exclude its own results)
     * @param integer $communityId          Community id of the requester (to get only results from that community)
     * @param $format                       Return format
     * @return array|null The matchings found or null if not found.
     */
    public function getResultsForSearch(
        array $origin,
        array $destination,
        \Datetime $date,
        ?\Datetime $time,
        int $frequency,
        ?bool $strictDate = null,
        ?bool $strictPunctual = null,
        ?bool $strictRegular = null,
        ?int $role = null,
        ?int $userId = null,
        ?int $communityId = null
    ) {
        // we set the params
        $params = [
            "origin" => $origin,
            "destination" => $destination,
            "waypoints" => [],
            "date" => $date->format('Y-m-d\TH:i:s\Z'),
            "regular" => $frequency == Criteria::FREQUENCY_REGULAR ? Criteria::FREQUENCY_REGULAR : Criteria::FREQUENCY_PUNCTUAL,
            "search" => true,
            "seats" => 1
        ];
        if (!is_null($strictDate)) {
            $params["strictDate"] = $strictDate;
        }
        if (!is_null($time)) {
            $params["outwardTime"] = $time->format('H:i');
        }
        if (!is_null($strictPunctual)) {
            $params["strictPunctual"] = $strictPunctual;
        }
        if (!is_null($strictRegular)) {
            $params["strictRegular"] = $strictRegular;
        }
        if (!is_null($role)) {
            $params["driver"] = $role == 1 || $role == 3;
            $params["passenger"] = $role == 2 || $role == 3;
        }
        if (!is_null($userId)) {
            $params["userId"] = $userId;
        }
        if (!is_null($communityId)) {
            $params["communityId"] = $communityId;
        }
        // for regular search, we set every day as a possible carpooling day
        $params["schedules"] = [[
            'mon' => true,
            'tue' => true,
            'wed' => true,
            'thu' => true,
            'fri' => true,
            'sat' => true,
            'sun' => true
        ]];

        return $this->createAd($params);
    }
}
