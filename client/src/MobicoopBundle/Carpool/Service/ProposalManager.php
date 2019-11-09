<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Matching;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Criteria;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Waypoint;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;

/**
 * Proposal management service.
 */
class ProposalManager
{
    private $dataProvider;
    private $userManager;
    private $marginTime;
    
    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider, UserManager $userManager, int $marginTime)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Proposal::class);
        $this->userManager = $userManager;
        $this->marginTime = $marginTime;
    }

    /**
     * Get all matchings for a search.
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
    public function getMatchingsForSearch(
        array $origin,
        array $destination,
        \Datetime $date,
        int $frequency,
        ?int $regularLifeTime = null,
        ?bool $strictDate = null,
        ?bool $useTime = null,
        ?bool $strictPunctual = null,
        ?bool $strictRegular = null,
        ?int $role = null,
        ?int $userId = null,
        ?int $communityId = null,
        $format = null
    ) {
        // we set the params
        $params = [
            "origin" => $origin,
            "destination" => $destination,
            "date" => $date->format('Y-m-d\TH:i:s\Z'),
            "frequency" => $frequency
        ];
        if (!is_null($regularLifeTime)) {
            $params["regularLifeTime"] = $regularLifeTime;
        }
        if (!is_null($strictDate)) {
            $params["strictDate"] = $strictDate;
        }
        if (!is_null($useTime)) {
            $params["useTime"] = $useTime;
        }
        if (!is_null($strictPunctual)) {
            $params["strictPunctual"] = $strictPunctual;
        }
        if (!is_null($strictRegular)) {
            $params["strictRegular"] = $strictRegular;
        }
        if (!is_null($role)) {
            $params["role"] = $role;
        }
        if (!is_null($userId)) {
            $params["userId"] = $userId;
        }
        if (!is_null($communityId)) {
            $params["communityId"] = $communityId;
        }
        if (is_null($format)) {
            $format = $this->dataProvider::RETURN_OBJECT;
        }
        $this->dataProvider->setFormat($format);
        // we call the special collection operation "search"
        $response = $this->dataProvider->getSpecialCollection("search", $params);
        return $response->getValue();
    }
    
    /**
     * Create a proposal from a search
     *
     * @param User $user
     * @param array $params     The params
     * @param bool $formalAsk   True if we have to create a formal ask
     * @return void
     */
    public function createProposalFromSearch(User $user, array $params, bool $formalAsk=false)
    {
        $data = [
            "formalAsk" => $formalAsk,
            "private" => true,
            "proposalId" => $params['proposalId'],
            "origin"=>$params['origin'],
            "destination"=>$params['destination'],
            "outwardDate" => isset($params['date']) ? \DateTime::createFromFormat(\DateTime::ISO8601, $params['date'])->format('Y-m-d') : (new \Datetime())->format('Y-m-d'),
            "outwardTime" => isset($params['time']) ? \DateTime::createFromFormat(\DateTime::ISO8601, $params['time'])->format('H:i') : null,
            "seats" => isset($params['seats']) ? $params['seats'] : 1,
            "driver" => $params['driver'],
            "passenger" => $params['passenger'],
            "priceKm" => isset($params['priceKm']) ? $params['priceKm'] : null,
            "outwardPrice" => isset($params['outwardPrice']) ? $params['outwardPrice'] : null,
            "outwardRoundedPrice" => isset($params['outwardRoundedPrice']) ? $params['outwardRoundedPrice'] : null,
            "outwardComputedPrice" => isset($params['outwardComputedPrice']) ? $params['outwardComputedPrice'] : null,
            "outwardComputedRoundedPrice" => isset($params['outwardComputedRoundedPrice']) ? $params['outwardComputedRoundedPrice'] : null,
            "returnPrice" => isset($params['returnPrice']) ? $params['returnPrice'] : null,
            "returnRoundedPrice" => isset($params['returnRoundedPrice']) ? $params['returnRoundedPrice'] : null,
            "returnComputedPrice" => isset($params['returnComputedPrice']) ? $params['returnComputedPrice'] : null,
            "returnComputedRoundedPrice" => isset($params['returnComputedRoundedPrice']) ? $params['returnComputedRoundedPrice'] : null,
            "regular" => $params['regular'],
            "waypoints" => []
        ];
        if (isset($params["fromDate"])) {
            $data["fromDate"] = $params['fromDate'];
        }
        if (isset($params["toDate"])) {
            $data['toDate'] = $params['toDate'];
        }
        if (isset($params["outwardSchedule"])) {
            $schedules = [];
            if (isset($params["outwardSchedule"]['monTime']) && !is_null($params["outwardSchedule"]['monTime'])) {
                $schedules['outwardMon']['outwardTime'] = $params["outwardSchedule"]['monTime'];
                $schedules['outwardMon']['returnTime'] = '';
                $schedules['outwardMon']['mon'] = true;
            }
            if (isset($params["outwardSchedule"]['tueTime']) && !is_null($params["outwardSchedule"]['tueTime'])) {
                $schedules['outwardTue']['outwardTime'] = $params["outwardSchedule"]['tueTime'];
                $schedules['outwardTue']['returnTime'] = '';
                $schedules['outwardTue']['tue'] = true;
            }
            if (isset($params["outwardSchedule"]['wedTime']) && !is_null($params["outwardSchedule"]['wedTime'])) {
                $schedules['outwardWed']['outwardTime'] = $params["outwardSchedule"]['wedTime'];
                $schedules['outwardWed']['returnTime'] = '';
                $schedules['outwardWed']['wed'] = true;
            }
            if (isset($params["outwardSchedule"]['thuTime']) && !is_null($params["outwardSchedule"]['thuTime'])) {
                $schedules['outwardThu']['outwardTime'] = $params["outwardSchedule"]['thuTime'];
                $schedules['outwardThu']['returnTime'] = '';
                $schedules['outwardThu']['thu'] = true;
            }
            if (isset($params["outwardSchedule"]['friTime']) && !is_null($params["outwardSchedule"]['friTime'])) {
                $schedules['outwardFri']['outwardTime'] = $params["outwardSchedule"]['friTime'];
                $schedules['outwardFri']['returnTime'] = '';
                $schedules['outwardFri']['fri'] = true;
            }
            if (isset($params["outwardSchedule"]['satTime']) && !is_null($params["outwardSchedule"]['satTime'])) {
                $schedules['outwardSat']['outwardTime'] = $params["outwardSchedule"]['satTime'];
                $schedules['outwardSat']['returnTime'] = '';
                $schedules['outwardSat']['sat'] = true;
            }
            if (isset($params["outwardSchedule"]['sunTime']) && !is_null($params["outwardSchedule"]['sunTime'])) {
                $schedules['outwardSun']['outwardTime'] = $params["outwardSchedule"]['sunTime'];
                $schedules['outwardSun']['returnTime'] = '';
                $schedules['outwardSun']['sun'] = true;
            }
        }
        if (isset($params["returnSchedule"])) {
            if (!isset($schedules)) {
                $schedules = [];
            }
            if (isset($params["returnSchedule"]['monTime']) && !is_null($params["returnSchedule"]['monTime'])) {
                $schedules['returnMon']['outwardTime'] = '';
                $schedules['returnMon']['returnTime'] = $params["returnSchedule"]['monTime'];
                $schedules['returnMon']['mon'] = true;
            }
            if (isset($params["returnSchedule"]['tueTime']) && !is_null($params["returnSchedule"]['tueTime'])) {
                $schedules['returnTue']['outwardTime'] = '';
                $schedules['returnTue']['returnTime'] = $params["returnSchedule"]['tueTime'];
                $schedules['returnTue']['tue'] = true;
            }
            if (isset($params["returnSchedule"]['wedTime']) && !is_null($params["returnSchedule"]['wedTime'])) {
                $schedules['returnWed']['outwardTime'] = '';
                $schedules['returnWed']['returnTime'] = $params["returnSchedule"]['wedTime'];
                $schedules['returnWed']['wed'] = true;
            }
            if (isset($params["returnSchedule"]['thuTime']) && !is_null($params["returnSchedule"]['thuTime'])) {
                $schedules['returnThu']['outwardTime'] = '';
                $schedules['returnThu']['returnTime'] = $params["returnSchedule"]['thuTime'];
                $schedules['returnThu']['thu'] = true;
            }
            if (isset($params["returnSchedule"]['friTime']) && !is_null($params["returnSchedule"]['friTime'])) {
                $schedules['returnFri']['outwardTime'] = '';
                $schedules['returnFri']['returnTime'] = $params["returnSchedule"]['friTime'];
                $schedules['returnFri']['fri'] = true;
            }
            if (isset($params["returnSchedule"]['satTime']) && !is_null($params["returnSchedule"]['satTime'])) {
                $schedules['returnSat']['outwardTime'] = '';
                $schedules['returnSat']['returnTime'] = $params["returnSchedule"]['satTime'];
                $schedules['returnSat']['sat'] = true;
            }
            if (isset($params["returnSchedule"]['sunTime']) && !is_null($params["returnSchedule"]['sunTime'])) {
                $schedules['returnSun']['outwardTime'] = '';
                $schedules['returnSun']['returnTime'] = $params["returnSchedule"]['sunTime'];
                $schedules['returnSun']['sun'] = true;
            }
        }
        if (isset($schedules)) {
            $data['schedules'] = $schedules;
        }
        return $this->createProposalFromAd($data, $user);
    }

    /**
     * Create a proposal from an ad
     *
     * @param array $ad The data posted by the user
     * @param User $poster The poster of the ad
     * @return Proposal
     */
    public function createProposalFromAd(array $ad, User $poster)
    {
        // todo : create a validation method for $ad
        $proposal = new Proposal();
        $criteria = new Criteria();

        // we check if the ad is posted for another user (delegation)
        if (isset($ad['user'])) {
            $user = $this->userManager->getUser($ad['user']);
            $proposal->setUser($user);
            $proposal->setUserDelegate($poster);
        } else {
            $proposal->setUser($poster);
        }
        // we check if the proposal is private (usually if the proposal is created after a search)
        if (isset($ad['private']) && $ad['private']) {
            $proposal->setPrivate(true);
        }
        // we check if there's a proposalID
        if (isset($ad['proposalId'])) {
            // there's a proposalId : we know that it's a match to force
            $proposal->setMatchingProposal(new Proposal($ad['proposalId']));
        }
        // we check if a formal ask has to be made after the creation of the proposal (usually if the proposal is created after a search)
        if (isset($ad['formalAsk'])) {
            $proposal->setFormalAsk($ad['formalAsk']);
        }
        // we set the type to one way, we'll check later if it's a return trip
        $proposal->setType(Proposal::TYPE_ONE_WAY);
        if (isset($ad['message'])) {
            $proposal->setComment($ad['message']);
        }
        // communities
        if (isset($ad['communities'])) {
            foreach ($ad['communities'] as $community) {
                $proposal->addCommunity($community);
            }
        }
        $criteria->setDriver($ad['driver']);
        $criteria->setPassenger($ad['passenger']);
        $criteria->setSeats($ad['seats']);
        if (isset($ad['solidary'])) {
            $criteria->setSolidaryExclusive($ad['solidary']);
        }
        if (isset($ad['priceKm'])) {
            $criteria->setPriceKm($ad['priceKm']);
        }
        if (isset($ad['price'])) {
            $criteria->setPrice($ad['price']);
        }
        if (isset($ad['roundedPrice'])) {
            $criteria->setRoundedPrice($ad['roundedPrice']);
        }
        if (isset($ad['computedPrice'])) {
            $criteria->setComputedPrice($ad['computedPrice']);
        }
        if (isset($ad['computedRoundedPrice'])) {
            $criteria->setComputedRoundedPrice($ad['computedRoundedPrice']);
        }
        if (isset($ad['outwardPrice'])) {
            $criteria->setPrice($ad['outwardPrice']);
        }
        if (isset($ad['outwardRoundedPrice'])) {
            $criteria->setRoundedPrice($ad['outwardRoundedPrice']);
        }
        if (isset($ad['outwardComputedPrice'])) {
            $criteria->setComputedPrice($ad['outwardComputedPrice']);
        }
        if (isset($ad['outwardComputedRoundedPrice'])) {
            $criteria->setComputedRoundedPrice($ad['outwardComputedRoundedPrice']);
        }
        if (isset($ad['luggage'])) {
            $criteria->setLuggage($ad['luggage']);
        }
        if (isset($ad['bike'])) {
            $criteria->setBike($ad['bike']);
        }
        if (isset($ad['backSeats'])) {
            $criteria->setBackSeats($ad['backSeats']);
        }
        if ($ad['regular']) {
            // regular
            $criteria->setFrequency(Criteria::FREQUENCY_REGULAR);
            if (isset($ad['fromDate'])) {
                $criteria->setFromDate(\DateTime::createFromFormat('Y-m-d', $ad['fromDate']));
            } else {
                $criteria->setFromDate(new \Datetime());
            }
            if (isset($ad['toDate'])) {
                $criteria->setToDate(\DateTime::createFromFormat('Y-m-d', $ad['toDate']));
            }
            
            foreach ($ad['schedules'] as $schedule) {
                if ($schedule['outwardTime'] != '') {
                    if (isset($schedule['mon']) && $schedule['mon']) {
                        $criteria->setMonCheck(true);
                        $criteria->setMonTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $criteria->setMonMarginDuration($this->marginTime);
                    }
                    if (isset($schedule['tue']) && $schedule['tue']) {
                        $criteria->setTueCheck(true);
                        $criteria->setTueTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $criteria->setTueMarginDuration($this->marginTime);
                    }
                    if (isset($schedule['wed']) && $schedule['wed']) {
                        $criteria->setWedCheck(true);
                        $criteria->setWedTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $criteria->setWedMarginDuration($this->marginTime);
                    }
                    if (isset($schedule['thu']) && $schedule['thu']) {
                        $criteria->setThuCheck(true);
                        $criteria->setThuTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $criteria->setThuMarginDuration($this->marginTime);
                    }
                    if (isset($schedule['fri']) && $schedule['fri']) {
                        $criteria->setFriCheck(true);
                        $criteria->setFriTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $criteria->setFriMarginDuration($this->marginTime);
                    }
                    if (isset($schedule['sat']) && $schedule['sat']) {
                        $criteria->setSatCheck(true);
                        $criteria->setsatTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $criteria->setSatMarginDuration($this->marginTime);
                    }
                    if (isset($schedule['sun']) && $schedule['sun']) {
                        $criteria->setSunCheck(true);
                        $criteria->setSunTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $criteria->setSunMarginDuration($this->marginTime);
                    }
                }
                if (isset($schedule['returnTime']) && $schedule['returnTime'] != '') {
                    $proposal->setType(Proposal::TYPE_OUTWARD);
                }
            }
        } else {
            // punctual
            $criteria->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
            $criteria->setFromDate(\DateTime::createFromFormat('Y-m-d', $ad['outwardDate']));
            $criteria->setFromTime($ad['outwardTime'] ? \DateTime::createFromFormat('H:i', $ad['outwardTime']): null);
            $criteria->setMarginDuration($this->marginTime);
            if (isset($ad['returnDate']) && $ad['returnDate'] != '' && isset($ad['returnTime']) && $ad['returnTime'] != '') {
                $proposal->setType(Proposal::TYPE_OUTWARD);
            }
        }

        // waypoints
        $waypointOrigin = new Waypoint();
        $originAddress = new Address();
        if (isset($ad['origin']['houseNumber'])) {
            $originAddress->setHouseNumber($ad['origin']['houseNumber']);
        }
        if (isset($ad['origin']['street'])) {
            $originAddress->setStreet($ad['origin']['street']);
        }
        if (isset($ad['origin']['streetAddress'])) {
            $originAddress->setStreetAddress($ad['origin']['streetAddress']);
        }
        if (isset($ad['origin']['postalCode'])) {
            $originAddress->setPostalCode($ad['origin']['postalCode']);
        }
        if (isset($ad['origin']['subLocality'])) {
            $originAddress->setSubLocality($ad['origin']['subLocality']);
        }
        if (isset($ad['origin']['addressLocality'])) {
            $originAddress->setAddressLocality($ad['origin']['addressLocality']);
        }
        if (isset($ad['origin']['localAdmin'])) {
            $originAddress->setLocalAdmin($ad['origin']['localAdmin']);
        }
        if (isset($ad['origin']['county'])) {
            $originAddress->setCounty($ad['origin']['county']);
        }
        if (isset($ad['origin']['macroCounty'])) {
            $originAddress->setMacroCounty($ad['origin']['macroCounty']);
        }
        if (isset($ad['origin']['region'])) {
            $originAddress->setRegion($ad['origin']['region']);
        }
        if (isset($ad['origin']['macroRegion'])) {
            $originAddress->setMacroRegion($ad['origin']['macroRegion']);
        }
        if (isset($ad['origin']['addressCountry'])) {
            $originAddress->setAddressCountry($ad['origin']['addressCountry']);
        }
        if (isset($ad['origin']['countryCode'])) {
            $originAddress->setCountryCode($ad['origin']['countryCode']);
        }
        if (isset($ad['origin']['latitude'])) {
            $originAddress->setLatitude($ad['origin']['latitude']);
        }
        if (isset($ad['origin']['longitude'])) {
            $originAddress->setLongitude($ad['origin']['longitude']);
        }
        if (isset($ad['origin']['elevation'])) {
            $originAddress->setElevation($ad['origin']['elevation']);
        }
        if (isset($ad['origin']['name'])) {
            $originAddress->setName($ad['origin']['name']);
        }
        if (isset($ad['origin']['home'])) {
            $originAddress->setHome($ad['origin']['home']);
        }
        $waypointOrigin->setAddress($originAddress);
        $waypointOrigin->setPosition(0);
        $waypointOrigin->setDestination(false);
        $proposal->addWaypoint($waypointOrigin);

        $position = 1;
        foreach ($ad['waypoints'] as $waypoint) {
            if ($waypoint['visible']) {
                $waypointStep = new Waypoint();
                $stepAddress = new Address();
                if (isset($waypoint['address']['houseNumber'])) {
                    $stepAddress->setHouseNumber($waypoint['address']['houseNumber']);
                }
                if (isset($waypoint['address']['street'])) {
                    $stepAddress->setStreet($waypoint['address']['street']);
                }
                if (isset($waypoint['address']['streetAddress'])) {
                    $stepAddress->setStreetAddress($waypoint['address']['streetAddress']);
                }
                if (isset($waypoint['address']['postalCode'])) {
                    $stepAddress->setPostalCode($waypoint['address']['postalCode']);
                }
                if (isset($waypoint['address']['subLocality'])) {
                    $stepAddress->setSubLocality($waypoint['address']['subLocality']);
                }
                if (isset($waypoint['address']['addressLocality'])) {
                    $stepAddress->setAddressLocality($waypoint['address']['addressLocality']);
                }
                if (isset($waypoint['address']['localAdmin'])) {
                    $stepAddress->setLocalAdmin($waypoint['address']['localAdmin']);
                }
                if (isset($waypoint['address']['county'])) {
                    $stepAddress->setCounty($waypoint['address']['county']);
                }
                if (isset($waypoint['address']['macroCounty'])) {
                    $stepAddress->setMacroCounty($waypoint['address']['macroCounty']);
                }
                if (isset($waypoint['address']['region'])) {
                    $stepAddress->setRegion($waypoint['address']['region']);
                }
                if (isset($waypoint['address']['macroRegion'])) {
                    $stepAddress->setMacroRegion($waypoint['address']['macroRegion']);
                }
                if (isset($waypoint['address']['addressCountry'])) {
                    $stepAddress->setAddressCountry($waypoint['address']['addressCountry']);
                }
                if (isset($waypoint['address']['countryCode'])) {
                    $stepAddress->setCountryCode($waypoint['address']['countryCode']);
                }
                if (isset($waypoint['address']['latitude'])) {
                    $stepAddress->setLatitude($waypoint['address']['latitude']);
                }
                if (isset($waypoint['address']['longitude'])) {
                    $stepAddress->setLongitude($waypoint['address']['longitude']);
                }
                if (isset($waypoint['address']['elevation'])) {
                    $stepAddress->setElevation($waypoint['address']['elevation']);
                }
                if (isset($waypoint['address']['name'])) {
                    $stepAddress->setName($waypoint['address']['name']);
                }
                if (isset($waypoint['address']['home'])) {
                    $stepAddress->setHome($waypoint['address']['home']);
                }
                $waypointStep->setAddress($stepAddress);
                $waypointStep->setPosition($position);
                $waypointStep->setDestination(false);
                $proposal->addWaypoint($waypointStep);
                $position++;
            }
        }

        $waypointDestination = new Waypoint();
        $destinationAddress = new Address();
        if (isset($ad['destination']['houseNumber'])) {
            $destinationAddress->setHouseNumber($ad['destination']['houseNumber']);
        }
        if (isset($ad['destination']['street'])) {
            $destinationAddress->setStreet($ad['destination']['street']);
        }
        if (isset($ad['destination']['streetAddress'])) {
            $destinationAddress->setStreetAddress($ad['destination']['streetAddress']);
        }
        if (isset($ad['destination']['postalCode'])) {
            $destinationAddress->setPostalCode($ad['destination']['postalCode']);
        }
        if (isset($ad['destination']['subLocality'])) {
            $destinationAddress->setSubLocality($ad['destination']['subLocality']);
        }
        if (isset($ad['destination']['addressLocality'])) {
            $destinationAddress->setAddressLocality($ad['destination']['addressLocality']);
        }
        if (isset($ad['destination']['localAdmin'])) {
            $destinationAddress->setLocalAdmin($ad['destination']['localAdmin']);
        }
        if (isset($ad['destination']['county'])) {
            $destinationAddress->setCounty($ad['destination']['county']);
        }
        if (isset($ad['destination']['macroCounty'])) {
            $destinationAddress->setMacroCounty($ad['destination']['macroCounty']);
        }
        if (isset($ad['destination']['region'])) {
            $destinationAddress->setRegion($ad['destination']['region']);
        }
        if (isset($ad['destination']['macroRegion'])) {
            $destinationAddress->setMacroRegion($ad['destination']['macroRegion']);
        }
        if (isset($ad['destination']['addressCountry'])) {
            $destinationAddress->setAddressCountry($ad['destination']['addressCountry']);
        }
        if (isset($ad['destination']['countryCode'])) {
            $destinationAddress->setCountryCode($ad['destination']['countryCode']);
        }
        if (isset($ad['destination']['latitude'])) {
            $destinationAddress->setLatitude($ad['destination']['latitude']);
        }
        if (isset($ad['destination']['longitude'])) {
            $destinationAddress->setLongitude($ad['destination']['longitude']);
        }
        if (isset($ad['destination']['elevation'])) {
            $destinationAddress->setElevation($ad['destination']['elevation']);
        }
        if (isset($ad['destination']['name'])) {
            $destinationAddress->setName($ad['destination']['name']);
        }
        if (isset($ad['destination']['home'])) {
            $destinationAddress->setHome($ad['destination']['home']);
        }
        $waypointDestination->setAddress($destinationAddress);
        $waypointDestination->setPosition($position);
        $waypointDestination->setDestination(true);
        $proposal->addWaypoint($waypointDestination);
        $proposal->setCriteria($criteria);

        // creation of the outward proposal
        $response = $this->dataProvider->post($proposal);
        if ($response->getCode() != 201) {
            return $response->getValue();
        }
        $proposalOutward = $response->getValue();

        // proposal successfully created, we check if there's a return
        if ($proposal->getType() == Proposal::TYPE_OUTWARD) {
            // creation of the return trip
            $proposalReturn = clone $proposal;
            if (isset($ad['communities'])) {
                foreach ($ad['communities'] as $community) {
                    $proposalReturn->addCommunity($community);
                }
            }
            // if there's a matching linked, it means the proposal we create may be the return trip of a "forced" matching proposal
            if ($proposalOutward->getMatchingLinked()) {
                $proposalReturn->setMatchingLinked($proposalOutward->getMatchingLinked());
            }
            // we check if the proposal is private (usually if the proposal is created after a search)
            if (isset($ad['private']) && $ad['private']) {
                $proposalReturn->setPrivate(true);
            }
            // we check if there's a proposalID
            if (isset($ad['proposalId'])) {
                // there's a proposalId : we know that it's a match to force
                // as it's a return trip, this proposalId will be replaced by the linked proposalId
                $proposalReturn->setMatchingProposal(new Proposal($ad['proposalId']));
            }
            // we check if an formal ask has to be made after the creation of the proposal (usually if the proposal is created after a search)
            if (isset($ad['formalAsk'])) {
                $proposalReturn->setFormalAsk($ad['formalAsk']);
            }
            $criteriaReturn = new Criteria();
            $criteriaReturn->setDriver($ad['driver']);
            $criteriaReturn->setPassenger($ad['passenger']);
            $criteriaReturn->setSeats($ad['seats']);
            if (isset($ad['priceKm'])) {
                $criteriaReturn->setPriceKm($ad['priceKm']);
            }
            if (isset($ad['solidary'])) {
                $criteriaReturn->setSolidaryExclusive($ad['solidary']);
            }
            if (isset($ad['price'])) {
                $criteriaReturn->setPrice($ad['price']);
            }
            if (isset($ad['roundedPrice'])) {
                $criteriaReturn->setRoundedPrice($ad['roundedPrice']);
            }
            if (isset($ad['computedPrice'])) {
                $criteriaReturn->setComputedPrice($ad['computedPrice']);
            }
            if (isset($ad['computedRoundedPrice'])) {
                $criteriaReturn->setComputedRoundedPrice($ad['computedRoundedPrice']);
            }
            if (isset($ad['returnPrice'])) {
                $criteriaReturn->setPrice($ad['returnPrice']);
            }
            if (isset($ad['returnRoundedPrice'])) {
                $criteriaReturn->setRoundedPrice($ad['returnRoundedPrice']);
            }
            if (isset($ad['returnComputedPrice'])) {
                $criteriaReturn->setComputedPrice($ad['returnComputedPrice']);
            }
            if (isset($ad['returnComputedRoundedPrice'])) {
                $criteriaReturn->setComputedRoundedPrice($ad['returnComputedRoundedPrice']);
            }
            if (isset($ad['luggage'])) {
                $criteriaReturn->setLuggage($ad['luggage']);
            }
            if (isset($ad['bike'])) {
                $criteriaReturn->setBike($ad['bike']);
            }
            if (isset($ad['backSeats'])) {
                $criteriaReturn->setBackSeats($ad['backSeats']);
            }
            $proposalReturn->setType(Proposal::TYPE_RETURN);
            $proposalReturn->setCriteria($criteriaReturn);
            if ($ad['regular']) {
                // regular
                $criteriaReturn->setFrequency(Criteria::FREQUENCY_REGULAR);
                if (isset($ad['fromDate'])) {
                    $criteriaReturn->setFromDate(\DateTime::createFromFormat('Y-m-d', $ad['fromDate']));
                } else {
                    $criteriaReturn->setFromDate(new \Datetime());
                }
                if (isset($ad['toDate'])) {
                    $criteriaReturn->setToDate(\DateTime::createFromFormat('Y-m-d', $ad['toDate']));
                }
                foreach ($ad['schedules'] as $schedule) {
                    if (isset($schedule['returnTime']) && $schedule['returnTime'] != '') {
                        if (isset($schedule['mon']) && $schedule['mon']) {
                            $criteriaReturn->setMonCheck(true);
                            $criteriaReturn->setMonTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $criteriaReturn->setMonMarginDuration($this->marginTime);
                        }
                        if (isset($schedule['tue']) && $schedule['tue']) {
                            $criteriaReturn->setTueCheck(true);
                            $criteriaReturn->setTueTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $criteriaReturn->setTueMarginDuration($this->marginTime);
                        }
                        if (isset($schedule['wed']) && $schedule['wed']) {
                            $criteriaReturn->setWedCheck(true);
                            $criteriaReturn->setWedTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $criteriaReturn->setWedMarginDuration($this->marginTime);
                        }
                        if (isset($schedule['thu']) && $schedule['thu']) {
                            $criteriaReturn->setThuCheck(true);
                            $criteriaReturn->setThuTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $criteriaReturn->setThuMarginDuration($this->marginTime);
                        }
                        if (isset($schedule['fri']) && $schedule['fri']) {
                            $criteriaReturn->setFriCheck(true);
                            $criteriaReturn->setFriTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $criteriaReturn->setFriMarginDuration($this->marginTime);
                        }
                        if (isset($schedule['sat']) && $schedule['sat']) {
                            $criteriaReturn->setSatCheck(true);
                            $criteriaReturn->setsatTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $criteriaReturn->setSatMarginDuration($this->marginTime);
                        }
                        if (isset($schedule['sun']) && $schedule['sun']) {
                            $criteriaReturn->setSunCheck(true);
                            $criteriaReturn->setSunTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
                            $criteriaReturn->setSunMarginDuration($this->marginTime);
                        }
                    }
                }
            } else {
                // punctual
                $criteriaReturn->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
                $criteriaReturn->setFromDate(\DateTime::createFromFormat('Y-m-d', $ad['returnDate']));
                $criteriaReturn->setFromTime(\DateTime::createFromFormat('H:i', $ad['returnTime']));
                $criteriaReturn->setMarginDuration($this->marginTime);
            }
        
            // Waypoints
            // We use the waypoints in reverse order if return trip
            // /!\ for now we assume that the return trip uses the same waypoints as the outward) /!\
            $reversedWaypoints = [];
            $nbWaypoints = count($proposal->getWaypoints());
            // we need to get the waypoints in reverse order
            // we will read the waypoints a first time to create an array with the position as index
            $aWaypoints = [];
            foreach ($proposal->getWaypoints() as $proposalWaypoint) {
                $aWaypoints[$proposalWaypoint->getPosition()] = $proposalWaypoint;
            }
            // we sort the array by key
            ksort($aWaypoints);
            // our array is ordered by position, we read it backwards
            $reversedWaypoints = array_reverse($aWaypoints);
            
            $proposalReturn->setCriteria($criteriaReturn);
            foreach ($reversedWaypoints as $pos=>$proposalWaypoint) {
                $waypoint = clone $proposalWaypoint;
                $waypoint->setPosition($pos);
                $waypoint->setDestination(false);
                // address
                $waypoint->setAddress(clone $proposalWaypoint->getAddress());
                if ($pos == ($nbWaypoints-1)) {
                    $waypoint->setDestination(true);
                }
                $proposalReturn->addWaypoint($waypoint);
            }

            // link
            $proposalReturn->setProposalLinked($proposalOutward->getIri());

            // creation of the return proposal
            $response = $this->dataProvider->post($proposalReturn);
            if ($response->getCode() != 201) {
                return $response->getValue();
            }
            
            // we set the linked proposal as the outward proposal was returned before the linked proposal was created...
            $proposalOutward->setProposalLinked($response->getValue()->getId());
        }
 
        return $proposalOutward;
    }


    

    /**
     * Create a proposal
     *
     * @param Proposal $proposal The proposal to create
     *
     * @return Proposal|null The proposal created or null if error.
     */
    public function createProposal(Proposal $proposal)
    {
        $response = $this->dataProvider->post($proposal);
        if ($response->getCode() == 201) {
            return $response->getValue();
        }
        return null;
    }
    
    /**
     * Get all proposals for a user
     *
     * @return array|null The proposals found or null if not found.
     */
    public function getProposals(User $user)
    {
        // we will make the request on the User instead of the Proposal
        $this->dataProvider->setClass(User::class);
        $response = $this->dataProvider->getSubCollection($user->getId(), Proposal::class);
        return $response->getValue();
    }
    
    /**
     * Get a proposal for a user
     *
     * @param int $id
     * @return Proposal|null The proposal found or null if not found.
     */
    public function getProposal(int $id)
    {
        $response = $this->dataProvider->getItem($id);
        return $response->getValue();
    }
    
    /**
     * Get all matchings for a user proposal
     *
     * @return array|null The matchings found or null if not found.
     */
    public function getMatchings(Proposal $proposal)
    {
        // we will make the request on the Matching instead of the Proposal
        if ($proposal->getProposalType() == Proposal::PROPOSAL_TYPE_OFFER) {
            $response = $this->dataProvider->getSubCollection($proposal->getId(), Matching::class, "matching_requests");
        } else {
            $response = $this->dataProvider->getSubCollection($proposal->getId(), Matching::class, "matching_offers");
        }
        return $response->getValue();
    }

    /**
     * Delete a proposal
     *
     * @param int $id The id of the proposal to delete
     *
     * @return boolean The result of the deletion.
     */
    public function deleteProposal(int $id)
    {
        $response = $this->dataProvider->delete($id);
        if ($response->getCode() == 204) {
            return true;
        }
        return false;
    }

    /**
     * Create a solidary proposal from an ad
     *
     * @param array $ad The data posted by the user
     * @param User $poster The poster of the ad
     * @param boolean $persist If we persist the proposal in the database (false for a simple search)
     * @return Proposal
     */
    public function createSolidaryProposalFromData(array $ad, User $poster, $persist = false)
    {
        // todo : create a validation method for $ad
        $proposal = new Proposal();
        $criteria = new Criteria();

        // we check if the ad is posted for another user (delegation)
        if (isset($ad['user'])) {
            $user = $this->userManager->getUser($ad['user']);
            $proposal->setUser($user);
            $proposal->setUserDelegate($poster);
        } else {
            $proposal->setUser($poster);
        }
        // we set the type to one way, we'll check later if it's a return trip
        $proposal->setType(Proposal::TYPE_ONE_WAY);
        if (isset($ad['message'])) {
            $proposal->setComment($ad['message']);
        }
        // communities
        if (isset($ad['communities'])) {
            foreach ($ad['communities'] as $community) {
                $proposal->addCommunity($community);
            }
        }
//        $criteria->setDriver($ad['driver']);
        $criteria->setPassenger($ad['passenger']);
//        $criteria->setPriceKm($ad['priceKm']);
//        $criteria->setSeats($ad['seats']);
        if (isset($ad['price'])) {
            $criteria->setPrice($ad['price']);
        }
        if (isset($ad['luggage'])) {
            $criteria->setLuggage($ad['luggage']);
        }
        if (isset($ad['bike'])) {
            $criteria->setBike($ad['bike']);
        }
        if (isset($ad['backSeats'])) {
            $criteria->setBackSeats($ad['backSeats']);
        }
        if (isset($ad['regular']) && $ad['regular']) {
            // regular
            $criteria->setFrequency(Criteria::FREQUENCY_REGULAR);
            $criteria->setFromDate(new \Datetime());
            // $toDate = new \Datetime();
            // $toDate->add(new \DateInterval("P".Proposal::PROPOSAL_VALIDITY."Y"));
            // $criteria->setToDate($toDate);
            foreach ($ad['schedules'] as $schedule) {
                if ($schedule['outwardTime'] != '') {
                    if ($schedule['mon']) {
                        $criteria->setMonCheck(true);
                        $criteria->setMonTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $criteria->setMonMarginDuration($this->marginTime);
                    }
                    if ($schedule['tue']) {
                        $criteria->setTueCheck(true);
                        $criteria->setTueTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $criteria->setTueMarginDuration($this->marginTime);
                    }
                    if ($schedule['wed']) {
                        $criteria->setWedCheck(true);
                        $criteria->setWedTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $criteria->setWedMarginDuration($this->marginTime);
                    }
                    if ($schedule['thu']) {
                        $criteria->setThuCheck(true);
                        $criteria->setThuTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $criteria->setThuMarginDuration($this->marginTime);
                    }
                    if ($schedule['fri']) {
                        $criteria->setFriCheck(true);
                        $criteria->setFriTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $criteria->setFriMarginDuration($this->marginTime);
                    }
                    if ($schedule['sat']) {
                        $criteria->setSatCheck(true);
                        $criteria->setsatTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $criteria->setSatMarginDuration($this->marginTime);
                    }
                    if ($schedule['sun']) {
                        $criteria->setSunCheck(true);
                        $criteria->setSunTime(\DateTime::createFromFormat('H:i', $schedule['outwardTime']));
                        $criteria->setSunMarginDuration($this->marginTime);
                    }
                }
                if (isset($schedule['returnTime']) && $schedule['returnTime'] != '') {
                    $proposal->setType(Proposal::TYPE_OUTWARD);
                }
            }
        } else {
            // punctual
            $criteria->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
            $criteria->setFromDate(\DateTime::createFromFormat('Y-m-d', $ad['date']));
//            $criteria->setFromTime($ad['date'] ? \DateTime::createFromFormat('H:i', $ad['date']): null);
            $criteria->setMarginDuration($this->marginTime);
            if (isset($ad['returnDate']) && $ad['returnDate'] != '' && isset($ad['returnTime']) && $ad['returnTime'] != '') {
                $proposal->setType(Proposal::TYPE_OUTWARD);
            }
        }

        // waypoints
        $waypointOrigin = new Waypoint();
        $originAddress = new Address();
        if (isset($ad['origin']['houseNumber'])) {
            $originAddress->setHouseNumber($ad['origin']['houseNumber']);
        }
        if (isset($ad['origin']['street'])) {
            $originAddress->setStreet($ad['origin']['street']);
        }
        if (isset($ad['origin']['streetAddress'])) {
            $originAddress->setStreetAddress($ad['origin']['streetAddress']);
        }
        if (isset($ad['origin']['postalCode'])) {
            $originAddress->setPostalCode($ad['origin']['postalCode']);
        }
        if (isset($ad['origin']['subLocality'])) {
            $originAddress->setSubLocality($ad['origin']['subLocality']);
        }
        if (isset($ad['origin']['addressLocality'])) {
            $originAddress->setAddressLocality($ad['origin']['addressLocality']);
        }
        if (isset($ad['origin']['localAdmin'])) {
            $originAddress->setLocalAdmin($ad['origin']['localAdmin']);
        }
        if (isset($ad['origin']['county'])) {
            $originAddress->setCounty($ad['origin']['county']);
        }
        if (isset($ad['origin']['macroCounty'])) {
            $originAddress->setMacroCounty($ad['origin']['macroCounty']);
        }
        if (isset($ad['origin']['region'])) {
            $originAddress->setRegion($ad['origin']['region']);
        }
        if (isset($ad['origin']['macroRegion'])) {
            $originAddress->setMacroRegion($ad['origin']['macroRegion']);
        }
        if (isset($ad['origin']['addressCountry'])) {
            $originAddress->setAddressCountry($ad['origin']['addressCountry']);
        }
        if (isset($ad['origin']['countryCode'])) {
            $originAddress->setCountryCode($ad['origin']['countryCode']);
        }
        if (isset($ad['origin']['latitude'])) {
            $originAddress->setLatitude($ad['origin']['latitude']);
        }
        if (isset($ad['origin']['longitude'])) {
            $originAddress->setLongitude($ad['origin']['longitude']);
        }
        if (isset($ad['origin']['elevation'])) {
            $originAddress->setElevation($ad['origin']['elevation']);
        }
        if (isset($ad['origin']['name'])) {
            $originAddress->setName($ad['origin']['name']);
        }
        if (isset($ad['origin']['home'])) {
            $originAddress->setHome($ad['origin']['home']);
        }
        $waypointOrigin->setAddress($originAddress);
        $waypointOrigin->setPosition(0);
        $waypointOrigin->setDestination(false);
        $proposal->addWaypoint($waypointOrigin);

        $position = 1;
//        foreach ($ad['waypoints'] as $waypoint) {
//            if ($waypoint['visible']) {
//                $waypointStep = new Waypoint();
//                $stepAddress = new Address();
//                if (isset($waypoint['address']['houseNumber'])) {
//                    $stepAddress->setHouseNumber($waypoint['address']['houseNumber']);
//                }
//                if (isset($waypoint['address']['street'])) {
//                    $stepAddress->setStreet($waypoint['address']['street']);
//                }
//                if (isset($waypoint['address']['streetAddress'])) {
//                    $stepAddress->setStreetAddress($waypoint['address']['streetAddress']);
//                }
//                if (isset($waypoint['address']['postalCode'])) {
//                    $stepAddress->setPostalCode($waypoint['address']['postalCode']);
//                }
//                if (isset($waypoint['address']['subLocality'])) {
//                    $stepAddress->setSubLocality($waypoint['address']['subLocality']);
//                }
//                if (isset($waypoint['address']['addressLocality'])) {
//                    $stepAddress->setAddressLocality($waypoint['address']['addressLocality']);
//                }
//                if (isset($waypoint['address']['localAdmin'])) {
//                    $stepAddress->setLocalAdmin($waypoint['address']['localAdmin']);
//                }
//                if (isset($waypoint['address']['county'])) {
//                    $stepAddress->setCounty($waypoint['address']['county']);
//                }
//                if (isset($waypoint['address']['macroCounty'])) {
//                    $stepAddress->setMacroCounty($waypoint['address']['macroCounty']);
//                }
//                if (isset($waypoint['address']['region'])) {
//                    $stepAddress->setRegion($waypoint['address']['region']);
//                }
//                if (isset($waypoint['address']['macroRegion'])) {
//                    $stepAddress->setMacroRegion($waypoint['address']['macroRegion']);
//                }
//                if (isset($waypoint['address']['addressCountry'])) {
//                    $stepAddress->setAddressCountry($waypoint['address']['addressCountry']);
//                }
//                if (isset($waypoint['address']['countryCode'])) {
//                    $stepAddress->setCountryCode($waypoint['address']['countryCode']);
//                }
//                if (isset($waypoint['address']['latitude'])) {
//                    $stepAddress->setLatitude($waypoint['address']['latitude']);
//                }
//                if (isset($waypoint['address']['longitude'])) {
//                    $stepAddress->setLongitude($waypoint['address']['longitude']);
//                }
//                if (isset($waypoint['address']['elevation'])) {
//                    $stepAddress->setElevation($waypoint['address']['elevation']);
//                }
//                if (isset($waypoint['address']['name'])) {
//                    $stepAddress->setName($waypoint['address']['name']);
//                }
//                if (isset($waypoint['address']['home'])) {
//                    $stepAddress->setHome($waypoint['address']['home']);
//                }
//                $waypointStep->setAddress($stepAddress);
//                $waypointStep->setPosition($position);
//                $waypointStep->setDestination(false);
//                $proposal->addWaypoint($waypointStep);
//                $position++;
//            }
//        }

        $waypointDestination = new Waypoint();
        $destinationAddress = new Address();
        if (isset($ad['destination']['houseNumber'])) {
            $destinationAddress->setHouseNumber($ad['destination']['houseNumber']);
        }
        if (isset($ad['destination']['street'])) {
            $destinationAddress->setStreet($ad['destination']['street']);
        }
        if (isset($ad['destination']['streetAddress'])) {
            $destinationAddress->setStreetAddress($ad['destination']['streetAddress']);
        }
        if (isset($ad['destination']['postalCode'])) {
            $destinationAddress->setPostalCode($ad['destination']['postalCode']);
        }
        if (isset($ad['destination']['subLocality'])) {
            $destinationAddress->setSubLocality($ad['destination']['subLocality']);
        }
        if (isset($ad['destination']['addressLocality'])) {
            $destinationAddress->setAddressLocality($ad['destination']['addressLocality']);
        }
        if (isset($ad['destination']['localAdmin'])) {
            $destinationAddress->setLocalAdmin($ad['destination']['localAdmin']);
        }
        if (isset($ad['destination']['county'])) {
            $destinationAddress->setCounty($ad['destination']['county']);
        }
        if (isset($ad['destination']['macroCounty'])) {
            $destinationAddress->setMacroCounty($ad['destination']['macroCounty']);
        }
        if (isset($ad['destination']['region'])) {
            $destinationAddress->setRegion($ad['destination']['region']);
        }
        if (isset($ad['destination']['macroRegion'])) {
            $destinationAddress->setMacroRegion($ad['destination']['macroRegion']);
        }
        if (isset($ad['destination']['addressCountry'])) {
            $destinationAddress->setAddressCountry($ad['destination']['addressCountry']);
        }
        if (isset($ad['destination']['countryCode'])) {
            $destinationAddress->setCountryCode($ad['destination']['countryCode']);
        }
        if (isset($ad['destination']['latitude'])) {
            $destinationAddress->setLatitude($ad['destination']['latitude']);
        }
        if (isset($ad['destination']['longitude'])) {
            $destinationAddress->setLongitude($ad['destination']['longitude']);
        }
        if (isset($ad['destination']['elevation'])) {
            $destinationAddress->setElevation($ad['destination']['elevation']);
        }
        if (isset($ad['destination']['name'])) {
            $destinationAddress->setName($ad['destination']['name']);
        }
        if (isset($ad['destination']['home'])) {
            $destinationAddress->setHome($ad['destination']['home']);
        }
        $waypointDestination->setAddress($destinationAddress);
        $waypointDestination->setPosition($position);
        $waypointDestination->setDestination(true);
        $proposal->addWaypoint($waypointDestination);
        $proposal->setCriteria($criteria);

        if (isset($ad['proposalId'])) {
            // There' a proposalId : we know that is a match
            $proposal->setMatchedProposal(new Proposal($ad['proposalId']));
        }

        if ($persist) {
            $response = $this->dataProvider->post($proposal);
            if ($response->getCode() != 201) {
                return $response->getValue();
            }
        } else {
            return $proposal;
        }
        // creation of the outward proposal
//
//        $proposalOutward = $response->getValue();
//
//        // proposal successfully created, we check if there's a return
//        if ($proposal->getType() == Proposal::TYPE_OUTWARD) {
//            // creation of the return trip
//            $proposalReturn = clone $proposal;
//            if (isset($ad['communities'])) {
//                foreach ($ad['communities'] as $community) {
//                    $proposalReturn->addCommunity($community);
//                }
//            }
//            $criteriaReturn = new Criteria();
//            $criteriaReturn->setDriver($ad['driver']);
//            $criteriaReturn->setPassenger($ad['passenger']);
//            $criteriaReturn->setPriceKm($ad['priceKm']);
//            $criteriaReturn->setSeats($ad['seats']);
//            if (isset($ad['price'])) {
//                $criteriaReturn->setPrice($ad['price']);
//            }
//            if (isset($ad['luggage'])) {
//                $criteriaReturn->setLuggage($ad['luggage']);
//            }
//            if (isset($ad['bike'])) {
//                $criteriaReturn->setBike($ad['bike']);
//            }
//            if (isset($ad['backSeats'])) {
//                $criteriaReturn->setBackSeats($ad['backSeats']);
//            }
//            $proposalReturn->setType(Proposal::TYPE_RETURN);
//            $proposalReturn->setCriteria($criteriaReturn);
//            if ($ad['regular']) {
//                // regular
//                $criteriaReturn->setFrequency(Criteria::FREQUENCY_REGULAR);
//                $criteriaReturn->setFromDate(new \Datetime());
//                // $toDateReturn = new \Datetime();
//                // $toDateReturn->add(new \DateInterval("P".Proposal::PROPOSAL_VALIDITY."Y"));
//                // $criteriaReturn->setToDate($toDateReturn);
//                foreach ($ad['schedules'] as $schedule) {
//                    if (isset($schedule['returnTime']) && $schedule['returnTime'] != '') {
//                        if ($schedule['mon']) {
//                            $criteriaReturn->setMonCheck(true);
//                            $criteriaReturn->setMonTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
//                            $criteriaReturn->setMonMarginDuration($this->marginTime);
//                        }
//                        if ($schedule['tue']) {
//                            $criteriaReturn->setTueCheck(true);
//                            $criteriaReturn->setTueTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
//                            $criteriaReturn->setTueMarginDuration($this->marginTime);
//                        }
//                        if ($schedule['wed']) {
//                            $criteriaReturn->setWedCheck(true);
//                            $criteriaReturn->setWedTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
//                            $criteriaReturn->setWedMarginDuration($this->marginTime);
//                        }
//                        if ($schedule['thu']) {
//                            $criteriaReturn->setThuCheck(true);
//                            $criteriaReturn->setThuTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
//                            $criteriaReturn->setThuMarginDuration($this->marginTime);
//                        }
//                        if ($schedule['fri']) {
//                            $criteriaReturn->setFriCheck(true);
//                            $criteriaReturn->setFriTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
//                            $criteriaReturn->setFriMarginDuration($this->marginTime);
//                        }
//                        if ($schedule['sat']) {
//                            $criteriaReturn->setSatCheck(true);
//                            $criteriaReturn->setsatTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
//                            $criteriaReturn->setSatMarginDuration($this->marginTime);
//                        }
//                        if ($schedule['sun']) {
//                            $criteriaReturn->setSunCheck(true);
//                            $criteriaReturn->setSunTime(\DateTime::createFromFormat('H:i', $schedule['returnTime']));
//                            $criteriaReturn->setSunMarginDuration($this->marginTime);
//                        }
//                    }
//                }
//            } else {
//                // punctual
//                $criteriaReturn->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
//                $criteriaReturn->setFromDate(\DateTime::createFromFormat('Y-m-d', $ad['returnDate']));
//                $criteriaReturn->setFromTime(\DateTime::createFromFormat('H:i', $ad['returnTime']));
//                $criteriaReturn->setMarginDuration($this->marginTime);
//            }
//
//            // Waypoints
//            // We use the waypoints in reverse order if return trip
//            // /!\ for now we assume that the return trip uses the same waypoints as the outward) /!\
//            $reversedWaypoints = [];
//            $nbWaypoints = count($proposal->getWaypoints());
//            // we need to get the waypoints in reverse order
//            // we will read the waypoints a first time to create an array with the position as index
//            $aWaypoints = [];
//            foreach ($proposal->getWaypoints() as $proposalWaypoint) {
//                $aWaypoints[$proposalWaypoint->getPosition()] = $proposalWaypoint;
//            }
//            // we sort the array by key
//            ksort($aWaypoints);
//            // our array is ordered by position, we read it backwards
//            $reversedWaypoints = array_reverse($aWaypoints);
//
//            $proposalReturn->setCriteria($criteriaReturn);
//            foreach ($reversedWaypoints as $pos=>$proposalWaypoint) {
//                $waypoint = clone $proposalWaypoint;
//                $waypoint->setPosition($pos);
//                $waypoint->setDestination(false);
//                // address
//                $waypoint->setAddress(clone $proposalWaypoint->getAddress());
//                if ($pos == ($nbWaypoints-1)) {
//                    $waypoint->setDestination(true);
//                }
//                $proposalReturn->addWaypoint($waypoint);
//            }
//
//            // link
//            $proposalReturn->setProposalLinked($proposalOutward->getIri());
//
//            // creation of the return proposal
////            $response = $this->dataProvider->post($proposalReturn);
////            if ($response->getCode() != 201) {
////                return $response->getValue();
////            }
//
//            // we set the linked proposal as the outward proposal was returned before the linked proposal was created...
//            $proposalOutward->setProposalLinked($response->getValue()->getId());
//        }
    }
}
