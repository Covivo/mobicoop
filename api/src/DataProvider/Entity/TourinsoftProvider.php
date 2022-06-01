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

namespace App\DataProvider\Entity;

use App\DataProvider\Service\DataProvider;
use App\Event\Entity\Event;
use App\Event\Interfaces\EventProviderInterface;
use App\Geography\Entity\Address;
use DateTime;
use Exception;

/**
 * Event Provider for Tourinsoft.
 *
 * @author Celine Jacquet <celine.jacquet@mobicoop.org>
 */
class TourinsoftProvider implements EventProviderInterface
{
    public const PROVIDER = 'Tourinsoft';
    public const FORMAT = 'JSON';
    public const COMMUNICATION_MEDIA_WEBSITE_KEY = '#Site web';
    public const REQUESTED_FIELDS = 'SyndicObjectID,SyndicObjectName,MoyenDeCom,Description,ObjectTypeName,Adresse1,Adresse2,Adresse3,GmapLatitude,GmapLongitude,PeriodeOuverture,Photos,CodePostal,Commune,LieuManifestation';

    private $eventProviderServerUrl;

    public function __construct(string $eventProviderServerUrl)
    {
        $this->eventProviderServerUrl = $eventProviderServerUrl;
    }

    /**
     * Get tourinsoft event.
     */
    public function getEvent(): void
    {
    }

    /**
     * Get tourinsoft events.
     *
     * @return array array of events
     */
    public function getEvents(): array
    {
        $dataProvider = new DataProvider($this->eventProviderServerUrl);

        // we set an empty array of tourinsoft events
        $tourinsoftEvents = [];
        // We call tourinsoft api to get all events
        $queryParams = [
            '$format' => self::FORMAT,
            '$select' => self::REQUESTED_FIELDS,
        ];

        $response = $dataProvider->getItem($queryParams);
        $events = json_decode($response->getValue(), false);

        foreach ($events->value as $event) {
            $tourinsoftEvents[] = $event;
        }

        return $this->createEvents($tourinsoftEvents);
    }

    /**
     * Create Event Object from Tourinsoft Event.
     *
     * @param array $tourinsoftEvents array of Tourinsoft Events
     *
     * @return array array of events
     */
    public function createEvents($tourinsoftEvents): array
    {
        // https://wcf.tourinsoft.com/Syndication/3.0/cdt11/8132036e-2b56-4710-a160-4737c6493c98/doc/syndication
        // http://api-doc.tourinsoft.com/#/syndication-3x#api-format

        $newEvents = [];

        foreach ($tourinsoftEvents as $event) {
            $newEvent = new Event();
            $newEvent->setExternalId($event->SyndicObjectID);
            $newEvent->setExternalSource(self::PROVIDER);

            if (isset($event->SyndicObjectName)) {
                $newEvent->setName($event->SyndicObjectName);
            } else {
                throw new Exception('Event name is mandatory', 1);
            }

            if (isset($event->PeriodeOuverture)) {
                $dates = $event->PeriodeOuverture;
                $array = explode('|', $dates);

                $startDate = DateTime::createFromFormat('d/m/Y', $array[0]);
                $endDate = DateTime::createFromFormat('d/m/Y', $array[1]);

                $fromDate = $startDate->format('Y-m-d');
                $toDate = $endDate->format('Y-m-d');

                // some events are annual so we check first if the year is up to date if not we set the actual year
                $year = (new \DateTime($fromDate))->format('Y');
                $startDate = new \DateTime($fromDate);
                $endDate = new \DateTime($toDate);
                $actualYear = (new \DateTime('now'))->format('Y');
                if ($year < $actualYear) {
                    $newEvent->setFromDate($startDate->setDate($actualYear, $startDate->format('m'), $startDate->format('d')));
                    $newEvent->setToDate($endDate->setDate($actualYear, $endDate->format('m'), $endDate->format('d')));
                } else {
                    $newEvent->setFromDate($startDate);
                    $newEvent->setToDate($endDate);
                }
            } else {
                throw new Exception('Start and end dates are mandatory', 1);
            }

            if (isset($event->ObjectTypeName)) {
                $newEvent->setDescription($event->ObjectTypeName);
            } else {
                throw new Exception('Description is mandatory', 1);
            }

            if (isset($event->Description)) {
                $newEvent->setFullDescription($event->Description);
            } else {
                throw new Exception('Description is mandatory', 1);
            }

            if (isset($event->Photos)) {
                $url = $event->Photos;
                $picture = explode('|', $url);
                $picture = $picture[0];
                $newEvent->setExternalImageUrl($picture);
            }

            if (isset($event->MoyenDeCom)) {
                $informations = $event->MoyenDeCom;
                $communicationMedia = explode('|', $informations);

                if (in_array(self::COMMUNICATION_MEDIA_WEBSITE_KEY, $communicationMedia)) {
                    $communicationMediaKey = array_search(self::COMMUNICATION_MEDIA_WEBSITE_KEY, $communicationMedia);
                    $newEvent->setUrl($communicationMedia[$communicationMediaKey + 1]);
                }
            }

            // we create and set the address
            $address = new Address();

            $fullStreetAddress = [];

            if (isset($event->Adresse1) || trim('' !== $event->Adresse1)) {
                array_push($fullStreetAddress, $event->Adresse1);
            }
            if (isset($event->Adresse2) || trim('' !== $event->Adresse2)) {
                array_push($fullStreetAddress, $event->Adresse2);
            }

            if (isset($event->Adresse3) || trim('' !== $event->Adresse3)) {
                array_push($fullStreetAddress, $event->Adresse3);
            }

            $fullStreetAddressString = implode(' ', $fullStreetAddress);

            if (!is_null($fullStreetAddressString)) {
                $address->setStreetAddress(isset($fullStreetAddressString) ? $fullStreetAddressString : (isset($event->LieuManifestation) ? $event->LieuManifestation : ''));
            }

            if (isset($event->Commune)) {
                $address->setAddressLocality(isset($event->Commune) ? $event->Commune : null);
            }

            if (isset($event->CodePostal)) {
                $address->setPostalCode(isset($event->CodePostal) ? $event->CodePostal : null);
            }

            if (isset($event->GmapLatitude) && ($event->GmapLongitude)) {
                $address->setLatitude($event->GmapLatitude);
                $address->setLongitude($event->GmapLongitude);
            } else {
                continue;
            }

            $newEvent->setAddress($address);

            // We pass the newEvent in array
            $newEvents[] = $newEvent;
        }

        return $newEvents;
    }
}
