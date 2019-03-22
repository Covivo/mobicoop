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
use App\Carpool\Service\ProposalMatcher;
use Doctrine\ORM\EntityManagerInterface;
use App\Carpool\Entity\Proposal;
use App\Carpool\Entity\Criteria;
use Symfony\Component\HttpFoundation\Response;
use App\Match\Service\GeoMatcher;
use App\Match\Entity\Candidate;
use App\Geography\Entity\Address;
use App\Geography\Service\GeoRouter;

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
     * @Route("/rd/matcher/{id}", name="matcher", requirements={"id"="\d+"})
     *
     */
    public function matcher($id, EntityManagerInterface $entityManager, ProposalMatcher $proposalMatcher)
    {
        if ($proposal = $entityManager->getRepository(Proposal::class)->find($id)) {
            echo "#$id : <l>";
            echo "<li>" . $proposal->getUser()->getEmail() . "</li>";
            if ($proposal->getCriteria()->isDriver()) echo "<li>Conducteur</li>";
            if ($proposal->getCriteria()->isPassenger()) echo "<li>Passager</li>";
            if ($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                echo "<li>Punctual</li>";
                echo "<li>" . $proposal->getCriteria()->getFromDate()->format('D d/m/Y') . " " . $proposal->getCriteria()->getMinTime()->format('H:i') . " - " . $proposal->getCriteria()->getMaxTime()->format('H:i') ."</li>";
            } else {
                echo "<li>Regular <ul>";
                if ($proposal->getCriteria()->getMonCheck()) echo "<li>L " . $proposal->getCriteria()->getMonMinTime()->format('H:i') . " - " . $proposal->getCriteria()->getMonMaxTime()->format('H:i') . "</li>";
                if ($proposal->getCriteria()->getTueCheck()) echo "<li>M " . $proposal->getCriteria()->getTueMinTime()->format('H:i') . " - " . $proposal->getCriteria()->getTueMaxTime()->format('H:i') . "</li>";
                if ($proposal->getCriteria()->getWedCheck()) echo "<li>Me " . $proposal->getCriteria()->getWedMinTime()->format('H:i') . " - " . $proposal->getCriteria()->getWedMaxTime()->format('H:i') . "</li>";
                if ($proposal->getCriteria()->getThuCheck()) echo "<li>J " . $proposal->getCriteria()->getThuMinTime()->format('H:i') . " - " . $proposal->getCriteria()->getThuMaxTime()->format('H:i') . "</li>";
                if ($proposal->getCriteria()->getFriCheck()) echo "<li>V " . $proposal->getCriteria()->getFriMinTime()->format('H:i') . " - " . $proposal->getCriteria()->getFriMaxTime()->format('H:i') . "</li>";
                if ($proposal->getCriteria()->getSatCheck()) echo "<li>S " . $proposal->getCriteria()->getSatMinTime()->format('H:i') . " - " . $proposal->getCriteria()->getSatMaxTime()->format('H:i') . "</li>";
                if ($proposal->getCriteria()->getSunCheck()) echo "<li>D " . $proposal->getCriteria()->getSunMinTime()->format('H:i') . " - " . $proposal->getCriteria()->getSunMaxTime()->format('H:i') . "</li>";
                echo "</ul></li>";
                echo "<li>" . $proposal->getCriteria()->getFromDate()->format('D d/m/Y') . " - " . $proposal->getCriteria()->getToDate()->format('D d/m/Y') . "</li>";
            }
            echo "</ul>";
            if ($proposals = $proposalMatcher->findMatchingProposals($proposal)) {
                echo "<ul>";
                foreach ($proposals as $proposalFound) {
                    echo "<li>Proposal #" . $proposalFound->getId() . "<ul>";
                    echo "<li>" . $proposalFound->getUser()->getEmail() . "</li>";
                    if ($proposalFound->getCriteria()->isDriver()) echo "<li>Conducteur</li>";
                    if ($proposalFound->getCriteria()->isPassenger()) echo "<li>Passager</li>";
                    if ($proposalFound->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                        echo "<li>Punctual</li>";
                        echo "<li>" . $proposalFound->getCriteria()->getFromDate()->format('D d/m/Y') . " " . $proposalFound->getCriteria()->getMinTime()->format('H:i') . " - " . $proposalFound->getCriteria()->getMaxTime()->format('H:i') ."</li>";
                    } else {
                        echo "<li>Regular <ul>";
                        if ($proposalFound->getCriteria()->getMonCheck()) echo "<li>L " . $proposalFound->getCriteria()->getMonMinTime()->format('H:i') . " - " . $proposalFound->getCriteria()->getMonMaxTime()->format('H:i') . "</li>";
                        if ($proposalFound->getCriteria()->getTueCheck()) echo "<li>M " . $proposalFound->getCriteria()->getTueMinTime()->format('H:i') . " - " . $proposalFound->getCriteria()->getTueMaxTime()->format('H:i') . "</li>";
                        if ($proposalFound->getCriteria()->getWedCheck()) echo "<li>Me " . $proposalFound->getCriteria()->getWedMinTime()->format('H:i') . " - " . $proposalFound->getCriteria()->getWedMaxTime()->format('H:i') . "</li>";
                        if ($proposalFound->getCriteria()->getThuCheck()) echo "<li>J " . $proposalFound->getCriteria()->getThuMinTime()->format('H:i') . " - " . $proposalFound->getCriteria()->getThuMaxTime()->format('H:i') . "</li>";
                        if ($proposalFound->getCriteria()->getFriCheck()) echo "<li>V " . $proposalFound->getCriteria()->getFriMinTime()->format('H:i') . " - " . $proposalFound->getCriteria()->getFriMaxTime()->format('H:i') . "</li>";
                        if ($proposalFound->getCriteria()->getSatCheck()) echo "<li>S " . $proposalFound->getCriteria()->getSatMinTime()->format('H:i') . " - " . $proposalFound->getCriteria()->getSatMaxTime()->format('H:i') . "</li>";
                        if ($proposalFound->getCriteria()->getSunCheck()) echo "<li>D " . $proposalFound->getCriteria()->getSunMinTime()->format('H:i') . " - " . $proposalFound->getCriteria()->getSunMaxTime()->format('H:i') . "</li>";
                        echo "</ul></li>";
                        echo "<li>" . $proposalFound->getCriteria()->getFromDate()->format('D d/m/Y') . " - " . $proposalFound->getCriteria()->getToDate()->format('D d/m/Y') . "</li>";
                    }
                    echo "</ul>";
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
    public function matcherAll(EntityManagerInterface $entityManager, ProposalMatcher $proposalMatcher)
    {
        $proposals = $entityManager->getRepository(Proposal::class)->findAll();
        echo "Finding matching for " . count($proposals) . " proposals.";
        echo "<ul>";
        foreach ($proposals as $proposal) {
            echo "<li>Creating matchings for proposals #" . $proposal->getId() . "</li>";
            $proposalMatcher->createMatchingsForProposal($proposal);
        }
        echo "</ul>";
        return new Response();
    }

    /**
     * Test of the matcher.
     *
     * @Route("/rd/matcher/simple", name="matcher_simple")
     *
     */
    public function matcherSimple(GeoMatcher $geoMatcher, GeoRouter $geoRouter)
    {
        echo "simple matcher<br />";

        $candidate1 = new Candidate();
        $candidate2 = new Candidate();

        $address1 = new Address();
        $address1b = new Address();
        $address1c = new Address();
        $address2 = new Address();
        $address3 = new Address();
        $address4 = new Address();

        $address1->setLatitude("48.682839");
        $address1->setLongitude("6.175954");
        $address1b->setLatitude("48.966277");
        $address1b->setLongitude("6.105825");
        $address1c->setLatitude("49.057861");
        $address1c->setLongitude("6.122703");
        $address2->setLatitude("49.599326");
        $address2->setLongitude("6.132797");
        $address3->setLatitude("49.125015");
        $address3->setLongitude("6.164381");
        $address4->setLatitude("49.261915");
        $address4->setLongitude("6.169167");

        $candidate1->setAddresses([$address1,$address1b,$address1c,$address2]);
        $candidate2->setAddresses([$address3,$address4]);
        
        $candidate1->setMaxDetourDistance(15000);
        $candidate1->setMaxDetourDuration(1200000);

        if ($routes = $geoRouter->getRoutes($candidate1->getAddresses())) {
            $candidate1->setDirection($routes[0]);
            $matches = $geoMatcher->singleMatch($candidate1, [$candidate2], true);
            echo "<pre>" . print_r($matches, true) . "</pre>";
        }
        exit;
    }
}
