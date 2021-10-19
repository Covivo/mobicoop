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

namespace App\Solidary\Admin\Service;

use App\Solidary\Entity\SolidaryUserStructure;
use App\Solidary\Admin\Service\SolidaryManager;

/**
 * Solidary carpool matcher in admin context.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class SolidaryCarpoolMatcher
{
    private $solidaryManager;

    /**
    * Constructor
    */
    public function __construct(SolidaryManager $solidaryManager)
    {
        $this->solidaryManager = $solidaryManager;
    }

    
    /**
     * Create all solidaryMatchings of a given solidaryUserStructure
     *
     * @param SolidaryUserStructure $solidaryUserStructure
     * @return void
     */
    public function createSolidaryMatching(SolidaryUserStructure $solidaryUserStructure)
    {
        foreach ($solidaryUserStructure->getSolidaries() as $solidary) {
            $solidary->getProposal()->getMatchingOffers();
            foreach ($solidary->getProposal()->getMatchingOffers() as $matching) {
                $this->solidaryManager->createSolidaryMatchingFromCarpoolMatching($matching, $matching->getProposalRequest()->getSolidary());
            }
        }
    }
}
