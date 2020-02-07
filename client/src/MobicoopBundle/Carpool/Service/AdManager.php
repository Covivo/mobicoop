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
        $this->dataProvider->setClass(Ad::class, Ad::RESOURCE_NAME);
    }

    /**
     * Get an ad and its results
     *
     * @param int $id       The ad id
     * @param array|null    The filters to apply to the results
     * @return Ad|null
     */
    public function getAd(int $id, ?array $filters = null)
    {
        if ($data = $this->dataProvider->getItem($id, $filters)) {
            return $data->getValue();
        }
        return null;
    }

    /**
     * Create an ad. The ad can be a search.
     *
     * @param array $data   The data used to create the ad
     * @return Ad
     */
    public function createAd(array $data)
    {
        $ad = new Ad();
        
        // the ad is a search ?
        if (isset($data['search']) && $data['search']) {
            $ad->setSearch(true);
        }

        // role
        $ad->setRole($data['driver'] ? ($data['passenger'] ? Ad::ROLE_DRIVER_OR_PASSENGER : Ad::ROLE_DRIVER) : Ad::ROLE_PASSENGER);

        // oneway ?
        if (isset($data['oneway']) && $data['oneway']) {
            $ad->setOneWay(true);
        }

        // frequency
        $ad->setFrequency($data['regular'] ? Ad::FREQUENCY_REGULAR : Ad::FREQUENCY_PUNCTUAL);

        // outward waypoints
        $outwardsWaypoints[] = $data['origin'];
        foreach ($data['waypoints'] as $waypoint) {
            if ($waypoint['visible']) {
                $outwardsWaypoints[] = $waypoint['address'];
            }
        }
        $outwardsWaypoints[] = $data['destination'];
        $ad->setOutwardWaypoints($outwardsWaypoints);

        // date and time
        if ($ad->getFrequency() == Ad::FREQUENCY_REGULAR) {
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
        } else {
            $ad->setOutwardDate($data['outwardDate']);
            $ad->setOutwardTime(isset($data['outwardTime']) ? $data['outwardTime'] : null);
            if (isset($data['returnDate']) && isset($data['returnTime'])) {
                $ad->setOneWay(false);
                $ad->setReturnDate($data['returnDate']);
                $ad->setReturnTime($data['returnTime']);
            }
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

        //Gestion events : If an event is set as destination or arrival, we set the event in proposal
        if ((isset($data['origin']['event']) && $data['origin']['event'] != null) || (isset($data['destination']['event']) && $data['destination']['event'] != null)) {
            $event = $data['origin']['event']  != null ? $data['origin']['event'] : $data['destination']['event'];
            $ad->setEventId($event['id']);
        }


        // filters
        if (isset($data['filters'])) {
            $ad->setFilters($data['filters']);
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
     * @param \Datetime $date               The date in a Datetime object
     * @param \Datetime $time               The time in a Datetime object
     * @param boolean $regular              The trip is regular
     * @param boolean|null $strictDate      Strict date
     * @param boolean $strictPunctual       Strictly punctual
     * @param boolean $strictRegular        Strictly regular
     * @param integer|null $role            Role (driver and/or passenger)
     * @param integer|null $userId          User id of the requester (to exclude its own results)
     * @param integer $communityId          Community id of the requester (to get only results from that community)
     * @param array|null $filters           Filters and order choices
     * @return array|null The matchings found or null if not found.
     */
    public function getResultsForSearch(
        array $origin,
        array $destination,
        \Datetime $date,
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
            "origin" => $origin,
            "destination" => $destination,
            "waypoints" => [],
            "outwardDate" => $date,
            "regular" => $regular,
            "search" => true
        ];
        if (!is_null($strictDate)) {
            $params["strictDate"] = $strictDate;
        }
        if (!is_null($time)) {
            $params["outwardTime"] = $time;
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
        if (!is_null($filters)) {
            $params["filters"] = $filters;
        }
        return $this->createAd($params);
    }

    /**
     * Create an ask from an ad result
     *
     * @param array $data           The data used to create the ask
     * @param boolean|null $formal  If the ask is formal
     * @return void
     */
    public function createAsk(array $params, ?bool $formal=false)
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
            $schedule  = [];
            if (isset($params['outwardSchedule']['monTime']) && !is_null($params['outwardSchedule']['monTime'])) {
                if (isset($params['returnSchedule']['monTime']) && !is_null($params['returnSchedule']['monTime'])) {
                    $schedule[] = [
                        'outwardTime' => $params['outwardSchedule']['monTime'],
                        'returnTime' => $params['returnSchedule']['monTime'],
                        'mon' => true
                    ];
                } else {
                    $schedule[] = [
                        'outwardTime' => $params['outwardSchedule']['monTime'],
                        'returnTime' => '',
                        'mon' => true
                    ];
                }
            } elseif (isset($params['returnSchedule']['monTime']) && !is_null($params['returnSchedule']['monTime'])) {
                $schedule[] = [
                    'outwardTime' => '',
                    'returnTime' => $params['returnSchedule']['monTime'],
                    'mon' => true
                ];
            }
            if (isset($params['outwardSchedule']['tueTime']) && !is_null($params['outwardSchedule']['tueTime'])) {
                if (isset($params['returnSchedule']['tueTime']) && !is_null($params['returnSchedule']['tueTime'])) {
                    $schedule[] = [
                        'outwardTime' => $params['outwardSchedule']['tueTime'],
                        'returnTime' => $params['returnSchedule']['tueTime'],
                        'tue' => true
                    ];
                } else {
                    $schedule[] = [
                        'outwardTime' => $params['outwardSchedule']['tueTime'],
                        'returnTime' => '',
                        'tue' => true
                    ];
                }
            } elseif (isset($params['returnSchedule']['tueTime']) && !is_null($params['returnSchedule']['tueTime'])) {
                $schedule[] = [
                    'outwardTime' => '',
                    'returnTime' => $params['returnSchedule']['tueTime'],
                    'tue' => true
                ];
            }
            if (isset($params['outwardSchedule']['wedTime']) && !is_null($params['outwardSchedule']['wedTime'])) {
                if (isset($params['returnSchedule']['wedTime']) && !is_null($params['returnSchedule']['wedTime'])) {
                    $schedule[] = [
                        'outwardTime' => $params['outwardSchedule']['wedTime'],
                        'returnTime' => $params['returnSchedule']['wedTime'],
                        'wed' => true
                    ];
                } else {
                    $schedule[] = [
                        'outwardTime' => $params['outwardSchedule']['wedTime'],
                        'returnTime' => '',
                        'wed' => true
                    ];
                }
            } elseif (isset($params['returnSchedule']['wedTime']) && !is_null($params['returnSchedule']['wedTime'])) {
                $schedule[] = [
                    'outwardTime' => '',
                    'returnTime' => $params['returnSchedule']['wedTime'],
                    'wed' => true
                ];
            }
            if (isset($params['outwardSchedule']['thuTime']) && !is_null($params['outwardSchedule']['thuTime'])) {
                if (isset($params['returnSchedule']['thuTime']) && !is_null($params['returnSchedule']['thuTime'])) {
                    $schedule[] = [
                        'outwardTime' => $params['outwardSchedule']['thuTime'],
                        'returnTime' => $params['returnSchedule']['thuTime'],
                        'thu' => true
                    ];
                } else {
                    $schedule[] = [
                        'outwardTime' => $params['outwardSchedule']['thuTime'],
                        'returnTime' => '',
                        'thu' => true
                    ];
                }
            } elseif (isset($params['returnSchedule']['thuTime']) && !is_null($params['returnSchedule']['thuTime'])) {
                $schedule[] = [
                    'outwardTime' => '',
                    'returnTime' => $params['returnSchedule']['thuTime'],
                    'thu' => true
                ];
            }
            if (isset($params['outwardSchedule']['friTime']) && !is_null($params['outwardSchedule']['friTime'])) {
                if (isset($params['returnSchedule']['friTime']) && !is_null($params['returnSchedule']['friTime'])) {
                    $schedule[] = [
                        'outwardTime' => $params['outwardSchedule']['friTime'],
                        'returnTime' => $params['returnSchedule']['friTime'],
                        'fri' => true
                    ];
                } else {
                    $schedule[] = [
                        'outwardTime' => $params['outwardSchedule']['friTime'],
                        'returnTime' => '',
                        'fri' => true
                    ];
                }
            } elseif (isset($params['returnSchedule']['friTime']) && !is_null($params['returnSchedule']['friTime'])) {
                $schedule[] = [
                    'outwardTime' => '',
                    'returnTime' => $params['returnSchedule']['friTime'],
                    'fri' => true
                ];
            }
            if (isset($params['outwardSchedule']['satTime']) && !is_null($params['outwardSchedule']['satTime'])) {
                if (isset($params['returnSchedule']['satTime']) && !is_null($params['returnSchedule']['satTime'])) {
                    $schedule[] = [
                        'outwardTime' => $params['outwardSchedule']['satTime'],
                        'returnTime' => $params['returnSchedule']['satTime'],
                        'sat' => true
                    ];
                } else {
                    $schedule[] = [
                        'outwardTime' => $params['outwardSchedule']['satTime'],
                        'returnTime' => '',
                        'sat' => true
                    ];
                }
            } elseif (isset($params['returnSchedule']['satTime']) && !is_null($params['returnSchedule']['satTime'])) {
                $schedule[] = [
                    'outwardTime' => '',
                    'returnTime' => $params['returnSchedule']['satTime'],
                    'sat' => true
                ];
            }
            if (isset($params['outwardSchedule']['sunTime']) && !is_null($params['outwardSchedule']['sunTime'])) {
                if (isset($params['returnSchedule']['sunTime']) && !is_null($params['returnSchedule']['sunTime'])) {
                    $schedule[] = [
                        'outwardTime' => $params['outwardSchedule']['sunTime'],
                        'returnTime' => $params['returnSchedule']['sunTime'],
                        'sun' => true
                    ];
                } else {
                    $schedule[] = [
                        'outwardTime' => $params['outwardSchedule']['sunTime'],
                        'returnTime' => '',
                        'sun' => true
                    ];
                }
            } elseif (isset($params['returnSchedule']['sunTime']) && !is_null($params['returnSchedule']['sunTime'])) {
                $schedule[] = [
                    'outwardTime' => '',
                    'returnTime' => $params['returnSchedule']['sunTime'],
                    'sun' => true
                ];
            }
            $ad->setSchedule($schedule);
            $ad->setOutwardDate(\DateTime::createFromFormat('Y-m-d', $params['fromDate']));
            $ad->setOutwardLimitdate(\DateTime::createFromFormat('Y-m-d', $params['toDate']));
        } else {
            // punctual or contact ask
        }

        // creation of the ad ask
        if ($formal) {
            $response = $this->dataProvider->post($ad, 'ask');
        } else {
            $response = $this->dataProvider->post($ad, 'contact');
        }
        if ($response->getCode() != 201) {
            return $response->getValue();
        }

        return $response->getValue();
    }

    /**
     * Get an ad and its results by its related Ask
     *
     * @param int $askId   Id of the related Ask
     * @param int $userId  The user that make the request
     * @return Ad|null
     */
    public function getAdAsk(int $askId, int $userId)
    {
        if ($data = $this->dataProvider->getSpecialItem($askId, "ask", ["userId"=>$userId], true)) {
            return $data->getValue();
        }
        return null;
    }

    /**
     * Update an Ask via the Ad
     *
     * @param int $ad   The ad to update
     * @param int $userId  The user that make the request
     * @return Ad|null
     */
    public function updateAdAsk(Ad $ad, int $userId)
    {
        if ($data = $this->dataProvider->putSpecial($ad, null, "ask", ["userId"=>$userId], true)) {
            return $data->getValue();
        }
        return null;
    }
}
