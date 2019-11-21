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

namespace Mobicoop\Bundle\MobicoopBundle\Event\Service;

use Mobicoop\Bundle\MobicoopBundle\Event\Entity\Event;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;

/**
 * Event management service.
 */
class EventManager
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
        $this->dataProvider->setClass(Event::class);
    }
    
    /**
     * Create an event
     *
     * @param Event $event The event to create
     *
     * @return Event|null The event created or null if error.
     */
    public function createEvent($data, Event $event, User $user)
    {
        $address = new Address();
        // set the user as a user of the community
        $event->setUser($user);

        // set event address
        $communityAddress=json_decode($data->get('address'), true);
        $address->setAddressCountry($communityAddress['addressCountry']);
        $address->setAddressLocality($communityAddress['addressLocality']);
        $address->setCountryCode($communityAddress['countryCode']);
        $address->setCounty($communityAddress['county']);
        $address->setLatitude($communityAddress['latitude']);
        $address->setLocalAdmin($communityAddress['localAdmin']);
        $address->setLongitude($communityAddress['longitude']);
        $address->setMacroCounty($communityAddress['macroCounty']);
        $address->setMacroRegion($communityAddress['macroRegion']);
        $address->setPostalCode($communityAddress['postalCode']);
        $address->setRegion($communityAddress['region']);
        $address->setStreet($communityAddress['street']);
        $address->setHouseNumber($communityAddress['houseNumber']);
        $address->setStreetAddress($communityAddress['streetAddress']);
        $address->setSubLocality($communityAddress['subLocality']);
        $address->setDisplayLabel($communityAddress['displayLabel']);

        // Set Datetime from data
        $from = $data->get('startTime') != null ? new \DateTime($data->get('startDate').'.'.$data->get('startTime'))  : new \DateTime($data->get('startDate'));
        $to = $data->get('endTime') != null ? new \DateTime($data->get('endDate').'.'.$data->get('endTime'))  : new \DateTime($data->get('endDate'));
        //Set use time = 1, if user set time
        $flagTime = ($data->get('endTime') == null && $data->get('startTime') == null) ?  0 : 1;
        $event->setUseTime($flagTime);
        $event->setStatus(1);

        // set event infos
        $event->setName($data->get('name'));
        $event->setDescription($data->get('fullDescription'));
        $event->setFullDescription($data->get('fullDescription'));
        $event->setAddress($address);
        $event->setUrl($data->get('urlEvent'));
        $event->setFromDate($from);
        $event->setToDate($to);

        $response = $this->dataProvider->post($event);
        if ($response->getCode() == 201) {
            return $response->getValue();
        }
        return null;
    }
    
    /**
     * Get all events which end date happens after a certain date.
     * @param \DateTimeInterface    $endDateIsAfter     The date after which the end date of events are taken account (usually the current date)
     * @param int                   $flag               Flag for know if we want event passed or incoming (0, past, 1 incoming)
     * @param string                $orderBy            Property on which order the results (id or fromDate)
     * @param string                $order              Order type (asc or desc)
     * @param int                   $limit              The max number of results
     * @return array|null The events found or null if not found.
     */
    public function getEvents($flag = 1, ?\DateTimeInterface $endDateIsAfter = null, string $orderBy="fromDate", string $order="asc", int $limit=null)
    {
        $params=[];

        $endDate = $endDateIsAfter ? $endDateIsAfter :  $now = new \DateTime();
        if ($flag == 0) {
            $params['toDate[strictly_before]'] = $endDate->format('Y-m-d');
        } else {
            $params['toDate[after]'] = $endDate->format('Y-m-d');
        }

        if ($orderBy == "fromDate") {
            $params['order[fromDate]'] = $order;
        }
        if ($orderBy == "id") {
            $params['order[id]'] = $order;
        }
        if ($limit) {
            $params['perPage'] = $limit;
        }

        $response = $this->dataProvider->getCollection($params);
        if ($response->getCode() >=200 && $response->getCode() <= 300) {
            return $response->getValue()->getMember();
        }
        return $response->getValue();
    }
    
    /**
     * Get an event
     *
     * @param int $id
     * @return Event|null The event found or null if not found.
     */
    public function getEvent(int $id)
    {
        $response = $this->dataProvider->getItem($id);

        return $response->getValue();
    }
    
    /**
     * Update an event
     *
     * @param Event $event The event to update
     *
     * @return Event|null The event updated or null if error.
     */
    public function updateEvent(Event $event)
    {
        $response = $this->dataProvider->put($event);
        return $response->getValue();
    }
    
    /**
     * Delete an event
     *
     * @param int $id The id of the event to delete
     *
     * @return boolean The result of the deletion.
     */
    public function deleteEvent(int $id)
    {
        $response = $this->dataProvider->delete($id);
        if ($response->getCode() == 204) {
            return true;
        }
        return false;
    }

    /**
     * Undocumented function
     *
     * @param integer $id
     * @return void
     */
    public function getProposals(int $id)
    {
        $this->dataProvider->setFormat($this->dataProvider::RETURN_JSON);
        $proposals = $this->dataProvider->getSubCollection($id, "proposal", "proposals");
        return $proposals->getValue();
    }
}
