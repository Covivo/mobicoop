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

use Doctrine\Common\Collections\ArrayCollection;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\Mass;
use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\MassCarpool;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\MassJourney;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\MassMatching;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\MassMatrix;
use Mobicoop\Bundle\MobicoopBundle\Match\Entity\MassPerson;
use Mobicoop\Bundle\MobicoopBundle\Service\UtilsService;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;

/**
 * Mass management service.
 */
class MassManager
{
    private const MIN_OVERLAP_RATIO = 0.005;
    private const MAX_SUPERIOR_DISTANCE_RATIO = 1.5;

    private $dataProvider;
    private $userManager;

    /**
     * Constructor.
     * @param DataProvider $dataProvider The data provider that provides the Mass
     */
    public function __construct(DataProvider $dataProvider, UserManager $userManager)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Mass::class);
        $this->userManager = $userManager;
    }
    
    /**
     * Get a Mass
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
            if ($mass->getStatus()>=4) {
            }
            return $mass;
        }
        return null;
    }
    
    /**
     * Create a mass
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
     * Delete a mass
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
     * Analyze a Mass
     *
     * @param int $id The mass id
     *
     * @return Mass|null The mass read or null if error.
     */
    public function analyzeMass(int $id)
    {
        $response = $this->dataProvider->getSpecialItem($id, "analyze");
        return $response->getValue();
    }

    /**
     * reAnalyze a Mass
     *
     * @param int $id The mass id
     *
     * @return Mass|null The mass read or null if error.
     */
    public function reAnalyzeMass(int $id)
    {
        $response = $this->dataProvider->getSpecialItem($id, "reanalyze");
        return $response->getValue();
    }

    /**
     * Calculate a Mass (calculation of matchings)
     *
     * @param int $id The mass id
     *
     * @return Mass|null The mass read or null if error.
     */
    public function matchMass(int $id)
    {
        $params = [
            'minOverlapRatio'=>self::MIN_OVERLAP_RATIO,
            'maxSuperiorDistanceRatio'=>self::MAX_SUPERIOR_DISTANCE_RATIO
        ];
        $response = $this->dataProvider->getSpecialItem($id, "match", $params);
        return $response->getValue();
    }

    /**
     * reCalculate a Mass (calculation of matchings)
     *
     * @param int $id The mass id
     *
     * @return Mass|null The mass read or null if error.
     */
    public function reMatchMass(int $id)
    {
        $params = [
            'minOverlapRatio'=>self::MIN_OVERLAP_RATIO,
            'maxSuperiorDistanceRatio'=>self::MAX_SUPERIOR_DISTANCE_RATIO
        ];
        $response = $this->dataProvider->getSpecialItem($id, "rematch", $params);
        return $response->getValue();
    }

    /**
     * Compute all data of a Mass
     *
     * @param int $id The mass id
     *
     * @return Mass|null The mass read or null if error.
     */
    public function computeMass(int $id)
    {
        $response = $this->dataProvider->getSpecialItem($id, "compute");
        return $response->getValue();
    }

    /**
     * Get all different working places of a Mass
     *
     * @param int $id The mass id
     *
     * @return array|null The mass read or null if error.
     */
    public function workingPlacesMass(int $id)
    {
        $response = $this->dataProvider->getSpecialItem($id, "workingplaces");
        return $response->getValue();
    }
}
