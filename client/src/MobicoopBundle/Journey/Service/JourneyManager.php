<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\Journey\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Journey\Entity\Journey;

/**
 * Journey management service.
 */
class JourneyManager
{
    private $dataProvider;

    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Journey::class);
    }

    /**
     * Get cities
     * @return array The cities found
     */
    public function getCities()
    {
        $cities = [];
        $this->dataProvider->setFormat(DataProvider::RETURN_JSON);
        $response = $this->dataProvider->getSpecialCollection('cities');
        if ($response->getCode() >=200 && $response->getCode() <= 300) {
            // organize cities by first letter
            foreach ($response->getValue() as $city) {
                if (!array_key_exists($this->getFirstUpperLetter($city), $cities) || !in_array($city, $cities[$this->getFirstUpperLetter($city)])) {
                    if (!is_numeric($this->getFirstUpperLetter($city))) {
                        $cities[$this->getFirstUpperLetter($city)][] = $city;
                    }
                }
            }
        }
        // we add the url-friendly name
        $superCities = [];
        foreach ($cities as $letter=>$lcities) {
            foreach ($lcities as $city) {
                $superCities[$letter][] = [
                    'city' => $city,
                    'sanitized' => $this->sanitize($city)
                ];
            }
        }
        return $superCities;
    }

    /**
     * Get all destinations from a given city
     *
     * @param string $city  The city
     * @return array The destinations found
     */
    public function getDestinations(string $city)
    {
        $response = $this->dataProvider->getSpecialCollection('destinations/'.$city);
        if ($response->getCode() >=200 && $response->getCode() <= 300) {
            $origin = $city;
            // we format the destination
            // we search the "real" origin => the city provided as parameter is a sanitized version
            foreach ($response->getValue()->getMember() as $journey) {
                $journey->setDestination(ucfirst(strtolower($journey->getDestination())));
                $journey->setDestinationSanitized($this->sanitize($journey->getDestination()));
                $journey->setOriginSanitized($this->sanitize($journey->getOrigin()));
                if ($journey->getOrigin() !== $origin) {
                    $origin = ucfirst(strtolower($journey->getOrigin()));
                }
            }
            return [
                'origin' => $origin,
                'originSanitized' => $this->sanitize($origin),
                'journeys' => $response->getValue()->getMember()
            ];
        }
        return [
            'origin' => $city,
            'originSanitized' => $this->sanitize($city),
            'journeys' => []
        ];
    }

    /**
     * Get all origins to a given city
     *
     * @param string $city  The city
     * @return array The origins found
     */
    public function getOrigins(string $city)
    {
        $response = $this->dataProvider->getSpecialCollection('origins/'.$city);
        if ($response->getCode() >=200 && $response->getCode() <= 300) {
            $destination = $city;
            // we format the origin
            // we also search the "real" destination => the city provided as parameter is a sanitized version
            foreach ($response->getValue()->getMember() as $journey) {
                $journey->setOrigin(ucfirst(strtolower($journey->getOrigin())));
                $journey->setOriginSanitized($this->sanitize($journey->getOrigin()));
                $journey->setDestinationSanitized($this->sanitize($journey->getDestination()));
                if ($journey->getDestination() !== $destination) {
                    // we stop as soon as we get a valid destination
                    $destination = ucfirst(strtolower($journey->getDestination()));
                }
            }
            return [
                'destination' => $destination,
                'destinationSanitized' => $this->sanitize($destination),
                'journeys' => $response->getValue()->getMember()
            ];
        }
        return [
            'destination' => $city,
            'destinationSanitized' => $this->sanitize($city),
            'journeys' => []
        ];
    }

    /**
     * Get all journeys from a given city to a given city
     *
     * @param string $origin        The origin
     * @param string $destination   The destination
     * @param integer $frequency    The default frequency to display
     * @param integer $page         The page of results to display for the given frequency
     * @param integer $perPage      The number of results per page to dislay
     * @return void
     */
    public function getFromTo(string $origin, string $destination, int $frequency=1, int $page=1, int $perPage=30)
    {
        $response = $this->dataProvider->getSpecialCollection('origin/'.$origin.'/destination/'.$destination, ['page'=>$page,'perPage'=>$perPage]);
        if ($response->getCode() >=200 && $response->getCode() <= 300) {
            $journeys = [
                'punctual' => [],
                'regular' => []
            ];
            $lOrigin = $lDestination = null;
            foreach ($response->getValue()->getMember() as $journey) {
                // we search the "real" origin and destination => the cities provided as parameter are a sanitized version
                if (is_null($lOrigin) && $journey->getOrigin() !== $origin) {
                    $lOrigin = ucfirst(strtolower($journey->getOrigin()));
                }
                if (is_null($lDestination) && $journey->getDestination() !== $destination) {
                    $lDestination = ucfirst(strtolower($journey->getDestination()));
                }
                if ($journey->getFrequency() == Journey::FREQUENCY_PUNCTUAL) {
                    $journeys['punctual'][] = $journey;
                } else {
                    $journeys['regular'][] = $journey;
                }
            }
            return [
                'origin' => is_null($lOrigin) ? $origin : $lOrigin,
                'destination' => is_null($lDestination) ? $destination : $lDestination,
                'journeys' => $journeys,
                'total' => $response->getValue()->getTotalItems()
            ];
        }
        return [
            'origin' => $origin,
            'destination' => $destination,
            'journeys' => [],
            'total' => 0
        ];
    }


    /**
     * Get the popular journeys
     *
     * @return array
     */
    public function getPopularJourneys(): array
    {
        $popularJourneys = [];
        
        $this->dataProvider->setFormat(DataProvider::RETURN_JSON);
        $response = $this->dataProvider->getSpecialCollection('popular');
        if ($response->getCode() >=200 && $response->getCode() <= 300) {
            $popularJourneys = $response->getValue();
            // we add the url-friendly name
            foreach ($popularJourneys as $key => $popularJourney) {
                $popularJourneys[$key]["originSanitize"] = $this->sanitize($popularJourney['origin']);
                $popularJourneys[$key]["destinationSanitize"] = $this->sanitize($popularJourney['destination']);
            }
        }

        return $popularJourneys;
    }

    private function getFirstUpperLetter(string $string)
    {
        return strtoupper($this->normalize(mb_substr($string, 0, 1, 'utf-8')));
    }

    private function normalize($string)
    {
        $table = array(
            'Š'=>'S', 'š'=>'s', 'Ð'=>'Dj', 'Ž'=>'Z', 'ž'=>'z',
            'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
            'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
            'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
            'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
            'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
            'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'ý'=>'y', 'þ'=>'b',
            'ÿ'=>'y',
        );
        return strtr($string, $table);
    }

    /**
     * Sanitizes a name (remove special chars, replace spaces...).
     * @param string $string
     * @param boolean $force_lowercase
     * @param boolean $anal
     * @return string
     */
    private function sanitize(string $string, bool $force_lowercase = true, bool $anal = false)
    {
        $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
            "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
            "â€”", "â€“", ",", "<", ".", ">", "/", "?");
        $clean = trim(str_replace($strip, "-", strip_tags($string)));
        $clean = preg_replace('/\s+/', "-", $clean);
        $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean ;
        if ($force_lowercase) {
            if (function_exists('mb_strtolower')) {
                $clean = mb_strtolower($clean, 'UTF-8');
            } else {
                $clean = strtolower($clean);
            }
        }
        
        $clean = strtr($clean, [
            "à" => "a",
            "â" => "a",
            "ä" => "a",
            "é" => "e",
            "è" => "e",
            "ê" => "e",
            "ë" => "e",
            "ï" => "i",
            "î" => "i",
            "ô" => "o",
            "ö" => "o",
            "ù" => "u",
            "û" => "u",
            "ü" => "u",
            "ç" => "c"
        ]);
        return $clean;
    }
}
