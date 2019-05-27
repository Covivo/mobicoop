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

namespace Mobicoop\Bundle\MobicoopBundle\Match\Service;

use Mobicoop\Bundle\MobicoopBundle\Match\Entity\Mass;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Service\UtilsService;

/**
 * Mass management service.
 */
class MassManager
{
    private $dataProvider;
    
    /**
     * Constructor.
     * @param DataProvider $dataProvider    The data provider that provides the images
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Mass::class);
    }
    
    /**
     * Get an Mass
     *
     * @param int $id The mass id
     *
     * @return Mass|null The mass read or null if error.
     */
    public function getMass(int $id)
    {
        $response = $this->dataProvider->getItem($id);
        if ($response->getCode() == 200) {
            $mass = $response->getValue();
            $this->computeResults($mass);
            return $mass;
        }
        return null;
    }
    
    /**
     * Create an mass
     *
     * @param Mass $mass The mass to create
     *
     * @return Mass|null The mass created or null if error.
     */
    public function createMass(Mass $mass)
    {
        $response = $this->dataProvider->postMultiPart($mass);
        if ($response->getCode() == 201) {
            return $response->getValue();
        }
        return null;
    }
    
    /**
     * Delete an mass
     *
     * @param int $id The id of the mass to delete
     *
     * @return boolean The result of the deletion.
     */
    public function deleteMass(int $id)
    {
        $response = $this->dataProvider->delete($id);
        if ($response->getCode() == 204) {
            return true;
        }
        return false;
    }

    /**
     * Compute all necessary calculations for a mass
     *
     * @param Mass $mass
     * @return null
     */
    public function computeResults(Mass $mass)
    {

        $computedData = [
            "totalTravelDistance" => 0,
            "averageTravelDistance" => 0,
            "totalTravelDuration" => 0,
            "averageTravelDuration" => 0
        ];

        $persons = $mass->getPersons();

        $tabCoords = array();
        foreach($persons as $person){
            $tabCoords[] = array(
                "latitude"=>$person->getPersonalAddress()->getLatitude(),
                "longitude"=>$person->getPersonalAddress()->getLongitude(),
                "distance"=>$person->getDirection()->getDistance(),
                "duration"=>$person->getDirection()->getDuration()
            );
            $computedData["totalTravelDistance"] += $person->getDirection()->getDistance();
            $computedData["totalTravelDuration"] += $person->getDirection()->getDuration();
        }

        $mass->setPersonsCoords($tabCoords);

        // Enregistrement du lieu de travail
        $mass->setLatWorkingPlace($persons[0]->getWorkAddress()->getLatitude());
        $mass->setLonWorkingPlace($persons[0]->getWorkAddress()->getLongitude());

        // Calcul des moyennes
        $computedData["averageTravelDistance"] = $computedData["totalTravelDistance"] / count($persons);
        $computedData["averageTravelDuration"] = $computedData["totalTravelDuration"] / count($persons);

        // Calcul des affichages "humains" des durées (heurs, minutes, secondes)
        $computedData["humanTotalTravelDuration"] = UtilsService::convertSecondsToHumain($computedData["totalTravelDuration"]);
        $computedData["humanAverageTravelDuration"] = UtilsService::convertSecondsToHumain($computedData["averageTravelDuration"]);

        $mass->setComputedData($computedData);

        return null;
    }

}
