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
    
    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ProposalRepository $proposalRepository
     * @param GeoMatcher $geoMatcher
     * @param TimeMatcher $timeMatcher
     */
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
        // if no proposals are found we return an empty array
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
                // if the candidate is not passenger we skip (the 2 candidates could be driver AND passenger, and the second one match only as a driver)
                if (!$proposalToMatch->getCriteria()->isPassenger()) {
                    continue;
                }
                $candidate = new Candidate();
                $addressesCandidate = [];
                foreach ($proposalToMatch->getWaypoints() as $waypoint) {
                    $addressesCandidate[] = $waypoint->getAddress();
                }
                $candidate->setAddresses($addressesCandidate);
                $candidate->setDirection($proposalToMatch->getCriteria()->getDirectionPassenger());
                // the 2 following are not taken in account right now as only the driver detour matters
                $candidate->setMaxDetourDistance($proposalToMatch->getCriteria()->getMaxDetourDistance() ? $proposalToMatch->getCriteria()->getMaxDetourDistance() : ($proposalToMatch->getCriteria()->getDirectionPassenger()->getDistance()*self::MAX_DETOUR_DISTANCE_PERCENT/100));
                $candidate->setMaxDetourDuration($proposalToMatch->getCriteria()->getMaxDetourDuration() ? $proposalToMatch->getCriteria()->getMaxDetourDuration() : ($proposalToMatch->getCriteria()->getDirectionPassenger()->getDuration()*self::MAX_DETOUR_DURATION_PERCENT/100));
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
                // if the candidate is not driver we skip (the 2 candidates could be driver AND passenger, and the second one match only as a passenger)
                if (!$proposalToMatch->getCriteria()->isDriver()) {
                    continue;
                }
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

        // we check if the pickup times match
        $proposals = $this->checkPickUp($proposal,$proposals);

        return $proposals;
    }

    /**
     * Check that pickup times are valid against the given proposals.
     *
     * @param Proposal $proposal    The proposal 
     * @param array $proposals      The candidates
     * @return void
     */
    private function checkPickUp(Proposal $proposal, array $proposals) 
    {
        $validProposals = [];
        foreach ($proposals as $candidate) {
            $pickupDuration = null;
            $pickupTimes = [];
            foreach ($candidate['match'][0]['order'] as $value) {
                if ($value['candidate'] == 2 && $value['position'] == 0) {
                    $pickupDuration = (int)round($value['duration']);
                    break;
                }
            }
            if ($candidate['role'] == 'driver') {
                // driver proposal
                $pickupTimes = $this->getPickupTimes($proposal,$candidate['proposal'],$pickupDuration);
            } else {
                // passenger proposal
                $pickupTimes = $this->getPickupTimes($proposal,$candidate['proposal'],$pickupDuration);
            }
            if (count($pickupTimes)>0) {
                $candidate['match']['pickup'] = $pickupTimes;
                $validProposals[] = $candidate;
            }
        }
        return $validProposals;
    }

    /**
     * Get the pickup times for the given proposals
     *
     * @param Proposal $proposal1   The driver proposal
     * @param Proposal $proposal2   The passenger proposal
     * @param integer $pickupDuration   The duration from the origin to the pickup point
     * @return void
     */
    private function getPickupTimes(Proposal $proposal1, Proposal $proposal2, int $pickupDuration)
    {
        $minPickupTime = null;
        $maxPickupTime = null;
        $monMinPickupTime = null;
        $monMaxPickupTime = null;
        $tueMinPickupTime = null;
        $tueMaxPickupTime = null;
        $wedMinPickupTime = null;
        $wedMaxPickupTime = null;
        $thuMinPickupTime = null;
        $thuMaxPickupTime = null;
        $friMinPickupTime = null;
        $friMaxPickupTime = null;
        $satMinPickupTime = null;
        $satMaxPickupTime = null;
        $sunMinPickupTime = null;
        $sunMaxPickupTime = null;
        
        switch ($proposal1->getCriteria()->getFrequency()) {
            case Criteria::FREQUENCY_PUNCTUAL : {
                $minPickupTime = clone $proposal1->getCriteria()->getMinTime();
                $maxPickupTime = clone $proposal1->getCriteria()->getMaxTime();
                $minPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                $maxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                switch ($proposal2->getCriteria()->getFrequency()) {
                    case Criteria::FREQUENCY_PUNCTUAL : {
                        if (!(
                            ($minPickupTime>=$proposal2->getCriteria()->getMinTime() && $minPickupTime<=$proposal2->getCriteria()->getMaxTime()) || 
                            ($maxPickupTime>=$proposal2->getCriteria()->getMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getMaxTime()) 
                        ))  {
                            // not in range 
                            $minPickupTime = null;
                            $maxPickupTime = null;
                        }
                        break;
                    }
                    case Criteria::FREQUENCY_REGULAR : {
                        switch ($proposal1->getCriteria()->getFromDate()->format('w')) {
                            case 0 : {
                                if (!(
                                    ($minPickupTime>=$proposal2->getCriteria()->getSunMinTime() && $minPickupTime<=$proposal2->getCriteria()->getSunMaxTime()) || 
                                    ($maxPickupTime>=$proposal2->getCriteria()->getSunMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getSunMaxTime()) 
                                ))  {
                                    // not in range 
                                    $minPickupTime = null;
                                    $maxPickupTime = null;
                                }
                                break;
                            }
                            case 1 : {
                                if (!(
                                    ($minPickupTime>=$proposal2->getCriteria()->getMonMinTime() && $minPickupTime<=$proposal2->getCriteria()->getMonMaxTime()) || 
                                    ($maxPickupTime>=$proposal2->getCriteria()->getMonMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getMonMaxTime()) 
                                ))  {
                                    // not in range 
                                    $minPickupTime = null;
                                    $maxPickupTime = null;
                                }
                                break;
                            }
                            case 2 : {
                                if (!(
                                    ($minPickupTime>=$proposal2->getCriteria()->getTueMinTime() && $minPickupTime<=$proposal2->getCriteria()->getTueMaxTime()) || 
                                    ($maxPickupTime>=$proposal2->getCriteria()->getTueMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getTueMaxTime()) 
                                ))  {
                                    // not in range 
                                    $minPickupTime = null;
                                    $maxPickupTime = null;
                                }
                                break;
                            }
                            case 3 : {
                                if (!(
                                    ($minPickupTime>=$proposal2->getCriteria()->getWedMinTime() && $minPickupTime<=$proposal2->getCriteria()->getWedMaxTime()) || 
                                    ($maxPickupTime>=$proposal2->getCriteria()->getWedMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getWedMaxTime()) 
                                ))  {
                                    // not in range 
                                    $minPickupTime = null;
                                    $maxPickupTime = null;
                                }
                                break;
                            }
                            case 4 : {
                                if (!(
                                    ($minPickupTime>=$proposal2->getCriteria()->getThuMinTime() && $minPickupTime<=$proposal2->getCriteria()->getThuMaxTime()) || 
                                    ($maxPickupTime>=$proposal2->getCriteria()->getThuMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getThuMaxTime()) 
                                ))  {
                                    // not in range 
                                    $minPickupTime = null;
                                    $maxPickupTime = null;
                                }
                                break;
                            }
                            case 5 : {
                                if (!(
                                    ($minPickupTime>=$proposal2->getCriteria()->getFriMinTime() && $minPickupTime<=$proposal2->getCriteria()->getFriMaxTime()) || 
                                    ($maxPickupTime>=$proposal2->getCriteria()->getFriMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getFriMaxTime()) 
                                ))  {
                                    // not in range 
                                    $minPickupTime = null;
                                    $maxPickupTime = null;
                                }
                                break;
                            }
                            case 6 : {
                                if (!(
                                    ($minPickupTime>=$proposal2->getCriteria()->getSatMinTime() && $minPickupTime<=$proposal2->getCriteria()->getSatMaxTime()) || 
                                    ($maxPickupTime>=$proposal2->getCriteria()->getSatMinTime() && $maxPickupTime<=$proposal2->getCriteria()->getSatMaxTime()) 
                                ))  {
                                    // not in range 
                                    $minPickupTime = null;
                                    $maxPickupTime = null;
                                }
                                break;
                            }
                        }
                        break;
                    }
                }
                break;    
            }
            case Criteria::FREQUENCY_REGULAR : {
                switch ($proposal2->getCriteria()->getFrequency()) {
                    case Criteria::FREQUENCY_PUNCTUAL : {
                        if ($proposal1->getCriteria()->getMonCheck() && $proposal2->getCriteria()->getFromDate()->format('w') == 1) {
                            $monMinPickupTime = clone $proposal1->getCriteria()->getMonMinTime();
                            $monMaxPickupTime = clone $proposal1->getCriteria()->getMonMaxTime();
                            $monMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $monMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($monMinPickupTime>=$proposal2->getCriteria()->getMinTime() && $monMinPickupTime<=$proposal2->getCriteria()->getMaxTime()) || 
                                ($monMaxPickupTime>=$proposal2->getCriteria()->getMinTime() && $monMaxPickupTime<=$proposal2->getCriteria()->getMaxTime()) 
                            )) {
                                // not in range 
                                $monMinPickupTime = null;
                                $monMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->getTueCheck() && $proposal2->getCriteria()->getFromDate()->format('w') == 2) {
                            $tueMinPickupTime = clone $proposal1->getCriteria()->getTueMinTime();
                            $tueMaxPickupTime = clone $proposal1->getCriteria()->getTueMaxTime();
                            $tueMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $tueMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($tueMinPickupTime>=$proposal2->getCriteria()->getMinTime() && $tueMinPickupTime<=$proposal2->getCriteria()->getMaxTime()) || 
                                ($tueMaxPickupTime>=$proposal2->getCriteria()->getMinTime() && $tueMaxPickupTime<=$proposal2->getCriteria()->getMaxTime()) 
                            )) {
                                // not in range 
                                $tueMinPickupTime = null;
                                $tueMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->getWedCheck() && $proposal2->getCriteria()->getFromDate()->format('w') == 3) {
                            $wedMinPickupTime = clone $proposal1->getCriteria()->getWedMinTime();
                            $wedMaxPickupTime = clone $proposal1->getCriteria()->getWedMaxTime();
                            $wedMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $wedMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($wedMinPickupTime>=$proposal2->getCriteria()->getMinTime() && $wedMinPickupTime<=$proposal2->getCriteria()->getMaxTime()) || 
                                ($wedMaxPickupTime>=$proposal2->getCriteria()->getMinTime() && $wedMaxPickupTime<=$proposal2->getCriteria()->getMaxTime()) 
                            )) {
                                // not in range 
                                $wedMinPickupTime = null;
                                $wedMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->getThuCheck() && $proposal2->getCriteria()->getFromDate()->format('w') == 4) {
                            $thuMinPickupTime = clone $proposal1->getCriteria()->getThuMinTime();
                            $thuMaxPickupTime = clone $proposal1->getCriteria()->getThuMaxTime();
                            $thuMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $thuMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($thuMinPickupTime>=$proposal2->getCriteria()->getMinTime() && $thuMinPickupTime<=$proposal2->getCriteria()->getMaxTime()) || 
                                ($thuMaxPickupTime>=$proposal2->getCriteria()->getMinTime() && $thuMaxPickupTime<=$proposal2->getCriteria()->getMaxTime()) 
                            )) {
                                // not in range 
                                $thuMinPickupTime = null;
                                $thuMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->getFriCheck() && $proposal2->getCriteria()->getFromDate()->format('w') == 5) {
                            $friMinPickupTime = clone $proposal1->getCriteria()->getFriMinTime();
                            $friMaxPickupTime = clone $proposal1->getCriteria()->getFriMaxTime();
                            $friMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $friMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($friMinPickupTime>=$proposal2->getCriteria()->getMinTime() && $friMinPickupTime<=$proposal2->getCriteria()->getMaxTime()) || 
                                ($friMaxPickupTime>=$proposal2->getCriteria()->getMinTime() && $friMaxPickupTime<=$proposal2->getCriteria()->getMaxTime()) 
                            )) {
                                // not in range 
                                $friMinPickupTime = null;
                                $friMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->getSatCheck() && $proposal2->getCriteria()->getFromDate()->format('w') == 6) {
                            $satMinPickupTime = clone $proposal1->getCriteria()->getSatMinTime();
                            $satMaxPickupTime = clone $proposal1->getCriteria()->getSatMaxTime();
                            $satMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $satMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($satMinPickupTime>=$proposal2->getCriteria()->getMinTime() && $satMinPickupTime<=$proposal2->getCriteria()->getMaxTime()) || 
                                ($satMaxPickupTime>=$proposal2->getCriteria()->getMinTime() && $satMaxPickupTime<=$proposal2->getCriteria()->getMaxTime()) 
                            )) {
                                // not in range 
                                $monMinPickupTime = null;
                                $satMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->getSunCheck() && $proposal2->getCriteria()->getFromDate()->format('w') == 0) {
                            $sunMinPickupTime = clone $proposal1->getCriteria()->getSunMinTime();
                            $sunMaxPickupTime = clone $proposal1->getCriteria()->getSunMaxTime();
                            $sunMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $sunMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($sunMinPickupTime>=$proposal2->getCriteria()->getMinTime() && $sunMinPickupTime<=$proposal2->getCriteria()->getMaxTime()) || 
                                ($sunMaxPickupTime>=$proposal2->getCriteria()->getMinTime() && $sunMaxPickupTime<=$proposal2->getCriteria()->getMaxTime()) 
                            )) {
                                // not in range 
                                $sunMinPickupTime = null;
                                $sunMaxPickupTime = null;
                            }
                        }
                        break;
                    }
                    case Criteria::FREQUENCY_REGULAR : {
                        if ($proposal1->getCriteria()->getMonCheck() && $proposal2->getCriteria()->getMonCheck()) {
                            $monMinPickupTime = clone $proposal1->getCriteria()->getMonMinTime();
                            $monMaxPickupTime = clone $proposal1->getCriteria()->getMonMaxTime();
                            $monMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $monMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($monMinPickupTime>=$proposal2->getCriteria()->getMonMinTime() && $monMinPickupTime<=$proposal2->getCriteria()->getMonMaxTime()) || 
                                ($monMaxPickupTime>=$proposal2->getCriteria()->getMonMinTime() && $monMaxPickupTime<=$proposal2->getCriteria()->getMonMaxTime()) 
                            )) {
                                // not in range 
                                $monMinPickupTime = null;
                                $monMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->getTueCheck() && $proposal2->getCriteria()->getTueCheck()) {
                            $tueMinPickupTime = clone $proposal1->getCriteria()->getTueMinTime();
                            $tueMaxPickupTime = clone $proposal1->getCriteria()->getTueMaxTime();
                            $tueMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $tueMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($tueMinPickupTime>=$proposal2->getCriteria()->getTueMinTime() && $tueMinPickupTime<=$proposal2->getCriteria()->getTueMaxTime()) || 
                                ($tueMaxPickupTime>=$proposal2->getCriteria()->getTueMinTime() && $tueMaxPickupTime<=$proposal2->getCriteria()->getTueMaxTime()) 
                            )) {
                                // not in range 
                                $tueMinPickupTime = null;
                                $tueMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->getWedCheck() && $proposal2->getCriteria()->getWedCheck()) {
                            $wedMinPickupTime = clone $proposal1->getCriteria()->getWedMinTime();
                            $wedMaxPickupTime = clone $proposal1->getCriteria()->getWedMaxTime();
                            $wedMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $wedMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($wedMinPickupTime>=$proposal2->getCriteria()->getWedMinTime() && $wedMinPickupTime<=$proposal2->getCriteria()->getWedMaxTime()) || 
                                ($wedMaxPickupTime>=$proposal2->getCriteria()->getWedMinTime() && $wedMaxPickupTime<=$proposal2->getCriteria()->getWedMaxTime()) 
                            )) {
                                // not in range 
                                $wedMinPickupTime = null;
                                $wedMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->getThuCheck() && $proposal2->getCriteria()->getThuCheck()) {
                            $thuMinPickupTime = clone $proposal1->getCriteria()->getThuMinTime();
                            $thuMaxPickupTime = clone $proposal1->getCriteria()->getThuMaxTime();
                            $thuMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $thuMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($thuMinPickupTime>=$proposal2->getCriteria()->getThuMinTime() && $thuMinPickupTime<=$proposal2->getCriteria()->getThuMaxTime()) || 
                                ($thuMaxPickupTime>=$proposal2->getCriteria()->getThuMinTime() && $thuMaxPickupTime<=$proposal2->getCriteria()->getThuMaxTime()) 
                            )) {
                                // not in range 
                                $thuMinPickupTime = null;
                                $thuMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->getFriCheck() && $proposal2->getCriteria()->getFriCheck()) {
                            $friMinPickupTime = clone $proposal1->getCriteria()->getFriMinTime();
                            $friMaxPickupTime = clone $proposal1->getCriteria()->getFriMaxTime();
                            $friMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $friMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($friMinPickupTime>=$proposal2->getCriteria()->getFriMinTime() && $friMinPickupTime<=$proposal2->getCriteria()->getFriMaxTime()) || 
                                ($friMaxPickupTime>=$proposal2->getCriteria()->getFriMinTime() && $friMaxPickupTime<=$proposal2->getCriteria()->getFriMaxTime()) 
                            )) {
                                // not in range 
                                $friMinPickupTime = null;
                                $friMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->getSatCheck() && $proposal2->getCriteria()->getSatCheck()) {
                            $satMinPickupTime = clone $proposal1->getCriteria()->getSatMinTime();
                            $satMaxPickupTime = clone $proposal1->getCriteria()->getSatMaxTime();
                            $satMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $satMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($satMinPickupTime>=$proposal2->getCriteria()->getSatMinTime() && $satMinPickupTime<=$proposal2->getCriteria()->getSatMaxTime()) || 
                                ($satMaxPickupTime>=$proposal2->getCriteria()->getSatMinTime() && $satMaxPickupTime<=$proposal2->getCriteria()->getSatMaxTime()) 
                            )) {
                                // not in range 
                                $monMinPickupTime = null;
                                $satMaxPickupTime = null;
                            }
                        }
                        if ($proposal1->getCriteria()->getSunCheck() && $proposal2->getCriteria()->getSunCheck()) {
                            $sunMinPickupTime = clone $proposal1->getCriteria()->getSunMinTime();
                            $sunMaxPickupTime = clone $proposal1->getCriteria()->getSunMaxTime();
                            $sunMinPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            $sunMaxPickupTime->add(new \DateInterval('PT' . $pickupDuration . 'S'));
                            if (!(
                                ($sunMinPickupTime>=$proposal2->getCriteria()->getSunMinTime() && $sunMinPickupTime<=$proposal2->getCriteria()->getSunMaxTime()) || 
                                ($sunMaxPickupTime>=$proposal2->getCriteria()->getSunMinTime() && $sunMaxPickupTime<=$proposal2->getCriteria()->getSunMaxTime()) 
                            )) {
                                // not in range 
                                $sunMinPickupTime = null;
                                $sunMaxPickupTime = null;
                            }
                        }
                        break;
                    }
                }
                break;
            }
        }
        $return = [];
        if ($minPickupTime) $return['minPickupTime'] = $minPickupTime;
        if ($maxPickupTime) $return['maxPickupTime'] = $maxPickupTime;
        if ($monMinPickupTime) $return['monMinPickupTime'] = $monMinPickupTime;
        if ($monMaxPickupTime) $return['monMaxPickupTime'] = $monMaxPickupTime;
        if ($tueMinPickupTime) $return['tueMinPickupTime'] = $tueMinPickupTime;
        if ($tueMaxPickupTime) $return['tueMaxPickupTime'] = $tueMaxPickupTime;
        if ($wedMinPickupTime) $return['wedMinPickupTime'] = $wedMinPickupTime;
        if ($wedMaxPickupTime) $return['wedMaxPickupTime'] = $wedMaxPickupTime;
        if ($thuMinPickupTime) $return['thuMinPickupTime'] = $thuMinPickupTime;
        if ($thuMaxPickupTime) $return['thuMaxPickupTime'] = $thuMaxPickupTime;
        if ($friMinPickupTime) $return['friMinPickupTime'] = $friMinPickupTime;
        if ($friMaxPickupTime) $return['friMaxPickupTime'] = $friMaxPickupTime;
        if ($satMinPickupTime) $return['satMinPickupTime'] = $satMinPickupTime;
        if ($satMaxPickupTime) $return['satMaxPickupTime'] = $satMaxPickupTime;
        if ($sunMinPickupTime) $return['sunMinPickupTime'] = $sunMinPickupTime;
        if ($sunMaxPickupTime) $return['sunMaxPickupTime'] = $sunMaxPickupTime;
        return $return;
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
