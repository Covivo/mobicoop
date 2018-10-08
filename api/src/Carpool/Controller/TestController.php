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

namespace App\Carpool\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Carpool\Service\MatchingAnalyzer;
use Doctrine\ORM\EntityManagerInterface;
use App\Carpool\Entity\Proposal;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller class for API testing purpose.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class TestController extends AbstractController
{
    /**
     * Show matching proposals for a given proposal.
     *
     * @Route("/matcher/{id}", name="matcher", requirements={"id"="\d+"})
     *
     */
    public function matcher($id, EntityManagerInterface $entityManager, MatchingAnalyzer $matchingAnalyzer)
    {
        if ($proposal = $entityManager->getRepository(Proposal::class)->find($id)) {
            // we search for the starting and ending point
            $startLocality = null;
            $endLocality = null;
            foreach ($proposal->getPoints() as $point) {
                if ($point->getPosition() == 0) $startLocality = $point->getAddress()->getStreetAddress() . " " . $point->getAddress()->getAddressLocality();
                if ($point->getLastPoint()) $endLocality = $point->getAddress()->getStreetAddress() . " " . $point->getAddress()->getAddressLocality();
                if (!is_null($startLocality) && !is_null($endLocality)) break;
            }
            echo ($proposal->getProposalType() == Proposal::PROPOSAL_TYPE_OFFER ? "Offer" : "Request") . " #$id : <ul>";
            echo "<li>" . $proposal->getUser()->getEmail() . "</li>";
            echo "<li>$startLocality => $endLocality</li>";
            echo "<li>" . $proposal->getCriteria()->getFromDate()->format('d/m/Y') . "</li>";
            echo "</ul>";
            echo ($proposal->getProposalType() == Proposal::PROPOSAL_TYPE_OFFER ? "Requests" : "Offers") . " found :";
            if ($proposals = $matchingAnalyzer->findMatchingProposals($proposal)) {
                echo "<ul>";
                foreach ($proposals as $proposalFound) {
                    $startLocalityFound = null;
                    $endLocalityFound = null;
                    foreach ($proposalFound->getPoints() as $point) {
                        if ($point->getPosition() == 0) $startLocalityFound = $point->getAddress()->getStreetAddress() . " " . $point->getAddress()->getAddressLocality();
                        if ($point->getLastPoint()) $endLocalityFound = $point->getAddress()->getStreetAddress() . " " . $point->getAddress()->getAddressLocality();
                        if (!is_null($startLocalityFound) && !is_null($endLocalityFound)) break;
                    }
                    echo "<li>Proposal #" . $proposalFound->getId() . "<ul>";
                    echo "<li>" . $proposalFound->getUser()->getEmail() . "</li>";
                    echo "<li>$startLocalityFound => $endLocalityFound</li>"; 
                    echo "<li>" . $proposalFound->getCriteria()->getFromDate()->format('d/m/Y') . "</li></ul>";
                }
                echo "</ul>";
            }
        } else {
            echo "No proposal found with id #$id";
        }
        return new Response();
    }
    
    /**
     * Create matching proposals for all proposals.
     *
     * @Route("/matcher/all", name="matcher_all")
     *
     */
    public function matcherAll(EntityManagerInterface $entityManager, MatchingAnalyzer $matchingAnalyzer)
    {
        $proposals = $entityManager->getRepository(Proposal::class)->findAll();
        echo "Finding matching for " . count($proposals) . " proposals.";
        echo "<ul>";
        foreach ($proposals as $proposal) {
            echo "<li>Creating matchings for proposals #" . $proposal->getId() . "</li>"; 
            $matchingAnalyzer->createMatchingsForProposal($proposal);
        }
        echo "</ul>";
        return new Response();
    }
}