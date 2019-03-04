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

namespace App\Geography\Service;

use App\Geography\Entity\Address;
use App\Geography\Entity\Zone;
use App\Geography\Entity\Near;
use Doctrine\ORM\EntityManagerInterface;
use App\Geography\Repository\ZoneRepository;
use App\Geography\Repository\NearRepository;

/**
 * Zone management service.
 *
 * This service gets the zone and nearby zones for routes and points.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ZoneManager
{
    const ZONE_STEP = 0.25;         // grid step granularity : 0.5 means 0.5° of latitude and longitude
    const ZONE_MIN_LAT = -55;       // south of chili
    const ZONE_MAX_LAT = 71;        // north of norway
    const ZONE_MIN_LON = -180;
    const ZONE_MAX_LON = 180;
    const ZONE_FINE = 0.000001;     // precision of latitude and longitude in the database :
    // eg. for latitude 48, for a precision of 0.000001 and a step of 0.5
    // => from 48.000000 to 48.499999, from 48.500000 to 48.999999 etc...
    
    const ZONE_EXCLUSION = [
        'south_atlantic' => [
            "-5.370941 -32.981277",
            "-53.671561 -51.196775",
            "-48.994979 28.040016",
            "-2.005297 2.430788",
            "-5.370941 -32.981277"
        ],
        'north_atlantic' => [
            "-2.441969 -33.781110",
            "10.275993 -53.299302",
            "28.956628 -70.693399",
            "51.649839 -41.780869",
            "61.710726 -22.205360",
            "40.446222 -12.845553",
            "39.215837 -32.270047",
            "19.244484 -19.124584",
            "17.557796 -29.738206",
            "1.818616 -7.553984",
            "-2.441969 -33.781110"
        ],
        'indian' => [
            "-36.884527 31.485103",
            "-20.677263 60.948575",
            "-2.416500 46.618354",
            "21.628438 62.887244",
            "3.332071 80.138364",
            "7.861915 88.844050",
            "-16.654507 112.828731",
            "-42.887503 110.261853",
            "-36.884527 31.485103"
        ],
        'north_pacific' => [
            "9.176729 129.522607",
            "14.271198 144.795667",
            "20.004987 -161.502381",
            "24.076250 -159.425745",
            "20.268988 -107.658026",
            "33.842041 -124.588173",
            "56.280672 -144.552292",
            "49.316147 -176.848227",
            "34.993887 143.498914",
            "20.203823 123.879791",
            "9.176729 129.522607"
        ],
        'middle_pacific' => [
            "4.501064 136.893880",
            "-11.476255 178.920444",
            "-11.927941 -149.825297",
            "-10.822515 -81.952354",
            "-1.269421 -94.153518",
            "8.963614 -90.222427",
            "27.865016 -128.062046",
            "17.630868 -154.237929",
            "20.738440 158.754132",
            "4.501064 136.893880"
        ],
        'south_pacific' => [
            "-20.416770 -177.355128",
            "-50.398292 -170.880605",
            "-56.294441 -76.828219",
            "-20.226241 -74.497866",
            "-2.339842 -90.433149",
            "-11.245732 -141.966619",
            "-21.106181 -148.955593",
            "-19.252824 -178.254350",
            "-20.416770 -177.355128"
        ]
    ];
    const EXCLUDE_ZONES = true;         // exclude zones when creating
    
    private $zoneRepository;
    private $nearRepository;
    private $entityManager;
    
    public function __construct(EntityManagerInterface $entityManager, ZoneRepository $zoneRepository, NearRepository $nearRepository)
    {
        $this->entityManager = $entityManager;
        $this->zoneRepository = $zoneRepository;
        $this->nearRepository = $nearRepository;
    }
    
    /**
     * Get the zones for a list of addresses.
     *
     * @param array $addresses[]    The array of addresses
     * @return array                The zones concerned by the addresses
     * @param int $deep             The deepness of near zones to retrieve (0 = only the zone, not the near zones)
     * @return array|NULL
     */
    public function getZonesForAddresses(array $addresses, int $deep=0): ?array
    {
        $zones = [];
        $baseLatitude = -1000;  // we are sure that this value doesn't exist
        $baseLongitude = -1000; //
        foreach ($addresses as $address) {
            // we search for the zone only if the base latitude or longitude has changed
            // /!\ we assume that the addresses are ordered /!\
            $baseAddressLongitude = $this->getBase($address->getLongitude());
            $baseAddressLatitude = $this->getBase($address->getLatitude());
            if (($baseAddressLatitude <> $baseLatitude) || ($baseAddressLongitude <> $baseLongitude)) {
                $baseLongitude = $baseAddressLongitude;
                $baseLatitude = $baseAddressLatitude;
                $zones = array_merge($zones, $this->getZonesForAddress($address, $deep));
            }
        }
        return array_unique($zones,SORT_REGULAR);
    }
    
    /**
     * Get the zones for an address.
     *
     * @param Address $address  The address
     * @param int $deep         The deepness of near zones to retrieve (0 = only the zone, not the near zones)
     * @return array|NULL       The zones concerned by the address
     */
    public function getZonesForAddress(Address $address, int $deep = 0): ?array
    {
        $zones = [];
        $zone = $this->zoneRepository->findOneByLatitudeLongitude($address->getLatitude(), $address->getLongitude());
        $zones[] = $zone;
        if ($deep == 0) {
            return $zones;
        } else {
            $nearbyZones = $this->getNear($zone->getId(), $deep);
            return array_merge($zones, $nearbyZones);
        }
    }
    
    /**
     * Get near zones.
     *
     * @param int $id       The id of the zone
     * @param int $deep     The deepness of the search (1 = direct nearby zones, 2 = nearby zone and their nearby zones, etc...)
     * @return array|NULL   The list of nearby zones.
     */
    public function getNear(int $id, int $deep): ?array
    {
        if ($zone = $this->zoneRepository->find($id)) {
            $azones = [];
            $near = self::near($zone, $azones, $deep);
            ksort($near);
            return $near;
        }
        return null;
    }
    
    private function near(Zone $zone, array $azones, int $deep): array
    {
        $azones[$zone->getId()] = $zone;
        if ($deep>0) {
            $nearZones = $this->nearRepository->findBy([
                'zone1' => $zone
            ]);
            foreach ($nearZones as $near) {
                $azones = self::near($near->getZone2(), $azones, $deep-1);
            }
        }
        return $azones;
    }
    
    // search for the base of a value, for a given step
    // the base is the nearest lower value for a given step
    // eg. for a 0.5 step, the base value of 48.123543 is 48
    // eg. for a 0.5 step, the base value of 48.823543 is 48.5
    // eg. for a 0.25 step, the base value of 48.123543 is 48
    // eg. for a 0.25 step, the base value of 48.823543 is 48.75
    public function getBase($value, $step=null)
    {
        if (is_null($step)) {
            $step = self::ZONE_STEP;
        }
        $nbstep = 1/$step;
        $base = intval($value);
        for ($i=1;$i<=$nbstep;$i++) {
            if ($base+($step*$i)>$value && $value>=0) {
                return $base+($step*($i-1));
            }
            if ($base-($step*$i)<$value && $value<0) {
                return $base-($step*($i-1));
            }
        }
        
        /*if ($step == self::ZONE_STEP) {
            return floor($value * 2) / 2;
        }*/
    }
    
    /**
     * FOR R&D PURPOSE ONLY
     *
     * Creation of geographic zones in the database.
     * Made in raw sql for performance optimization.
     */
