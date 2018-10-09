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

namespace App\Carpool\Service;

use App\Carpool\Entity\Proposal;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Proposal manager service.
 * 
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ProposalManager
{
    private $entityManager;
    private $matchingAnalyzer;
    
    public function __construct(EntityManagerInterface $entityManager, MatchingAnalyzer $matchingAnalyzer)
    {
        $this->entityManager = $entityManager;
        $this->matchingAnalyzer = $matchingAnalyzer;
    }
    
    /**
     * Create a proposal.
     * 
     * @param Proposal $proposal
     */
    public function createProposal(Proposal $proposal)
    {
        $this->entityManager->persist($proposal);
        $this->entityManager->flush();
        $this->matchingAnalyzer->createMatchingsForProposal($proposal);
    }
    
}