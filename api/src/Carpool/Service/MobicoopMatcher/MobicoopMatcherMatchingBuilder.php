<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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
 */

namespace App\Carpool\Service\MobicoopMatcher;

use App\Carpool\Entity\Matching;
use App\Carpool\Entity\MobicoopMatcher\Waypoint;
use App\Carpool\Entity\Proposal;
use App\Carpool\Repository\ProposalRepository;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class MobicoopMatcherMatchingBuilder
{
    /**
     * @var Matching
     */
    private $_matching;

    private $_proposal;
    private $_result;
    private $_proposalRepository;
    private $_maxDetourDistancePercent;
    private $_maxDetourDurationPercent;
    private $_criteriaBuilder;
    private $_waypointExtractor;
    private $_classicWaypointsBuilder;
    private $_computingTools;

    public function __construct(
        int $maxDetourDistancePercent,
        int $maxDetourDurationPercent,
        ProposalRepository $proposalRepository,
        MobicoopMatcherCriteriaBuilder $criteriaBuilder,
        MobicoopMatcherClassicWaypointsBuilder $classicWaypointsBuilder,
        MobicoopMatcherComputingTools $computingTools
    ) {
        $this->_proposalRepository = $proposalRepository;
        $this->_criteriaBuilder = $criteriaBuilder;
        $this->_maxDetourDistancePercent = $maxDetourDistancePercent;
        $this->_maxDetourDurationPercent = $maxDetourDurationPercent;
        $this->_classicWaypointsBuilder = $classicWaypointsBuilder;
        $this->_computingTools = $computingTools;
    }

    public function build(Proposal $proposal, array $result): Matching
    {
        $this->_proposal = $proposal;
        $this->_result = $result;
        $this->_waypointExtractor = new MobicoopMatcherWaypointExtractor($this->_result['journeys'][0]['waypoints']);

        $this->_matching = new Matching();
        $this->_matching->setCreatedDate(new \DateTime('now'));
        $this->_treatProposals();
        $this->_treatDistances();
        $this->_treatDurations();
        $this->_treatPickUpsAndDropOffsDurations();
        $this->_matching->setCriteria($this->_criteriaBuilder->build($proposal, $this->_result, $this->_matching));
        $this->_classicWaypointsBuilder->build($this->_matching, $this->_result['journeys'][0]['waypoints']);

        return $this->_matching;
    }

    private function _treatProposals()
    {
        $matchingProposal = $this->_proposalRepository->find($this->_result['proposal']);
        if (Waypoint::ROLE_DRIVER == $this->_result['role']) {
            $this->_matching->setProposalOffer($matchingProposal);
            $this->_matching->setProposalRequest($this->_proposal);
        } else {
            $this->_matching->setProposalRequest($matchingProposal);
            $this->_matching->setProposalOffer($this->_proposal);
        }
    }

    private function _treatDistances()
    {
        $this->_matching->setOriginalDistance($this->_result['initial_distance']);
        $this->_matching->setNewDistance($this->_result['final_distance']);
        $this->_matching->setDetourDistance($this->_result['final_distance'] - $this->_result['initial_distance']);
        $this->_matching->setDetourDistancePercent(100 * $this->_matching->getDetourDistance() / $this->_result['initial_distance']);
        $this->_matching->setAcceptedDetourDistance($this->_result['initial_distance'] * $this->_maxDetourDistancePercent / 100);

        // TO DO : CommonDistance
        // Ajouter dans le matcher la distance parcourue à chaque waypoint : si possible on calcul avec distance à DEPOSE – distance à PEC
        $this->_matching->setCommonDistance($this->_result['final_distance']);
    }

    private function _treatDurations()
    {
        $this->_matching->setOriginalDuration($this->_result['initial_duration']);
        $this->_matching->setNewDuration($this->_result['final_duration']);
        $this->_matching->setDetourDuration($this->_result['final_duration'] - $this->_result['initial_duration']);
        $this->_matching->setDetourDurationPercent(100 * $this->_matching->getDetourDuration() / $this->_result['initial_duration']);
        $this->_matching->setAcceptedDetourDuration($this->_result['initial_duration'] * $this->_maxDetourDurationPercent / 100);
    }

    private function _treatPickUpsAndDropOffsDurations()
    {
        $firstWaypoint = $this->_waypointExtractor->findFirstWaypoint();
        $pickUpPoint = $this->_waypointExtractor->findPickUpPoint();

        $this->_matching->setPickUpDuration($this->_computingTools->computeElapsedTimeInSeconds($firstWaypoint['time'], $pickUpPoint['time']));

        $dropOffPoint = $this->_waypointExtractor->findDropOffPoint();

        $this->_matching->setDropOffDuration($this->_computingTools->computeElapsedTimeInSeconds($firstWaypoint['time'], $dropOffPoint['time']));
    }
}
