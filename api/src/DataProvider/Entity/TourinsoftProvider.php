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

namespace App\DataProvider\Entity;

use App\DataProvider\Service\DataProvider;
use App\Event\Entity\Event;
use App\Event\Interfaces\EventProviderInterface;
use App\Geography\Entity\Address;
use DateTime;
use Exception;

/**
 * Event Provider for Tourinsoft
 *
 * @author Celine Jacquet <celine.jacquet@mobicoop.org>
 */
class TourinsoftProvider implements EventProviderInterface
{
    const PROVIDER = "Tourinsoft";
    const FORMAT = "json";
    const COMMUNICATION_MEDIA_WEBSITE_KEY = "Site web";

    private $eventProviderServerUrl;

    public function __construct(string $eventProviderServerUrl)
    {
        $this->eventProviderServerUrl = $eventProviderServerUrl;
    }

    /**
     * Get tourinsoft event
     *
     * @return void
     */
    public function getEvent()
    {
    }

    /**
     * Get tourinsoft events
     *
     * @return Array array of events
     */
    public function getEvents()
    {
        $dataProvider = new DataProvider($this->eventProviderServerUrl);

        // we set an empty array of tourinsoft events
        $tourinsoftEvents = [];
        // We call tourinsoft api v3 to get all events
        $queryParams = [
            'format' => self::FORMAT
        ];

        $response = $dataProvider->getItem($queryParams);
        $events = json_decode($response->getValue(), false);

        foreach ($events->value as $event) {
            $tourinsoftEvents[] = $event;
        }

        return $this->createEvents($tourinsoftEvents);
    }

    /**
     * Create Event Object from Tourinsoft Event
     *
     * @param Array $tourinsoftEvents array of Tourinsoft Events
     * @return Array array of events
     */
    public function createEvents($tourinsoftEvents)
    {
        //https://wcf.tourinsoft.com/Syndication/3.0/cdt11/8132036e-2b56-4710-a160-4737c6493c98/doc/syndication
        //http://api-doc.tourinsoft.com/#/syndication-3x#api-format

        $newEvents = [];

        foreach ($tourinsoftEvents as $event) {
            $newEvent = new Event();
            $newEvent->setExternalId($event->SyndicObjectID);
            $newEvent->setExternalSource(self::PROVIDER);

            if (isset($event->SyndicObjectName)) {
                $newEvent->setName($event->SyndicObjectName);
            } else {
                throw new Exception("Event name is mandatory", 1);
            }

            if (isset($event->PeriodeOuvertures) && is_array($event->PeriodeOuvertures) && count($event->PeriodeOuvertures) > 0) {
                // API v3: PeriodeOuvertures is an array of objects with Datedebut/Datefin in ISO 8601 format
                $firstPeriod = $event->PeriodeOuvertures[0];

                $startDate = new DateTime($firstPeriod->Datedebut);
                $endDate = new DateTime($firstPeriod->Datefin);

                // some events are annual so we check first if the year is up to date if not we set the actual year
                $year = $startDate->format('Y');
                $actualYear = (new \DateTime('now'))->format('Y');
                if ($year < $actualYear) {
                    $newEvent->setFromDate($startDate->setDate($actualYear, $startDate->format('m'), $startDate->format('d')));
                    $newEvent->setToDate($endDate->setDate($actualYear, $endDate->format('m'), $endDate->format('d')));
                } else {
                    $newEvent->setFromDate($startDate);
                    $newEvent->setToDate($endDate);
                }
            } else {
                throw new Exception("Start and end dates are mandatory", 1);
            }

            if (isset($event->ObjectTypeName)) {
                $newEvent->setDescription($event->ObjectTypeName);
            } else {
                throw new Exception("Description is mandatory", 1);
            }

            if (isset($event->Description)) {
                $newEvent->setFullDescription($event->Description);
            } else {
                throw new Exception("Description is mandatory", 1);
            }

            if (isset($event->Photoss) && is_array($event->Photoss) && count($event->Photoss) > 0) {
                // API v3: Photoss is an array of objects with Photo.Url
                $firstPhoto = $event->Photoss[0];
                if (isset($firstPhoto->Photo) && isset($firstPhoto->Photo->Url)) {
                    $newEvent->setExternalImageUrl($firstPhoto->Photo->Url);
                }
            }

            if (isset($event->MoyenDeComs) && is_array($event->MoyenDeComs)) {
                // API v3: MoyenDeComs is an array of objects with TypedaccesTelecom.ThesLibelle and CoordonneesTelecom
                foreach ($event->MoyenDeComs as $moyenDeCom) {
                    if (isset($moyenDeCom->TypedaccesTelecom) &&
                        isset($moyenDeCom->TypedaccesTelecom->ThesLibelle) &&
                        $moyenDeCom->TypedaccesTelecom->ThesLibelle === self::COMMUNICATION_MEDIA_WEBSITE_KEY &&
                        isset($moyenDeCom->CoordonneesTelecom)) {
                        $newEvent->setUrl($moyenDeCom->CoordonneesTelecom);
                        break;
                    }
                }
            }

            // we create and set the address
            $address = new Address();

            $fullStreetAddress = [];

            if (isset($event->Adresse1) && !empty(trim($event->Adresse1))) {
                $fullStreetAddress[] = $event->Adresse1;
            }
            if (isset($event->Adresse2) && !empty(trim($event->Adresse2))) {
                $fullStreetAddress[] = $event->Adresse2;
            }
            if (isset($event->Adresse3) && !empty(trim($event->Adresse3))) {
                $fullStreetAddress[] = $event->Adresse3;
            }

            $fullStreetAddressString = implode(" ", $fullStreetAddress);

            if (!empty($fullStreetAddressString)) {
                $address->setStreetAddress($fullStreetAddressString);
            } elseif (isset($event->LieuManifestation) && !empty($event->LieuManifestation)) {
                $address->setStreetAddress($event->LieuManifestation);
            }

            if (isset($event->Commune) && !empty($event->Commune)) {
                $address->setAddressLocality($event->Commune);
            }

            if (isset($event->CodePostal) && !empty($event->CodePostal)) {
                $address->setPostalCode($event->CodePostal);
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
        return  $newEvents;
    }
}
