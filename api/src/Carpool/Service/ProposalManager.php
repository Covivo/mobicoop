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
        // we will have to analyze the proposal to check the work to do (instead of simply persist the Proposal entity)
        // - proposalType : offer ? request ? both ?
        // - journeyType : one-way ? return trip ?
        
        // potentially we will create 4 proposals :
        $proposalOfferOutward = null;
        $proposalOfferReturn = null;
        $proposalRequestOutward = null;
        $proposalRequestReturn = null;
        
        // Proposal Type
        // offer/request or both ?
        if ($proposal->getProposalType() == Proposal::PROPOSAL_TYPE_OFFER || $proposal->getProposalType() == Proposal::PROPOSAL_TYPE_BOTH) {
            $proposalOfferOutward = clone $proposal;
            $proposalOfferOutward->setProposalType(Proposal::PROPOSAL_TYPE_OFFER);
            // criteria
            $proposalOfferOutward->setCriteria(clone $proposal->getCriteria());
            // points
            foreach ($proposal->getPoints() as $proposalPoint) {
                $point = clone $proposalPoint;
                // address
                $point->setAddress(clone $proposalPoint->getAddress());
                $proposalOfferOutward->addPoint($point);
            }
        }
        if ($proposal->getProposalType() == Proposal::PROPOSAL_TYPE_REQUEST || $proposal->getProposalType() == Proposal::PROPOSAL_TYPE_BOTH) {
            $proposalRequestOutward = clone $proposal;
            $proposalRequestOutward->setProposalType(Proposal::PROPOSAL_TYPE_REQUEST);
            // criteria
            $proposalRequestOutward->setCriteria(clone $proposal->getCriteria());
            // points
            foreach ($proposal->getPoints() as $proposalPoint) {
                $point = clone $proposalPoint;
                // address
                $point->setAddress(clone $proposalPoint->getAddress());
                $proposalRequestOutward->addPoint($point);
            }
        }
        
        // link between offer outward and request outward ?
        if (!is_null($proposalOfferOutward) && !is_null($proposalRequestOutward)) $proposalOfferOutward->setProposalLinked($proposalRequestOutward);
        
        // Journey Type
        // one way or outward/return ?
        $reversedPoints = [];
        $nbPoints = 0;
        if ($proposal->getJourneyType() == Proposal::JOURNEY_TYPE_OUTWARD) {
            // we will need the reverse points
            $nbPoints = count($proposal->getPoints());
            // we need to get the points in reverse order
            // we will read the points a first time to create an array with the position as index
            $apoints = [];
            foreach ($proposal->getPoints() as $proposalPoint) {
                $apoints[$proposalPoint->getPosition()] = $proposalPoint;
            }
            // we sort the array by key
            ksort($apoints);
            // our array is ordered by position, we read it backwards
            $reversedPoints = array_reverse($apoints);
        }
        
        if ($proposal->getJourneyType() == Proposal::JOURNEY_TYPE_OUTWARD && !is_null($proposalOfferOutward)) {
            $proposalOfferReturn = clone $proposal;
            $proposalOfferReturn->setProposalType(Proposal::PROPOSAL_TYPE_OFFER);
            $proposalOfferReturn->setJourneyType(Proposal::JOURNEY_TYPE_RETURN);
            // criteria
            $proposalOfferReturn->setCriteria(clone $proposal->getCriteria());
            foreach ($reversedPoints as $pos=>$proposalPoint) {
                $point = clone $proposalPoint;
                $point->setPosition($pos);
                $point->setLastPoint(false);
                // address
                $point->setAddress(clone $proposalPoint->getAddress());
                if ($pos == ($nbPoints-1)) $point->setLastPoint(true);
                $proposalOfferReturn->addPoint($point);
            }
            $proposalOfferOutward->setProposalLinkedJourney($proposalOfferReturn);
        }
        
        if ($proposal->getJourneyType() == Proposal::JOURNEY_TYPE_OUTWARD && !is_null($proposalRequestOutward)) {
            $proposalRequestReturn = clone $proposal;
            $proposalRequestReturn->setProposalType(Proposal::PROPOSAL_TYPE_REQUEST);
            $proposalRequestReturn->setJourneyType(Proposal::JOURNEY_TYPE_RETURN);
            // criteria
            $proposalRequestReturn->setCriteria(clone $proposal->getCriteria());
            foreach ($reversedPoints as $pos=>$proposalPoint) {
                $point = clone $proposalPoint;
                $point->setPosition($pos);
                $point->setLastPoint(false);
                // address
                $point->setAddress(clone $proposalPoint->getAddress());
                if ($pos == ($nbPoints-1)) $point->setLastPoint(true);
                $proposalRequestReturn->addPoint($point);
            }
            $proposalRequestOutward->setProposalLinkedJourney($proposalRequestReturn);
        }
        
        // link between offer return and request return
        if (!is_null($proposalOfferReturn) && !is_null($proposalRequestReturn)) $proposalOfferReturn->setProposalLinked($proposalRequestReturn);
        
        // persistence
        if (!is_null($proposalOfferOutward)) $this->entityManager->persist($proposalOfferOutward);
        if (!is_null($proposalOfferReturn)) $this->entityManager->persist($proposalOfferReturn);
        if (!is_null($proposalRequestOutward)) $this->entityManager->persist($proposalRequestOutward);
        if (!is_null($proposalRequestReturn)) $this->entityManager->persist($proposalRequestReturn);
        $this->entityManager->flush();
        
        // matching analyze 
        // => should be replaced by path analyzer when it's created
        // => the analyze would be asked when all paths are analyzed and returned
        if (!is_null($proposalOfferOutward)) $this->matchingAnalyzer->createMatchingsForProposal($proposalOfferOutward);
        if (!is_null($proposalOfferReturn)) $this->matchingAnalyzer->createMatchingsForProposal($proposalOfferReturn);
        if (!is_null($proposalRequestOutward)) $this->matchingAnalyzer->createMatchingsForProposal($proposalRequestOutward);
        if (!is_null($proposalRequestReturn)) $this->matchingAnalyzer->createMatchingsForProposal($proposalRequestReturn);
        
        // return the proposal (not really necessary, but good practice ?)
        if (!is_null($proposalOfferOutward)) return $proposalOfferOutward;
        if (!is_null($proposalRequestOutward)) return $proposalRequestOutward;
    }
        
}