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

/**
 * Event Provider for Apidae
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class ApidaeProvider implements EventProviderInterface
{
    const SERVER_URL = "https://api.apidae-tourisme.com/api/v002/recherche/list-objets-touristiques";
    const ID = "id";
    const NAME = "nom";
    const INFORMATIONS = "informations.moyensCommunication";
    const PICTURE = "illustrations";
    const SHORT_DESCRIPTION = "presentation.descriptifCourt";
    const FULL_DESCRIPTION = "presentation.descriptifDetaille";
    const START_DATE = "ouverture.periodesOuvertures.dateDebut";
    const END_DATE = "ouverture.periodesOuvertures.dateFin";
    const ADDRESS = "localisation.adresse";
    const GEOLOCATION = "localisation.geolocalisation";
    const PROVIDER = "Apidae";

    private $apiKey;
    private $projectId;
    private $serverUrl;
    private $selectionIds;

    public function __construct(string $apiKey, string $projectId, string $selectionIds)
    {
        $this->apiKey = $apiKey;
        $this->projectId = $projectId;
        $this->selectionIds = $selectionIds;
        $this->serverUrl = self::SERVER_URL;
    }

    public function getEvents()
    {
        $dataProvider = new DataProvider($this->serverUrl);

        $query = [];
        $query['apiKey'] = $this->apiKey;
        $query["projetId" ] = $this->projectId;
        $query["selectionIds" ] = [$this->selectionIds];
        $query["count" ] = 20;
        $query["first" ] = 1;
        $query["asc" ] = true;
        $query["responseFields" ] = [self::ID, self::NAME, self::INFORMATIONS, self::PICTURE, self::SHORT_DESCRIPTION, self::FULL_DESCRIPTION, self::START_DATE, self::END_DATE, self::ADDRESS, self::GEOLOCATION];

        $params = [
            "query" => json_encode($query)
        ];
        $response = $dataProvider->getItem($params);
        $events = json_decode($response->getValue(), true)["objetsTouristiques"];
        
        $newEvents = [];
        foreach ($events as $event) {
            $newEvent = new Event();
            $newEvent->setExternalId($event["id"]);
            $newEvent->setExternalSource(self::PROVIDER);
            $newEvent->setName($event["nom"]["libelleFr"]);
            
            $newEvent->setFromDate(new \DateTime($event["ouverture"]["periodesOuvertures"][0]["dateDebut"]));
            $newEvent->setToDate(new \DateTime($event["ouverture"]["periodesOuvertures"][0]["dateFin"]));

            $newEvent->setDescription($event["presentation"]["descriptifCourt"]["libelleFr"]);
            $newEvent->setFullDescription(isset($event["presentation"]["descriptifDetaille"][0]) ? $event["presentation"]["descriptifDetaille"]["libelleFr"] : $event["presentation"]["descriptifCourt"]["libelleFr"]);

            foreach ($event["informations"]["moyensCommunication"] as $communication) {
                if ($communication["type"]["libelleFr"] == "Site web (URL)") {
                    $newEvent->setUrl($communication["coordonnees"]["fr"]);
                }
            }
            
            $address = new Address();
            $address->setLatitude($event["localisation"]["geolocalisation"]["geoJson"]["coordinates"][1]);
            $address->setLongitude($event["localisation"]["geolocalisation"]["geoJson"]["coordinates"][0]);
            $address->setStreetAddress(isset($event["localisation"]["adresse"]["adresse1"]) ? $event["localisation"]["adresse"]["adresse1"] : $event["localisation"]["adresse"]["nomDuLieu"]);
            $address->setAddressLocality($event["localisation"]["adresse"]["commune"]["nom"]);
            $address->setPostalCode($event["localisation"]["adresse"]["codePostal"]);
            $address->setAddressCountry($event["localisation"]["adresse"]["commune"]["pays"]["libelleFr"]);
        
            $newEvent->setAddress($address);
            $newEvents[] = $newEvent;
        }
        
        return $newEvents;
    }

    public function getEvent()
    {
    }
}