//     public function createZones()
//     {
//         // Create connection
//         $conn = new \mysqli(
//             $this->entityManager->getConnection()->getHost(),
//             $this->entityManager->getConnection()->getUsername(),
//             $this->entityManager->getConnection()->getPassword(),
//             $this->entityManager->getConnection()->getDatabase()
//             );
//         // Check connection
//         if ($conn->connect_error) {
//             die("Connection failed: " . $conn->connect_error);
//         }
        
//         $MIN_LAT = self::ZONE_MIN_LAT;
//         $MAX_LAT = self::ZONE_MAX_LAT;
//         $MIN_LON = self::ZONE_MIN_LON;
//         $MAX_LON = self::ZONE_MAX_LON;
//         $step = self::ZONE_STEP;
//         $fine = self::ZONE_FINE;
//         $delta = $step - $fine;
//         $zones = [];
        
//         $pointLocation = new pointLocation();
        
//         set_time_limit(0);
        
//         // TRUNCATE TABLES
//         $time_start = microtime(true);
//         $sql = 'SET foreign_key_checks = 0;';
//         if (!$res = $conn->query($sql)) {
//             echo $conn->error;
//         }
//         $sql = 'TRUNCATE near;';
//         if (!$res = $conn->query($sql)) {
//             echo $conn->error;
//         }
//         $sql = 'TRUNCATE zone;';
//         if (!$res = $conn->query($sql)) {
//             echo $conn->error;
//         }
//         $sql = 'SET foreign_key_checks = 1;';
//         if (!$res = $conn->query($sql)) {
//             echo $conn->error;
//         }
//         $time_end = microtime(true);
//         $time = $time_end - $time_start;
//         echo 'Execution time for truncate : '.$time.' seconds<br />';
        
//         // CREATE ZONES
        
