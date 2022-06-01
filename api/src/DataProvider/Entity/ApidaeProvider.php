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
use Exception;

/**
 * Event Provider for Apidae.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class ApidaeProvider implements EventProviderInterface
{
    public const SERVER_URL = 'https://api.apidae-tourisme.com/api/v002/recherche/list-objets-touristiques';

    public const ID = 'id';
    public const NAME = 'nom';
    public const INFORMATIONS = 'informations.moyensCommunication';
    public const PICTURE = 'illustrations';
    public const SHORT_DESCRIPTION = 'presentation.descriptifCourt';
    public const FULL_DESCRIPTION = 'presentation.descriptifDetaille';
    public const START_DATE = 'ouverture.periodesOuvertures.dateDebut';
    public const END_DATE = 'ouverture.periodesOuvertures.dateFin';
    public const ADDRESS = 'localisation.adresse';
    public const GEOLOCATION = 'localisation.geolocalisation';
    public const PROVIDER = 'Apidae';
    public const OBJECT_TYPE = 'objetsTouristiques';
    public const NUMBER_OF_EVENTS = 20;
    public const WEB_URL = 'Site web (URL)';

    public function __construct(string $apiKey, string $projectId, string $selectionIds)
    {
        $this->apiKey = $apiKey;
        $this->projectId = $projectId;
        $this->selectionIds = $selectionIds;
        $this->serverUrl = self::SERVER_URL;
    }

    /**
     * Get apidae events.
     *
     * @return array array of events
     */
    public function getEvents(): array
    {
        $dataProvider = new DataProvider($this->serverUrl);

        // we initialise number of iteration
        $j = 0;
        // we set an empty array of apidae events
        $apidaeEvents = [];
        // We call apidae api to get all events
        for ($i = 0; $i <= $j; ++$i) {
            $query = [];
            $query['apiKey'] = $this->apiKey;
            $query['projetId'] = $this->projectId;
            $query['selectionIds'] = [$this->selectionIds];
            $query['count'] = self::NUMBER_OF_EVENTS;
            // first indicate the index of the first event to get
            $query['first'] = self::NUMBER_OF_EVENTS * $i;
            $query['asc'] = true;
            $query['dateDebut'] = (new \DateTime('now'))->format('Y-m-d');
            $query['responseFields'] = [self::ID, self::NAME, self::INFORMATIONS, self::PICTURE, self::SHORT_DESCRIPTION, self::FULL_DESCRIPTION, self::START_DATE, self::END_DATE, self::ADDRESS, self::GEOLOCATION];

            $params = [
                'query' => json_encode($query),
            ];
            $response = $dataProvider->getItem($params);
            $events = json_decode($response->getValue(), false);
            // we calculate the number of iterations
            $j = ($events->numFound / self::NUMBER_OF_EVENTS) - 1;
            // we pass each event in the array
            foreach ($events->objetsTouristiques as $event) {
                $apidaeEvents[] = $event;
            }
        }

        return $this->createEvents($apidaeEvents);
    }

    /**
     * Get apiae event.
     */
    public function getEvent(): void
    {
    }

    /**
     * Create Event Object from Apidae Event.
     *
     * @param array $apidaeEvents array of Apidea Events
     *
     * @return array array of events
     */
    public function createEvents($apidaeEvents): array
    {
        // apidae data structuration : http://dev.apidae-tourisme.com/fr/documentation-technique/v2/formats-des-objets
        // http://dev.apidae-tourisme.com/fr/documentation-technique/v2/formats-des-objets/types-dobjet-touristique
        $newEvents = [];

        foreach ($apidaeEvents as $event) {
            $newEvent = new Event();
            $newEvent->setExternalId($event->id);
            $newEvent->setExternalSource(self::PROVIDER);
            if (isset($event->nom->libelleFr)) {
                $newEvent->setName($event->nom->libelleFr);
            } else {
                throw new Exception('Event name is mandatory', 1);
            }

            if (isset($event->ouverture->periodesOuvertures[0])) {
                // some events are annual so we check first if the year is up to date if not we set the actual year
                $year = (new \DateTime($event->ouverture->periodesOuvertures[0]->dateDebut))->format('Y');
                $startDate = new \DateTime($event->ouverture->periodesOuvertures[0]->dateDebut);
                $endDate = new \DateTime($event->ouverture->periodesOuvertures[0]->dateFin);
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

            if (isset($event->presentation->descriptifCourt->libelleFr)) {
                $newEvent->setDescription($event->presentation->descriptifCourt->libelleFr);
                $newEvent->setFullDescription(isset($event->presentation->descriptifDetaille->libelleFr) ? $event->presentation->descriptifDetaille->libelleFr : $event->presentation->descriptifCourt->libelleFr);
            } else {
                throw new Exception('Description is mandatory', 1);
            }
            $newEvent->setExternalImageUrl(isset($event->illustrations[0]) ? $event->illustrations[0]->traductionFichiers[0]->url : null);

            foreach ($event->informations->moyensCommunication as $communication) {
                if (self::WEB_URL == $communication->type->libelleFr) {
                    $newEvent->setUrl($communication->coordonnees->fr);
                }
            }
            // we create and set the address
            $address = new Address();

            if (isset($event->localisation->geolocalisation->geoJson)) {
                $address->setLatitude($event->localisation->geolocalisation->geoJson->coordinates[1]);
                $address->setLongitude($event->localisation->geolocalisation->geoJson->coordinates[0]);
            } else {
                throw new Exception('Latitude and longiture are mandatory', 1);
            }
            if (isset($event->localisation->adresse)) {
                $address->setStreetAddress(isset($event->localisation->adresse->adresse1) ? $event->localisation->adresse->adresse1 : (isset($event->localisation->adresse->nomDuLieu) ? $event->localisation->adresse->nomDuLieu : ''));
                $address->setAddressLocality(isset($event->localisation->adresse->commune->nom) ? $event->localisation->adresse->commune->nom : null);
                $address->setPostalCode(isset($event->localisation->adresse->codePostal) ? $event->localisation->adresse->codePostal : null);
                $address->setAddressCountry(isset($event->localisation->adresse->commune->pays->libelleFr) ? $event->localisation->adresse->commune->pays->libelleFr : null);
            }

            $newEvent->setAddress($address);

            // We pass the newEvent in array
            $newEvents[] = $newEvent;
        }

        return $newEvents;
    }
}
