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

use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ask;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Criteria;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Matching;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Proposal;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;

/**
 * Ask management service.
 */
class AskManager
{
    private $dataProvider;
    private $userManager;
    
    /**
     * Constructor.
     *
     */
    public function __construct(DataProvider $dataProvider, UserManager $userManager)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Ask::class);
        $this->userManager = $userManager;
    }

    /**
     * Get an Ask by its identifier
     *
     * @param int $id The Ask id
     *
     * @return Ask|null The Ask found or null if not found.
     */
    public function getAsk(int $id)
    {
        $response = $this->dataProvider->getItem($id);
        $ask = $response->getValue();
        return $ask;
    }
    
    /**
     * Update an Ask
     *
     * @param Ask $ask The Ask to update
     *
     * @return Ask|null The Ask updated or null if error.
     */
    public function updateAsk(Ask $ask)
    {
        $response = $this->dataProvider->put($ask);
        return $response->getValue();
    }

    /**
     * Get all the AskHistories of an Ask
     *
     * @param int $idAsk The Ask id
     *
     * @return array|null The AskHistories found or null if not found.
     */
    public function getAskHistories(int $idAsk)
    {
        $this->dataProvider->setFormat($this->dataProvider::RETURN_JSON);
        $response = $this->dataProvider->getSubCollection($idAsk, 'askhistory', 'ask_histories');
        return $response->getValue();
    }

    /**
     * Create an ask from an ask from a result
     *
     * @param User $user
     * @param array $params     The params
     * @param bool $formalAsk   True if we have to create a formal ask
     * @return void
     */
    public function createAskFromResult(User $user, array $params, bool $formalAsk=false)
    {
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
        if (!isset($schedules)) {
            // no schedule => must be an undecided role ask, we set only a blank return time to create a return ask
            $schedules = [];
            $schedules['outwardMon']['outwardTime'] = '';
            $schedules['outwardMon']['returnTime'] = '';
            $schedules['outwardMon']['mon'] = false;
            $schedules['returnMon']['outwardTime'] = '';
            $schedules['returnMon']['returnTime'] = 'blank';
            $schedules['returnMon']['mon'] = false;
        }
        $params['schedules'] = $schedules;

        $ask = new Ask();
        $criteria = new Criteria();

        if ($formalAsk) {
            // if it's a formal ask, the status is pending
            $ask->setStatus(Ask::STATUS_PENDING);
        } else {
            // if it's not a formal ask, the status is initiated
            $ask->setStatus(Ask::STATUS_INITIATED);
        }

        $data = [
            "matchingId" => $params['matchingId'],
        ];

        // we set the type to one way, we'll check later if it's a return trip
        $ask->setType(Proposal::TYPE_ONE_WAY);

        // we check if the ask is posted for another user (delegation)
        if (isset($params['user'])) {
            $user = $this->userManager->getUser($params['user']);
            $ask->setUser($user);
            $ask->setUserDelegate($this->userManager->getLoggedUser());
        } else {
            $ask->setUser($this->userManager->getLoggedUser());
        }
        // we check if a formal is asked
        $ask->setStatus(Ask::STATUS_INITIATED);
        if (isset($data['formalAsk'])) {
            $ask->setStatus(Ask::STATUS_PENDING);
        }
        $criteria->setDriver($params['driver']);
        $criteria->setPassenger($params['passenger']);
        $criteria->setSeatsDriver(isset($params['seatsDriver']) ? $params['seatsDriver'] : 1);
        $criteria->setSeatsPassenger(isset($params['seatsPassenger']) ? $params['seatsPassenger'] : 1);

        if ($params['regular']) {
            // regular
            $criteria->setFrequency(Criteria::FREQUENCY_REGULAR);
            if (isset($params['fromDate'])) {
                $criteria->setFromDate(\DateTime::createFromFormat('Y-m-d', $params['fromDate']));
            } else {
                $criteria->setFromDate(new \Datetime());
            }
            if (isset($params['toDate'])) {
                $criteria->setToDate(\DateTime::createFromFormat('Y-m-d', $params['toDate']));
            }
            
            foreach ($params['schedules'] as $schedule) {
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
                    $ask->setType(Proposal::TYPE_OUTWARD);
                }
            }
        } else {
            // punctual
            $criteria->setFrequency(Criteria::FREQUENCY_PUNCTUAL);
            $criteria->setFromDate(\DateTime::createFromFormat('Y-m-d', $data['outwardDate']));
            $criteria->setFromTime($params['outwardTime'] ? \DateTime::createFromFormat('H:i', $params['outwardTime']): null);
            //$criteria->setMarginDuration($this->marginTime);
            if (isset($params['returnDate']) && $params['returnDate'] != '' && isset($params['returnTime']) && $params['returnTime'] != '') {
                $ask->setType(Proposal::TYPE_OUTWARD);
            }
        }

        $ask->setMatching(new Matching($params['matchingId']));

        $ask->setCriteria($criteria);

        // creation of the ask
        $response = $this->dataProvider->post($ask);
        return $response->getValue();
    }
}