//         $time_start = microtime(true);
//         $i=0;
//         for ($lat=$MIN_LAT;$lat<$MAX_LAT;$lat = $lat + $step) {
//             $sql = "INSERT INTO zone (id, from_lat, to_lat, from_lon, to_lon)
//             VALUES ";
//             for ($lon=$MIN_LON;$lon<$MAX_LON;$lon = $lon + $step) {
//                 $i++;
//                 $fromLat = $lat;
//                 if (($lat + $step) == $MAX_LAT) {
//                     $toLat = $lat + $step;
//                 } else {
//                     $toLat = $lat + $delta;
//                 }
//                 if ($lon == $MIN_LON) {
//                     $fromLon = $MIN_LON + $fine;
//                 } else {
//                     $fromLon = $lon;
//                 }
//                 if (($lon + $step) == $MAX_LON) {
//                     $toLon = $lon + $step;
//                 } else {
//                     $toLon = $lon + $delta;
//                 }
                
//                 $exclude = false;
//                 if (self::EXCLUDE_ZONES) {
//                     $point1 = $fromLat . " " . $fromLon;
//                     $point2 = $toLat . " " . $toLon;
//                     foreach (self::ZONE_EXCLUSION as $ocean=>$polygon) {
//                         if ($pointLocation->pointInPolygon($point1, $polygon) == "inside" && $pointLocation->pointInPolygon($point2, $polygon) == "inside") {
//                             $exclude = true;
//                             break(1);
//                         }
//                     }
//                 }
//                 if (!$exclude) {
//                     $sql .= "($i, $fromLat, $toLat, $fromLon, $toLon),";
//                     $zones[] = $i;  // on ajoute la zone à la liste des zones effectivement créées
//                 }
//             }
//             $sql = rtrim($sql, ",");
//             $res = $conn->query($sql);
//         }
//         $time_end = microtime(true);
//         $time = $time_end - $time_start;
//         echo 'Execution time for create zones : '.$time.' seconds<br />';
        
//         // CREATE NEAR
        
//         // les zones voisines de ZZ :
        
//         // X1 X2 X3
//         // X4 ZZ X5
//         // X6 X7 X8
        
//         $time_start = microtime(true);
        
//         $sql = "SELECT count(id) as nb FROM zone";
//         $result = $conn->query($sql);
        
//         $nbrows = 0;
//         if ($result->num_rows > 0) {
//             while ($row = $result->fetch_assoc()) {
//                 $nbrows = $row["nb"];
//             }
//         }
        
//         $nbLon = ($MAX_LON - $MIN_LON)/$step;
//         $nbLat = (($MAX_LAT - $MIN_LAT)/$step)-1;
        
//         $batchSize = 100;   // on va insérer les valeurs en base par batch pour optimiser le temps de traitement
//         $sql = "";
//         //for ($i=1;$i<=$nbrows;$i++) {
//         foreach ($zones as $i) {
//             if ($sql == "") {
//                 $sql = "INSERT INTO near (zone1_id,zone2_id) VALUES ";
//             }
            
//             // cas génériques (milieu de grille)
//             $x1 = $i-1-$nbLon;
//             $x2 = $i-$nbLon;
//             $x3 = $i+1-$nbLon;
//             $x4 = $i-1;
//             $x5 = $i+1;
//             $x6 = $i-1+$nbLon;
//             $x7 = $i+$nbLon;
//             $x8 = $i+1+$nbLon;
            
//             // on regarde si on est sur la première ligne ou la dernière ligne
//             if ($i<=$nbLon) {
//                 // première ligne : pas de X1, X2, X3
//                 $x1 = $x2 = $x3 = 0;
//                 if ($i==1) {
//                     // premier de la première ligne
//                     $x4 = $i-1+$nbLon;
//                     $x6 = $x4+$nbLon;
//                 } elseif ($i==$nbLon) {
//                     // dernier de la première ligne
//                     $x5 = $i-$nbLon+1;
//                     $x8 = $i+1;
//                 }
//             } elseif ($i>($nbLat*$nbLon)) {
//                 // dernière ligne : pas de X6, X7, X8
//                 $x6 = $x7 = $x8 = 0;
//                 if ((($i-1) % $nbLon) === 0) {
//                     // premier de la dernière ligne
//                     $x1 = $i-1;
//                     $x4 = $i-1+$nbLon;
//                 } elseif (($i % $nbLon) === 0) {
//                     // dernier de la dernière ligne
//                     $x3 = $i-$nbLon-$nbLon+1;
//                     $x5 = $i-$nbLon+1;
//                 }
//             } elseif ((($i-1) % $nbLon) === 0) {
//                 // premier de la ligne
//                 $x1 = $i-1;
//                 $x4 = $i-1+$nbLon;
//                 $x6 = $x4+$nbLon;
//             } elseif (($i % $nbLon) === 0) {
//                 // dernier de la ligne
//                 $x3 = $i-$nbLon-$nbLon+1;
//                 $x5 = $i-$nbLon+1;
//                 $x8 = $i+1;
//             }
            
