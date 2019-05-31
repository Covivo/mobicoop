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

namespace App\Geography\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use App\Geography\Service\TerritoryManager;
use App\Geography\Repository\TerritoryRepository;
use App\Geography\Entity\Address;
use Doctrine\ORM\EntityManagerInterface;
use App\Geography\Entity\Direction;
use App\DataProvider\Entity\GeoRouterProvider;

/**
 * Controller class for API testing purpose.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
class TestGeographyController extends AbstractController
{
    /**
     * Test territory
     *
     * @Route("/rd/geography/territory/{id}", name="test_geography_territory")
     *
     */
    public function testGeographyTerritory($id, TerritoryManager $territoryManager, TerritoryRepository $territoryRepository)
    {
        $territory = $territoryRepository->find($id);
        $territoryManager->associateDirectionsForTerritory($territory);
        return new Response();
    }

    /**
     * Test addresses geojson
     *
     * @Route("/rd/geography/addresses/geojson", name="test_addresses_geojson")
     *
     */
    public function testAddressesGeojson(EntityManagerInterface $entityManager)
    {
        if ($addresses = $entityManager->getRepository(Address::class)->findAll()) {
            foreach ($addresses as $address) {
                $address->setAutoGeoJson();
                $entityManager->persist($address);
            }
            $entityManager->flush();
        }
        return new Response();
    }

    /**
     * Test direction bbox geojson
     *
     * @Route("/rd/geography/directions/bbox/geojson", name="test_directions_bbox_geojson")
     *
     */
    public function testDirectionsBboxGeojson(EntityManagerInterface $entityManager)
    {
        if ($directions = $entityManager->getRepository(Direction::class)->findAll()) {
            foreach ($directions as $direction) {
                $direction->setAutoGeoJsonBbox();
                $entityManager->persist($direction);
            }
            $entityManager->flush();
        }
        return new Response();
    }

    /**
     * Test direction detail geojson
     *
     * @Route("/rd/geography/directions/detail/geojson", name="test_directions_detail_geojson")
     *
     */
    public function testDirectionsDetailGeojson(EntityManagerInterface $entityManager)
    {
        set_time_limit(600);
        if ($directions = $entityManager->getRepository(Direction::class)->findAll()) {
            foreach ($directions as $direction) {
                if (is_null($direction->getPoints())) {
                    $direction->setPoints(GeoRouterProvider::deserializePoints($direction->getDetail(), true, false));
                }
                $direction->setAutoGeoJsonDetail();
                $entityManager->persist($direction);
            }
            $entityManager->flush();
        }
        return new Response();
    }

}
