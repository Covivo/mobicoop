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

namespace App\Carpool\Service;

use App\Carpool\Entity\AntiFraudResponse;
use App\Carpool\Entity\Criteria;
use App\Carpool\Repository\ProposalRepository;
use App\Carpool\Ressource\Ad;
use App\Geography\Entity\Address;
use App\Geography\Service\GeoRouter;
use App\User\Service\UserManager;
use App\Carpool\Exception\AntiFraudException;

/**
 * Anti-Fraud system manager service.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class AntiFraudManager
{
    private $geoRouter;
    private $proposalRepository;
    private $userManager;

    // Parameters
    private $distanceMinCheck;
    private $nbCarpoolsMax;
    private $active;
    
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        GeoRouter $geoRouter,
        ProposalRepository $proposalRepository,
        UserManager $userManager,
        array $params
    ) {
        $this->geoRouter = $geoRouter;
        $this->proposalRepository = $proposalRepository;
        $this->userManager = $userManager;
        $this->distanceMinCheck = $params['distanceMinCheck'];
        $this->nbCarpoolsMax = $params['nbCarpoolsMax'];
        $this->active = $params['active'];
    }

    /**
     * Check if an Ad is valid against the Anti-Fraud system rules
     * If the Anti-Fraud system is inactive, or the Ad is a Search or the role is passenger only, it's an automatic validation
     *
     * @param Ad $ad                The Ad to check
     * @return AntiFraudResponse    The response
     */
    public function validAd(Ad $ad): AntiFraudResponse
    {
        // Default response is that the Ad is valid
        $response = new AntiFraudResponse(true, AntiFraudException::OK);
        
        // If the Anti-Fraud system is inactive, or the Ad is a Search or the role is passenger only, it's an automatic validation
        if (!$this->active || $ad->getRole() == Ad::ROLE_PASSENGER || $ad->isSearch()) {
            return $response;
        }
        
        // Compute the distance of the journey
        
        $addressesToValidate = [];
        foreach ($ad->getOutwardWaypoints() as $pointToValidate) {
            $waypointToValidate = new Address();
            if(is_array($pointToValidate)){
                $waypointToValidate->setLatitude($pointToValidate['latitude']);
                $waypointToValidate->setLongitude($pointToValidate['longitude']);
            }
            else{
                $waypointToValidate->setLatitude($pointToValidate->getLatitude());
                $waypointToValidate->setLongitude($pointToValidate->getLongitude());
            }
            $addressesToValidate[] = $waypointToValidate;
        }
        

        $route = $this->geoRouter->getRoutes($addressesToValidate, false, true);
        // var_dump($route);
        
        // If the journey is above the $distanceMinCheck paramaters we need to check it otherwise, it's an immediate validation
        if (($route[0]->getDistance()/1000) > $this->distanceMinCheck) {

            /****************** FIRST CHECK ********************** */
            $response = $this->validAdFirstCheck($ad);
            if (!$response->isValid()) {
                return $response;
            }
        }


        return $response;
    }

    /**
     * Anti Fraud System first check - Max number of journeys
     * A user can only have $nbCarpoolsMax on the same day
     *
     * @param Ad $ad
     * @return AntiFraudResponse
     */
    private function validAdFirstCheck(Ad $ad): AntiFraudResponse
    {
        // By default, the outward date is immutable, we need to make a regular Datetime
        $dateTime = new \DateTime(null, $ad->getOutwardDate()->getTimezone());
        $dateTime->setTimestamp($ad->getOutwardDate()->getTimestamp());

        // Setup the User if it exists
        $user = null;
        if (!is_null($ad->getUser())) {
            $user = $ad->getUser();
        } elseif (!is_null($ad->getUserId())) {
            $user = $this->userManager->getUser($ad->getUserId());
        }


        $proposals = $this->proposalRepository->findByDate($dateTime, $user, true);

        if (!is_null($proposals) && is_array($proposals) && count($proposals)>=$this->nbCarpoolsMax) {
            return new AntiFraudResponse(false, AntiFraudException::TOO_MANY_AD);
        }

        return new AntiFraudResponse(true, AntiFraudException::OK);
    }
}