//             // pour chaque zone on va aussi vérifier qu'elle a effectivement été créée : il pourrait s'agir d'une zone exclue (océan)
//             if (($x1>0) && ((self::EXCLUDE_ZONES && in_array($x1, $zones)) || (!self::EXCLUDE_ZONES))) {
//                 $sql .= "($i,$x1),";
//             }
//             if (($x2>0) && ((self::EXCLUDE_ZONES && in_array($x2, $zones)) || (!self::EXCLUDE_ZONES))) {
//                 $sql .= "($i,$x2),";
//             }
//             if (($x3>0) && ((self::EXCLUDE_ZONES && in_array($x3, $zones)) || (!self::EXCLUDE_ZONES))) {
//                 $sql .= "($i,$x3),";
//             }
//             if (($x4>0) && ((self::EXCLUDE_ZONES && in_array($x4, $zones)) || (!self::EXCLUDE_ZONES))) {
//                 $sql .= "($i,$x4),";
//             }
//             if (($x5>0) && ((self::EXCLUDE_ZONES && in_array($x5, $zones)) || (!self::EXCLUDE_ZONES))) {
//                 $sql .= "($i,$x5),";
//             }
//             if (($x6>0) && ((self::EXCLUDE_ZONES && in_array($x6, $zones)) || (!self::EXCLUDE_ZONES))) {
//                 $sql .= "($i,$x6),";
//             }
//             if (($x7>0) && ((self::EXCLUDE_ZONES && in_array($x7, $zones)) || (!self::EXCLUDE_ZONES))) {
//                 $sql .= "($i,$x7),";
//             }
//             if (($x8>0) && ((self::EXCLUDE_ZONES && in_array($x8, $zones)) || (!self::EXCLUDE_ZONES))) {
//                 $sql .= "($i,$x8),";
//             }
            
//             if (($i % $batchSize) === 0) {
//                 $sql = rtrim($sql, ",");
//                 $conn->query($sql);
//                 $sql = "";
//             }
//         }
        
//         if ($sql != "") {
//             $sql = rtrim($sql, ",");
//             $res = $conn->query($sql);
//         }
        
//         $time_end = microtime(true);
//         $time = $time_end - $time_start;
//         echo 'Execution time for create near : '.$time.' seconds';
        
//         $conn->close();
//     }
}

class pointLocation
{
    public $pointOnVertex = true; // verify if a point is on a summit
    
    public function pointLocation()
    {
    }
    
    public function pointInPolygon($point, $polygon, $pointOnVertex = true)
    {
        $this->pointOnVertex = $pointOnVertex;
        
        // transform each coordinates tuple in a 2-value array (x and y)
        $point = $this->pointStringToCoordinates($point);
        $vertices = array();
        foreach ($polygon as $vertex) {
            $vertices[] = $this->pointStringToCoordinates($vertex);
        }
        
        // verify if the point is exactly on a summit
        if ($this->pointOnVertex == true and $this->pointOnVertex($point, $vertices) == true) {
            return "vertex";
        }
        
        // verify if the point is in the polygon or on a side
        $intersections = 0;
        $vertices_count = count($vertices);
        
        for ($i=1; $i < $vertices_count; $i++) {
            $vertex1 = $vertices[$i-1];
            $vertex2 = $vertices[$i];
            if ($vertex1['y'] == $vertex2['y'] and $vertex1['y'] == $point['y'] and $point['x'] > min($vertex1['x'], $vertex2['x']) and $point['x'] < max($vertex1['x'], $vertex2['x'])) { // Vérifier si le point est sur un bord horizontal
                return "boundary";
            }
            if ($point['y'] > min($vertex1['y'], $vertex2['y']) and $point['y'] <= max($vertex1['y'], $vertex2['y']) and $point['x'] <= max($vertex1['x'], $vertex2['x']) and $vertex1['y'] != $vertex2['y']) {
                $xinters = ($point['y'] - $vertex1['y']) * ($vertex2['x'] - $vertex1['x']) / ($vertex2['y'] - $vertex1['y']) + $vertex1['x'];
                if ($xinters == $point['x']) { 
                    return "boundary";
                }
                if ($vertex1['x'] == $vertex2['x'] || $point['x'] <= $xinters) {
                    $intersections++;
                }
            }
        }
        // if the number of cross borders is even, the point is in the polygon
        if ($intersections % 2 != 0) {
            return "inside";
        } else {
            return "outside";
        }
    }
    
    public function pointOnVertex($point, $vertices)
    {
        foreach ($vertices as $vertex) {
            if ($point == $vertex) {
                return true;
            }
        }
    }
    
    public function pointStringToCoordinates($pointString)
    {
        $coordinates = explode(" ", $pointString);
        return array("x" => $coordinates[0], "y" => $coordinates[1]);
    }
}
