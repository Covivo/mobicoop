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

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use App\Service\ProposalManager;
use App\Entity\Proposal;
use App\Service\MatchingAnalyzer;

/**
 * Controller class for proposal post.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ProposalPost
{
    private $proposalManager;
    private $matchingAnalyzer;

    public function __construct(ProposalManager $proposalManager, MatchingAnalyzer $matchingAnalyzer)
    {
        $this->proposalManager = $proposalManager;
        $this->matchingAnalyzer = $matchingAnalyzer;
    }

    public function __invoke(Proposal $data): Proposal
    {
        $this->proposalManager->createProposal($data);
        $this->matchingAnalyzer->findMatchingProposals($data);
        return $data;
    }
    
}