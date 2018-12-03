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

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Geography\Service\GeoRouter;
use App\Address\Entity\Address;

/**
 * FOR TESTING PURPOSE ONLY
 *
 * Creation of geographic zones.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 */
class ZoneController extends AbstractController
{
    /**
     * @Route("/create_zones")
     */
    public function createZones()
    {
        $servername = "localhost";
        $username = "mobicoop";
        $password = "mobicoop";
        $dbname = "mobicoop";
        
        // Create connection
        $conn = new \mysqli($servername, $username, $password, $dbname);
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        
        $MIN_LAT = -55;     // sud chili
        $MAX_LAT = 71;      // nord norvege
        $MIN_LON = -180;
        $MAX_LON = 180;
        $step = 0.5;                // la finesse de la grille en degré : 0.5 correspond à 0.5° de latitude et longitude
        $fine = 0.000001;           // la précision des valeurs de longitude et latitude stockées en base : par exemple pour une latitude 48, pour une précision de 0.000001 et un pas de 0.5 => de 48.000000 à 48.499999, de 48.500000 à 48.999999 etc...
        $delta = $step - $fine;
        
        set_time_limit(0);
        
        // TRUNCATE TABLES
        $time_start = microtime(true);
        $sql = 'SET foreign_key_checks = 0;';
        if (!$res = $conn->query($sql)) {
            echo $conn->error;
        }
        $sql = 'TRUNCATE near;';
        if (!$res = $conn->query($sql)) {
            echo $conn->error;
        }
        $sql = 'TRUNCATE zone;';
        if (!$res = $conn->query($sql)) {
            echo $conn->error;
        }
        $sql = 'SET foreign_key_checks = 1;';
        if (!$res = $conn->query($sql)) {
            echo $conn->error;
        }
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        echo 'Execution time for truncate : '.$time.' seconds<br />';
        
        // CREATE ZONES
        
        $time_start = microtime(true);
        for ($lat=$MIN_LAT;$lat<$MAX_LAT;$lat = $lat + $step) {
            $sql = "INSERT INTO zone (from_lat, to_lat, from_lon, to_lon)
            VALUES ";
            for ($lon=$MIN_LON;$lon<$MAX_LON;$lon = $lon + $step) {
                $fromLat = $lat;
                if (($lat + $step) == $MAX_LAT) {
                    $toLat = $lat + $step;
                } else {
                    $toLat = $lat + $delta;
                }
                if ($lon == $MIN_LON) {
                    $fromLon = $MIN_LON + $fine;
                } else {
                    $fromLon = $lon;
                }
                if (($lon + $step) == $MAX_LON) {
                    $toLon = $lon + $step;
                } else {
                    $toLon = $lon + $delta;
                }
                $sql .= "($fromLat, $toLat, $fromLon, $toLon),";
            }
            $sql = rtrim($sql, ",");
            $res = $conn->query($sql);
        }
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        echo 'Execution time for create zones : '.$time.' seconds<br />';
        
        
        
        // CREATE NEAR
        
        // les zones voisines de ZZ :
        
        // X1 X2 X3
        // X4 ZZ X5
        // X6 X7 X8
        
        $time_start = microtime(true);
        
        $sql = "SELECT count(id) as nb FROM zone";
        $result = $conn->query($sql);
        
        $nbrows = 0;
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $nbrows = $row["nb"];
            }
        }
        
        $nbLon = ($MAX_LON - $MIN_LON)/$step;
        $nbLat = (($MAX_LAT - $MIN_LAT)/$step)-1;
        
        $batchSize = 100;   // on va insérer les valeurs en base par batch pour optimiser le temps de traitement
        $sql = "";
        for ($i=1;$i<=$nbrows;$i++) {
            if ($sql == "") {
                $sql = "INSERT INTO near (zone1_id,zone2_id) VALUES ";
            }
            
            // cas génériques (milieu de grille)
            $x1 = $i-1-$nbLon;
            $x2 = $i-$nbLon;
            $x3 = $i+1-$nbLon;
            $x4 = $i-1;
            $x5 = $i+1;
            $x6 = $i-1+$nbLon;
            $x7 = $i+$nbLon;
            $x8 = $i+1+$nbLon;
            
            // on regarde si on est sur la première ligne ou la dernière ligne
            if ($i<=$nbLon) {
                // première ligne : pas de X1, X2, X3
                $x1 = $x2 = $x3 = 0;
                if ($i==1) {
                    // premier de la première ligne
                    $x4 = $i-1+$nbLon;
                    $x6 = $x4+$nbLon;
                } elseif ($i==$nbLon) {
                    // dernier de la première ligne
                    $x5 = $i-$nbLon+1;
                    $x8 = $i+1;
                }
            } elseif ($i>($nbLat*$nbLon)) {
                // dernière ligne : pas de X6, X7, X8
                $x6 = $x7 = $x8 = 0;
                if ((($i-1) % $nbLon) === 0) {
                    // premier de la dernière ligne
                    $x1 = $i-1;
                    $x4 = $i-1+$nbLon;
                } elseif (($i % $nbLon) === 0) {
                    // dernier de la dernière ligne
                    $x3 = $i-$nbLon-$nbLon+1;
                    $x5 = $i-$nbLon+1;
                }
            } elseif ((($i-1) % $nbLon) === 0) {
                // premier de la ligne
                $x1 = $i-1;
                $x4 = $i-1+$nbLon;
                $x6 = $x4+$nbLon;
            } elseif (($i % $nbLon) === 0) {
                // dernier de la ligne
                $x3 = $i-$nbLon-$nbLon+1;
                $x5 = $i-$nbLon+1;
                $x8 = $i+1;
            }
            
            if ($x1>0) {
                $sql .= "($i,$x1),";
            }
            if ($x2>0) {
                $sql .= "($i,$x2),";
            }
            if ($x3>0) {
                $sql .= "($i,$x3),";
            }
            if ($x4>0) {
                $sql .= "($i,$x4),";
            }
            if ($x5>0) {
                $sql .= "($i,$x5),";
            }
            if ($x6>0) {
                $sql .= "($i,$x6),";
            }
            if ($x7>0) {
                $sql .= "($i,$x7),";
            }
            if ($x8>0) {
                $sql .= "($i,$x8),";
            }
            
            if (($i % $batchSize) === 0) {
                $sql = rtrim($sql, ",");
                $conn->query($sql);
                $sql = "";
            }
        }
        
        if ($sql != "") {
            $sql = rtrim($sql, ",");
            $res = $conn->query($sql);
        }
        
        $time_end = microtime(true);
        $time = $time_end - $time_start;
        echo 'Execution time for create near : '.$time.' seconds';
        
        $conn->close();
        exit;
    }
}
