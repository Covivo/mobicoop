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

namespace App\Controller;

use App\Geography\Entity\Address;
use App\Geography\Service\GeoRouter;
use App\Geography\Service\ZoneManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

/**
 * FOR TESTING PURPOSE ONLY.
 *
 * Test of GeoRouter services.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class GeoRouterController extends AbstractController
{
    /**
     * @Route("/georouter")
     */
    public function test(GeoRouter $geoRouter)
    {
        // test paris => nancy // reims => verdun
        $address1 = new Address(1);
        $address1->setLatitude(48.892523);
        $address1->setLongitude(2.367379);
        $address2 = new Address(2);
        $address2->setLatitude(48.699873);
        $address2->setLongitude(6.174560);
        $address3 = new Address(3);
        $address3->setLatitude(49.248909);
        $address3->setLongitude(4.037836);
        $address4 = new Address(4);
        $address4->setLatitude(48.755365);
        $address4->setLongitude(5.588119);
        $address5 = new Address(5);
        $address5->setLatitude(49.548909);
        $address5->setLongitude(4.137836);
        $address6 = new Address(6);
        $address6->setLatitude(49.181491);
        $address6->setLongitude(5.695852);

        $address7 = new Address(3);
        $address7->setLatitude(49.248909);
        $address7->setLongitude(4.037836);
        $address8 = new Address(4);
        $address8->setLatitude(48.755365);
        $address8->setLongitude(5.588119);
        $address9 = new Address(5);
        $address9->setLatitude(49.548909);
        $address9->setLongitude(4.137836);
        $address10 = new Address(6);
        $address10->setLatitude(49.181491);
        $address10->setLongitude(5.695852);

        $address11 = new Address(3);
        $address11->setLatitude(49.248909);
        $address11->setLongitude(4.037836);
        $address12 = new Address(4);
        $address12->setLatitude(48.755365);
        $address12->setLongitude(5.588119);
        $address13 = new Address(5);
        $address13->setLatitude(49.548909);
        $address13->setLongitude(4.137836);
        $address14 = new Address(6);
        $address14->setLatitude(49.181491);
        $address14->setLongitude(5.695852);

        // nancy metz
        $address15 = new Address(15);
        $address15->setLatitude(48.693900);
        $address15->setLongitude(6.178986);

        $address16 = new Address(16);
        $address16->setLatitude(49.120602);
        $address16->setLongitude(6.174711);

        // hambourg séville
        $address17 = new Address(17);
        $address17->setLatitude(53.50900);
        $address17->setLongitude(9.999634);

        $address18 = new Address(18);
        $address18->setLatitude(37.268897);
        $address18->setLongitude(-5.682345);

        $addresses1 = [
            $address1,
            $address2,
        ];

        $addresses2 = [
            $address1,
            $address3,
            $address4,
            $address2,
        ];

        $addresses3 = [
            $address1,
            $address5,
            $address6,
            $address2,
        ];

        $addresses4 = [
            $address1,
            $address7,
            $address8,
            $address2,
        ];

        $addresses5 = [
            $address1,
            $address9,
            $address10,
            $address2,
        ];

        $addresses6 = [
            $address1,
            $address11,
            $address12,
            $address2,
        ];

        $addresses7 = [
            $address1,
            $address13,
            $address14,
            $address2,
        ];

        $addresses8 = [
            $address15,
            $address16,
        ];

        $addresses9 = [
            $address17,
            $address18,
        ];

        $addressespool = [
            $address15,
            $address16,
            $address17,
            $address18,
        ];
        $addresses100 = [];

        for ($i = 1; $i <= 50; ++$i) {
            $addresses100[] = $addressespool[rand(0, count($addressespool) - 1)];
        }

        $start = microtime(true);
        $routes8 = $geoRouter->getRoutes($addresses100);
        $time_elapsed_secs = microtime(true) - $start;

        /*$route1 = $routes1[0];
        $route2 = $routes2[0];
        $route3 = $routes3[0];
        $route4 = $routes4[0];
        $route5 = $routes5[0];
        $route6 = $routes6[0];
        $route7 = $routes7[0];*/
        $route8 = $routes8[0];

        /*$duration1 = $route1->getDuration()/1000/60;
        $duration2 = $route2->getDuration()/1000/60;
        $duration3 = $route3->getDuration()/1000/60;
        $duration4 = $route4->getDuration()/1000/60;
        $duration5 = $route5->getDuration()/1000/60;
        $duration6 = $route6->getDuration()/1000/60;
        $duration7 = $route7->getDuration()/1000/60;*/
        $duration8 = $route8->getDuration() / 1000 / 60;

        /*$distance1 = $route1->getDistance()/1000;
        $distance2 = $route2->getDistance()/1000;
        $distance3 = $route3->getDistance()/1000;
        $distance4 = $route4->getDistance()/1000;
        $distance5 = $route5->getDistance()/1000;
        $distance6 = $route6->getDistance()/1000;
        $distance7 = $route7->getDistance()/1000;*/
        $distance8 = $route8->getDistance() / 1000;

        // echo "Route 1 // duration = $duration1 minutes, distance = $distance1 kms<br />";
        /*foreach ($route1->getWaypoints() as $waypoint) {
            echo $waypoint ->getLatitude() . ", " . $waypoint->getLongitude() . "<br />";
        }*/
        // echo "<pre>" . print_r($route1->getPoints(),true) . "</pre>";
        // echo "Route 2 // duration = $duration2 minutes, distance = $distance2 kms<br />";
        /*foreach ($route2->getWaypoints() as $waypoint) {
            echo $waypoint ->getLatitude() . ", " . $waypoint->getLongitude() . "<br />";
        }*/
        // echo "Route 3 // duration = $duration3 minutes, distance = $distance3 kms<br />";
        /*foreach ($route3->getWaypoints() as $waypoint) {
            echo $waypoint ->getLatitude() . ", " . $waypoint->getLongitude() . "<br />";
        }*/
        // echo "Route 4 // duration = $duration4 minutes, distance = $distance4 kms<br />";
        /*foreach ($route4->getWaypoints() as $waypoint) {
            echo $waypoint ->getLatitude() . ", " . $waypoint->getLongitude() . "<br />";
        }*/
        // echo "Route 5 // duration = $duration5 minutes, distance = $distance5 kms<br />";
        /*foreach ($route5->getWaypoints() as $waypoint) {
            echo $waypoint ->getLatitude() . ", " . $waypoint->getLongitude() . "<br />";
        }*/
        // echo "Route 6 // duration = $duration6 minutes, distance = $distance6 kms<br />";
        /*foreach ($route6->getWaypoints() as $waypoint) {
            echo $waypoint ->getLatitude() . ", " . $waypoint->getLongitude() . "<br />";
        }*/
        // echo "Route 7 // duration = $duration7 minutes, distance = $distance7 kms<br />";
        /*foreach ($route7->getWaypoints() as $waypoint) {
            echo $waypoint ->getLatitude() . ", " . $waypoint->getLongitude() . "<br />";
        }*/
        echo "Route 8 // duration = {$duration8} minutes, distance = {$distance8} kms<br />";
        /*foreach ($route7->getWaypoints() as $waypoint) {
            echo $waypoint ->getLatitude() . ", " . $waypoint->getLongitude() . "<br />";
        }*/
        echo "Calculation duration = {$time_elapsed_secs} s";

        exit;
    }

    /**
     * @Route("/georouter/zones")
     */
    public function zones(GeoRouter $geoRouter, ZoneManager $zoneManager)
    {
        // test paris => nancy // reims => verdun
        $address1 = new Address(1);
        $address1->setLatitude(48.45523);
        $address1->setLongitude(2.367379);
        $address2 = new Address(2);
        $address2->setLatitude(48.89873);
        $address2->setLongitude(6.174560);
        $address3 = new Address(3);
        $address3->setLatitude(49.12909);
        $address3->setLongitude(4.037836);
        $address4 = new Address(4);
        $address4->setLatitude(48.45365);
        $address4->setLongitude(5.588119);

        $addresses1 = [
            $address1,
            $address2,
        ];

        $addresses2 = [
            $address1,
            $address3,
            $address4,
            $address2,
        ];

        $start = microtime(true);
        $routes1 = $geoRouter->getRoutes($addresses1);
        $routes2 = $geoRouter->getRoutes($addresses2);

        $route1 = $routes1[0];
        $route2 = $routes2[0];

        $duration1 = $route1->getTime() / 1000 / 60;
        $duration2 = $route2->getTime() / 1000 / 60;

        $distance1 = $route1->getDistance() / 1000;
        $distance2 = $route2->getDistance() / 1000;

        $zones1 = $zoneManager->getZonesForAddresses($route1->getPoints());
        $zones2 = $zoneManager->getZonesForAddresses($route1->getPoints(), 1);

        $time_elapsed_secs = microtime(true) - $start;

        echo "Route 1 // duration = {$duration1} minutes, distance = {$distance1} kms<br />";
        echo 'Zones 1 // <br />';
        foreach ($zones1 as $zone) {
            echo $zone.' - ';
        }
        echo '<br />';
        echo "Route 2 // duration = {$duration2} minutes, distance = {$distance2} kms<br />";
        echo 'Zones 2 and near zones // <br />';
        foreach ($zones2 as $zone) {
            echo $zone.' - ';
        }
        echo '<br />';
        echo "Calculation duration = {$time_elapsed_secs} s";

        exit;
    }
}
