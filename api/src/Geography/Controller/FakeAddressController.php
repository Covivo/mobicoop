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
 */

namespace App\Geography\Controller;

use App\Geography\Service\GeoSearcher;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller class for API testing purpose.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class FakeAddressController extends AbstractController
{
    /**
     * Fake address generator.
     *
     * @Route("/rd/faker/{number}/{min_lat}/{min_lon}/{max_lat}/{max_lon}", name="faker")
     *
     * @param mixed $number
     * @param mixed $min_lat
     * @param mixed $min_lon
     * @param mixed $max_lat
     * @param mixed $max_lon
     */
    public function faker($number, $min_lat, $min_lon, $max_lat, $max_lon, GeoSearcher $geoSearcher)
    {
        $generated = 0;
        $fakes = [];
        while ($generated < $number) {
            $lat = $this->randomFloat($min_lat, $max_lat);
            $lon = $this->randomFloat($min_lon, $max_lon);
            if ($address = $geoSearcher->reverseGeoCode($lat, $lon)) {
                $fakes[] = $address;
                ++$generated;
            }
        }
        foreach ($fakes as $fake) {
            echo $fake.'<br />';
        }
        // return $geoSearcher->geoCode($this->request->get("input"));
        return new Response();
    }

    private function randomFloat($min = 0, $max = 1)
    {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
}
