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

use Doctrine\ORM\EntityManagerInterface;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Matching;
use App\Carpool\Entity\Criteria;

/**
 * Matching analyzer service.
 * 
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class MatchingAnalyzer
{
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Find matching proposals for a proposal.
     * 
     * @param Proposal $proposal
     * @return mixed|\Doctrine\DBAL\Driver\Statement|array|NULL
     */
    public function findMatchingProposals(Proposal $proposal)
    {
        return $this->entityManager->getRepository(Proposal::class)->findMatchingProposals($proposal);
    }
    
    /**
     * Create Matching proposal entities for a proposal.
     *  
     * @param Proposal $proposal    The proposal for which we want the matchings
     */
    public function createMatchingsForProposal(Proposal $proposal) 
    {
        $proposals = $this->findMatchingProposals($proposal);
        foreach ($proposals as $matchingProposal) {
            $matching = new Matching();
            if ($proposal->getProposalType() == Proposal::PROPOSAL_TYPE_OFFER) {
                $matching->setProposalOffer($proposal);
                $matching->setProposalRequest($matchingProposal);
                // if the matching already exists between the proposal and the matchingProposal => we jump to the next proposal
                if (!is_null($this->entityManager->getRepository(Matching::class)->findOneBy([
                        'proposalOffer'     => $proposal,
                        'proposalRequest'   => $matchingProposal
                ]))) break;
                // for now we just set the points to the start and destination points
                foreach ($proposal->getPoints() as $point) {
                    if ($point->getPosition() == 0) $matching->setPointOfferFrom($point);
                    if ($point->getLastPoint()) $matching->setPointOfferTo($point);
                }
                foreach ($matchingProposal->getPoints() as $point) {
                    if ($point->getPosition() == 0) {
                        $matching->setPointRequestFrom($point);
                        break;
                    }
                }
            } else {
                $matching->setProposalOffer($matchingProposal);
                $matching->setProposalRequest($proposal);
                // if the matching already exists between the proposal and the matchingProposal => we jump to the next proposal
                if (!is_null($this->entityManager->getRepository(Matching::class)->findOneBy([
                        'proposalOffer'     => $matchingProposal,
                        'proposalRequest'   => $proposal
                ]))) break;
                // for now we just set the points to the start and destination points
                foreach ($matchingProposal->getPoints() as $point) {
                    if ($point->getPosition() == 0) $matching->setPointOfferFrom($point);
                    if ($point->getLastPoint()) $matching->setPointOfferTo($point);
                }
                foreach ($proposal->getPoints() as $point) {
                    if ($point->getPosition() == 0) {
                        $matching->setPointRequestFrom($point);
                        break;
                    }
                }
            }
            
            $matchingCriteria = new Criteria();
            // for now we just clone some properties of the proposal criteria
            // in the future when the algorythm will be more efficient we will create a criteria based on most matching properties between the proposals
            $matchingCriteria->clone($proposal->getCriteria());
            $matching->setCriteria($matchingCriteria);
            $this->entityManager->persist($matching);
        }
        $this->entityManager->flush();
    }

}