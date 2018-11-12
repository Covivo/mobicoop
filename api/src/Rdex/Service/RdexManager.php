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

namespace App\Rdex\Service;

use App\Carpool\Service\ProposalManager;
use App\Rdex\Entity\RdexJourney;

/**
 * Rdex operations manager.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class RdexManager
{
    private $proposalManager;
    
    public function __construct(ProposalManager $proposalManager)
    {
        $this->proposalManager = $proposalManager;
    }
    
    /**
     * Validates the parameters of a request.
     * 
     * @param array $parameters
     * @return bool
     */
    public function validate(array $parameters): bool {
        
        // we check the mandatory parameters
        if (is_null($parameters["timestamp"])) return [];
        if (is_null($parameters["apikey"])) return [];
        if (is_null($parameters["p"])) return [];
        if (is_null($parameters["signature"])) return [];
        
        return true;
    }
    
    /**
     * Get the journeys from the proposals.
     * 
     * @param array $parameters
     * @return array
     */
    public function getJourneys(array $parameters): array
    {
        $return = [];
        
        $proposals = $this->proposalManager->getProposals(
            $parameters["driver"], $parameters["passenger"], 
            $parameters["from"]["longitude"], $parameters["from"]["latitude"], 
            $parameters["to"]["longitude"], $parameters["to"]["latitude"], 
            isset($parameters["frequency"]) ? $parameters["frequency"] : null, 
            isset($parameters["outward"]["mindate"]) ? $parameters["outward"]["mindate"] : null, isset($parameters["outward"]["maxdate"]) ? $parameters["outward"]["maxdate"] : null, 
            isset($parameters["outward"]["monday"]["mintime"]) ? $parameters["outward"]["monday"]["mintime"] : null, isset($parameters["outward"]["monday"]["maxtime"]) ? $parameters["outward"]["monday"]["maxtime"] : null,
            isset($parameters["outward"]["tuesday"]["mintime"]) ? $parameters["outward"]["tuesday"]["mintime"] : null, isset($parameters["outward"]["tuesday"]["maxtime"]) ? $parameters["outward"]["tuesday"]["maxtime"] : null,
            isset($parameters["outward"]["wednesday"]["mintime"]) ? $parameters["outward"]["wednesday"]["mintime"] : null, isset($parameters["outward"]["wednesday"]["maxtime"]) ? $parameters["outward"]["wednesday"]["maxtime"] : null,
            isset($parameters["outward"]["thursday"]["mintime"]) ? $parameters["outward"]["thursday"]["mintime"] : null, isset($parameters["outward"]["thursday"]["maxtime"]) ? $parameters["outward"]["thursday"]["maxtime"] : null,
            isset($parameters["outward"]["friday"]["mintime"]) ? $parameters["outward"]["friday"]["mintime"] : null, isset($parameters["outward"]["friday"]["maxtime"]) ? $parameters["outward"]["friday"]["maxtime"] : null,
            isset($parameters["outward"]["saturday"]["mintime"]) ? $parameters["outward"]["saturday"]["mintime"] : null, isset($parameters["outward"]["saturday"]["maxtime"]) ? $parameters["outward"]["saturday"]["maxtime"] : null,
            isset($parameters["outward"]["sunday"]["mintime"]) ? $parameters["outward"]["sunday"]["mintime"] : null, isset($parameters["outward"]["sunday"]["maxtime"]) ? $parameters["outward"]["sunday"]["maxtime"] : null
            );
            
        foreach ($proposals as $proposal) {
            $journey = new RdexJourney($proposal->getId());
            $journey->setOperator(RdexJourney::OPERATOR);
            $journey->setOrigin(RdexJourney::ORIGIN);
            $return[] = $journey;
        }
        
        return $return;
    }
}
