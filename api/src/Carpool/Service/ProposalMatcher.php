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
use App\Carpool\Repository\ProposalRepository;
use App\Match\Service\GeoMatcher;
use App\Match\Service\TimeMatcher;
use App\Match\Entity\Candidate;

/**
 * Matching analyzer service.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ProposalMatcher
{
    // max default detour distance
    // TODO : should depend on the total distance : total distance => max detour allowed
    private const MAX_DETOUR_DISTANCE_PERCENT = 40;
    private const MAX_DETOUR_DURATION_PERCENT = 40;

    private $entityManager;
    private $proposalRepository;
    private $geoMatcher;
    private $timeMatcher;
    
    public function __construct(EntityManagerInterface $entityManager, ProposalRepository $proposalRepository, GeoMatcher $geoMatcher, TimeMatcher $timeMatcher)
    {
        $this->entityManager = $entityManager;
        $this->proposalRepository = $proposalRepository;
        $this->geoMatcher = $geoMatcher;
        $this->timeMatcher = $timeMatcher;
    }
    
    /**
     * Find matching proposals for a proposal.
     *
     * @param Proposal $proposal
     * @return mixed|\Doctrine\DBAL\Driver\Statement|array|NULL
     */
    public function findMatchingProposals(Proposal $proposal)
    {
        // we search matching proposals in the database
        // if not proposals are found we return an empty array
        if (!$proposalsFound = $this->proposalRepository->findMatchingProposals($proposal)) {
            return [];
        }

        $proposals = [];

        // we filter with geomatcher
        $candidateProposal = new Candidate();
        $addresses = [];
        foreach ($proposal->getWaypoints() as $waypoint) {
            $addresses[] = $waypoint->getAddress();
        }
        $candidateProposal->setAddresses($addresses);

        if ($proposal->getCriteria()->isDriver()) {
            $candidateProposal->setMaxDetourDistance($proposal->getCriteria()->getMaxDetourDistance() ? $proposal->getCriteria()->getMaxDetourDistance() : ($proposal->getCriteria()->getDirectionDriver()->getDistance()*self::MAX_DETOUR_DISTANCE_PERCENT/100));
            $candidateProposal->setMaxDetourDuration($proposal->getCriteria()->getMaxDetourDuration() ? $proposal->getCriteria()->getMaxDetourDuration() : ($proposal->getCriteria()->getDirectionDriver()->getDuration()*self::MAX_DETOUR_DURATION_PERCENT/100));
            $candidateProposal->setDirection($proposal->getCriteria()->getDirectionDriver());
            foreach ($proposalsFound as $proposalToMatch) {
                $candidate = new Candidate();
                $addressesCandidate = [];
                foreach ($proposalToMatch->getWaypoints() as $waypoint) {
                    $addressesCandidate[] = $waypoint->getAddress();
                }
                $candidate->setAddresses($addressesCandidate);
                $candidate->setDirection($proposalToMatch->getCriteria()->getDirectionPassenger());
                // the 2 following are not taken in account right now as only the driver detour matters
                $candidate->setMaxDetourDistance($proposalToMatch->getCriteria()->getMaxDetourDistance() ? $proposalToMatch->getCriteria()->getMaxDetourDistance() : ($proposalToMatch->getCriteria()->getDirectionPassenger()->getDistance()*self::MAX_DETOUR_DISTANCE_PERCENT/100));
                $candidate->setMaxDetourDuration($proposalToMatch->getCriteria()->getMaxDetourDuration() ? $proposalToMatch->getCriteria()->getMaxDetourDuration() : ($proposalToMatch->getCriteria()->getDirectionPAssenger()->getDuration()*self::MAX_DETOUR_DURATION_PERCENT/100));
                if ($matches = $this->geoMatcher->singleMatch($candidateProposal, [$candidate], true)) {
                    if (is_array($matches) && count($matches)>0) {
                        $proposals[] = [
                            "role" => "driver",
                            "proposal" => $proposalToMatch,
                            "match" => $matches[0]
                        ];
                    }
                }
            }
        }

        if ($proposal->getCriteria()->isPassenger()) {
            $candidateProposal->setDirection($proposal->getCriteria()->getDirectionPassenger());
            // the 2 following are not taken in account right now as only the driver detour matters
            $candidateProposal->setMaxDetourDistance($proposal->getCriteria()->getMaxDetourDistance() ? $proposal->getCriteria()->getMaxDetourDistance() : ($proposal->getCriteria()->getDirectionPassenger()->getDistance()*self::MAX_DETOUR_DISTANCE_PERCENT/100));
            $candidateProposal->setMaxDetourDuration($proposal->getCriteria()->getMaxDetourDuration() ? $proposal->getCriteria()->getMaxDetourDuration() : ($proposal->getCriteria()->getDirectionPassenger()->getDuration()*self::MAX_DETOUR_DURATION_PERCENT/100));
            foreach ($proposalsFound as $proposalToMatch) {
                $candidate = new Candidate();
                $addressesCandidate = [];
                foreach ($proposalToMatch->getWaypoints() as $waypoint) {
                    $addressesCandidate[] = $waypoint->getAddress();
                }
                $candidate->setAddresses($addressesCandidate);
                $candidate->setDirection($proposalToMatch->getCriteria()->getDirectionDriver());
                $candidate->setMaxDetourDistance($proposalToMatch->getCriteria()->getMaxDetourDistance() ? $proposalToMatch->getCriteria()->getMaxDetourDistance() : ($proposalToMatch->getCriteria()->getDirectionDriver()->getDistance()*self::MAX_DETOUR_DISTANCE_PERCENT/100));
                $candidate->setMaxDetourDuration($proposalToMatch->getCriteria()->getMaxDetourDuration() ? $proposalToMatch->getCriteria()->getMaxDetourDuration() : ($proposalToMatch->getCriteria()->getDirectionDriver()->getDuration()*self::MAX_DETOUR_DURATION_PERCENT/100));
                if ($matches = $this->geoMatcher->singleMatch($candidateProposal, [$candidate], false)) {
                    if (is_array($matches) && count($matches)>0) {
                        $proposals[] = [
                            "role" => "passenger",
                            "proposal" => $proposalToMatch,
                            "match" => $matches[0]
                        ];
                    }
                }
            }
        }

        return $proposals;
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
                ]))) {
                    break;
                }
                
                // for now we just set the points to the start and destination points
                foreach ($proposal->getPoints() as $point) {
                    if ($point->getPosition() == 0) {
                        $matching->setPointOfferFrom($point);
                    }
                    if ($point->getLastPoint()) {
                        $matching->setPointOfferTo($point);
                    }
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
                ]))) {
                    break;
                }
                // for now we just set the points to the start and destination points
                foreach ($matchingProposal->getPoints() as $point) {
                    if ($point->getPosition() == 0) {
                        $matching->setPointOfferFrom($point);
                    }
                    if ($point->getLastPoint()) {
                        $matching->setPointOfferTo($point);
                    }
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
            // in the future when the algorythm will be more efficient we will create a criteria based on most matching properties between the proposals criteria
            $matchingCriteria->clone($proposal->getCriteria());
            $matching->setCriteria($matchingCriteria);
            $this->entityManager->persist($matching);
        }
        $this->entityManager->flush();
    }
}
