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

namespace App\Rdex\Service;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\Response;
use App\Carpool\Service\ProposalManager;
use App\Rdex\Entity\RdexJourney;
use App\Rdex\Entity\RdexError;
use App\Rdex\Entity\RdexDriver;
use App\Rdex\Entity\RdexPassenger;
use App\Rdex\Entity\RdexAddress;
use App\Carpool\Entity\Criteria;
use App\Carpool\Entity\Result;
use App\Carpool\Entity\ResultItem;
use App\Carpool\Service\AdManager;
use App\Rdex\Entity\RdexConnection;
use App\Rdex\Entity\RdexConnectionUser;
use App\Rdex\Entity\RdexDay;
use App\Rdex\Entity\RdexTripDate;
use App\Rdex\Entity\RdexDayTime;
use Symfony\Component\HttpFoundation\Request;
use App\Communication\Service\NotificationManager;
use App\Rdex\Event\RdexConnectionEvent;
use App\User\Service\UserManager;

/**
 * Rdex operations manager.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class RdexManager
{
    private const RDEX_CONFIG_FILE = "../config/rdex/clients.json";
    private const RDEX_OPERATOR_FILE = "../config/rdex/operator.json";
    private const RDEX_HASH = "sha256";         // hash algorithm
    private const MIN_TIMESTAMP_MINUTES = 60;   // accepted minutes for timestamp in the past
    private const MAX_TIMESTAMP_MINUTES = 60;   // accepted minutes for timestamp in the future
    private const IMAGE_VERSION = "square_250";

    // false for testing purpose only
    private const CHECK_SIGNATURE = false;
    
    private $adManager;
    private $notificationManager;
    private $userManager;

    private $clientKey; // Current client key in configuration file (clients.json)
    private $operator; // Operator information (operator.json)
    
    /**
     * Constructor.
     *
     * @param ProposalManager $proposalManager
     */
    public function __construct(AdManager $adManager, NotificationManager $notificationManager, UserManager $userManager)
    {
        $this->adManager = $adManager;
        $this->notificationManager = $notificationManager;
        $this->userManager = $userManager;
    }
    
    
    /**
     * Check if the request signature is valid
     *
     * @param Request $request
     * @param string $privateApiKey
     * @return RdexError|bool True if validation is ok, error if not
     */
    public function checkSignature(Request $request, string $privateApiKey)
    {
        // we check the signature
        if (self::CHECK_SIGNATURE) {
            $signatureValid = false;


            $posSignature = strpos($request->getUri(), "&signature=");
            if ($posSignature === false) {
                // the signature is the first parameter
                $posSignature = strpos($request->getUri(), "signature=");
            }

            // we search for the end of the signature (we add 1 to avoid getting the current &)
            $posEndSignature = strpos($request->getUri(), "&", $posSignature+1);
            if ($posEndSignature !== false) {
                $unsignedUrl = substr_replace($request->getUri(), '', $posSignature, ($posEndSignature-$posSignature));
            } else {
                $unsignedUrl = substr_replace($request->getUri(), '', $posSignature);
            }

            $expectedSignature = hash_hmac(self::RDEX_HASH, $unsignedUrl, $privateApiKey);

            if ($expectedSignature == $request->get("signature")) {
                $signatureValid = true;
            }
            if (!$signatureValid) {
                return new RdexError("signature", RdexError::ERROR_SIGNATURE_MISMATCH, "Signature mismatch");
            }
        } else {
            return true;
        }
    }
    
    
    /**
     * Validates the parameters of a request.
     *
     * @param Request $request
     * @return RdexError|bool True if validation is ok, error if not
     * @throws \Exception
     */
    public function validate(Request $request)
    {
        // we check the mandatory parameters
        if (is_null($request->get("timestamp"))) {
            return new RdexError("timestamp", RdexError::ERROR_MISSING_MANDATORY_FIELD);
        }
        if (is_null($request->get("apikey"))) {
            return new RdexError("apikey", RdexError::ERROR_MISSING_MANDATORY_FIELD);
        }
        if (is_null($request->get("p"))) {
            return new RdexError("p", RdexError::ERROR_MISSING_MANDATORY_FIELD);
        }
        if (is_null($request->get("signature"))) {
            return new RdexError("signature", RdexError::ERROR_MISSING_MANDATORY_FIELD);
        }
        $timestamp = $request->get("timestamp");
        $apikey = $request->get("apikey");
        $signature = $request->get("signature");
        $p = $request->get("p");
        if (!isset($p['driver']['state'])) {
            return new RdexError("p[driver][state]", RdexError::ERROR_MISSING_MANDATORY_FIELD);
        }
        if (!isset($p['passenger']['state'])) {
            return new RdexError("p[passenger][state]", RdexError::ERROR_MISSING_MANDATORY_FIELD);
        }
        if (!isset($p['from']['longitude'])) {
            return new RdexError("p[from][longitude]", RdexError::ERROR_MISSING_MANDATORY_FIELD);
        }
        if (!isset($p['from']['latitude'])) {
            return new RdexError("p[from][latitude]", RdexError::ERROR_MISSING_MANDATORY_FIELD);
        }
        if (!isset($p['to']['longitude'])) {
            return new RdexError("p[to][longitude]", RdexError::ERROR_MISSING_MANDATORY_FIELD);
        }
        if (!isset($p['to']['latitude'])) {
            return new RdexError("p[to][latitude]", RdexError::ERROR_MISSING_MANDATORY_FIELD);
        }
        
        // we verify the timestamp
        $minTime = new \DateTime();
        $maxTime = new \DateTime();
        $minTime->sub(new \DateInterval("PT".self::MIN_TIMESTAMP_MINUTES."M"));
        $maxTime->add(new \DateInterval("PT".self::MAX_TIMESTAMP_MINUTES."M"));
        $DTTimestamp = new \DateTime();
        $DTTimestamp->setTimestamp($timestamp);
        if ($DTTimestamp<$minTime || $DTTimestamp>$maxTime) {
            /**** UNCOMMENT WHEN DEV IS OVER */
//            return new RdexError("timestamp", RdexError::ERROR_TIMESTAMP_TOO_SKEWED);
        }

        // we check if the client exists in config
        if (!file_exists(self::RDEX_CONFIG_FILE)) {
            return new RdexError(null, RdexError::ERROR_MISSING_CONFIG);
        }
        $clientList = json_decode(file_get_contents(self::RDEX_CONFIG_FILE), true);
        $apikeyFound = false;
        $urlApikey = $request->get('apikey');
        $privateApiKey = null;
        foreach ($clientList as $currentClientKey => $currentClient) {
            if ($currentClient["public_key"]==$urlApikey) {
                $apikeyFound = true;
                $this->clientKey = $currentClientKey;
                $privateApiKey = $currentClient["private_key"];
            }
            
            if (!$apikeyFound) {
                return new RdexError("apikey", RdexError::ERROR_ACCESS_DENIED, "Invalid apikey");
            }
        }

        // we check the operator file. It's the id of the site and it will be sent by RDEX response
        // we check if the client exists in config
        if (!file_exists(self::RDEX_OPERATOR_FILE)) {
            return new RdexError(null, RdexError::ERROR_MISSING_OPERATOR);
        } else {
            $this->operator = json_decode(file_get_contents(self::RDEX_OPERATOR_FILE), true);
        }

        // we check the signature
        $checkSignature = $this->checkSignature($request, $privateApiKey);
        if ($checkSignature instanceof RdexError) {
            return $checkSignature;
        }

        $now = new \DateTime("midnight"); // we use 'midnight' to set the time to 0, as createFromFormat below sets the time to 0 if no time is provided
        // verification of outward min date
        if (isset($p['outward']['mindate'])) {
            $mindate = \DateTime::createFromFormat("Y-m-d", $p["outward"]["mindate"]);
            if ($mindate<$now) {
                return new RdexError("p[outward][mindate]", RdexError::ERROR_INVALID_INPUT, "Mindate must be greater than or equal to the current date");
            }
        }
        // verification of outward max date
        if (isset($p['outward']['maxdate'])) {
            $maxdate = \DateTime::createFromFormat("Y-m-d", $p["outward"]["maxdate"]);
            if ($maxdate<$now) {
                return new RdexError("p[outward][maxdate]", RdexError::ERROR_INVALID_INPUT, "Maxdate must be greater than or equal to the current date");
            }
        }
        // verification of outward min date / outward max date
        if (isset($p['outward']['mindate']) && isset($p['outward']['maxdate'])) {
            $mindate = \DateTime::createFromFormat("Y-m-d", $p["outward"]["mindate"]);
            $maxdate = \DateTime::createFromFormat("Y-m-d", $p["outward"]["maxdate"]);
            if ($mindate>$maxdate) {
                return new RdexError("p[outward][maxdate]", RdexError::ERROR_INVALID_INPUT, "Maxdate must be greater than or equal to mindate");
            }
        }
        
        // @todo : complete the checkings if needed
        
        return true;
    }

    /**
     * Checks if the request is empty.
     *
     * @param object $request
     * @return RdexError|bool True if request is empty, false if not
     */
    public function isEmptyRequest(object $request)
    {
        // we check the mandatory parameters
        if (is_null($request->get("timestamp")) && is_null($request->get("apikey")) && is_null($request->get("p")) && is_null($request->get("signature"))) {
            return true;
        }
        return false;
    }
    
    /**
     * Create an error array from an RdexError
     * @param RdexError $error
     * @return array
     */
    public function createError(RdexError $error): array
    {
        return [
            'error'=> json_encode([
                'error' => [
                    'name' => $error->getName(),
                    'message_debug' => $error->getMessageDebug(),
                    'message_user' => $error->getMessageUser(),
                    'field' => $error->getField()
                ]]),
            'code' => $error->getCode()
        ];
    }
    
    /**
     * Get the journeys from the proposals.
     *
     * @param array $parameters
     * @return array
     * @throws \Exception
     */
    public function getJourneys(array $parameters): array
    {
        $returnArray = [];

        if (is_null($this->clientKey)) {
            return new RdexError("apikey", RdexError::ERROR_ACCESS_DENIED, "Invalid apikey");
        }

        $ad = $this->adManager->getAdForRdex(
            $this->clientKey,
            $parameters["driver"]["state"],
            $parameters["passenger"]["state"],
            $parameters["from"]["longitude"],
            $parameters["from"]["latitude"],
            $parameters["to"]["longitude"],
            $parameters["to"]["latitude"],
            isset($parameters["frequency"]) ? $parameters["frequency"] : null,
            isset($parameters["days"]) ? $parameters["days"] : null,
            isset($parameters["outward"]) ? $parameters["outward"] : null
        );

        /**
         * @var Result $result
         */
        foreach ($ad->getResults() as $result) {
            $journey = new RdexJourney($result->getId());
            $journey->setOperator($this->operator['name']);
            $journey->setOrigin($this->operator['origin']);

            /** The url should be the detail of a the matching */
            $journey->setUrl($this->operator['url']);
            
            $journey->setType(RdexJourney::TYPE_ONE_WAY);
            if ($result->hasReturn()) {
                $journey->setType(RdexJourney::TYPE_ROUND_TRIP);
            }

            $carpoolerIsDriver = false;
            $carpoolerIsPassenger = false;
            $resultItem = null;
            if (!is_null($result->getResultPassenger()) && is_null($result->getResultDriver())) {
                $carpoolerIsDriver = true;
                $resultItem = $result->getResultPassenger();
                $roleRequester = "passenger";
            } elseif (is_null($result->getResultPassenger()) && !is_null($result->getResultDriver())) {
                $carpoolerIsPassenger = true;
                $resultItem = $result->getResultDriver();
                $roleRequester = "driver";
            } elseif (!is_null($result->getResultPassenger()) && !is_null($result->getResultDriver())) {
                $carpoolerIsDriver = true;
                $carpoolerIsPassenger = true;
                $resultItem = $result->getResultDriver();
                $roleRequester = "driver";
            } else {
                continue;
            }

            $driver = new RdexDriver($result->getCarpooler()->getId());
            $driver->setUuid($result->getCarpooler()->getId());
            $driver->setAlias($result->getCarpooler()->getGivenName()." ".$result->getCarpooler()->getShortFamilyName());
            
            ($result->getCarpooler()->getGender()==1) ? $driver->setGender('female') : ($result->getCarpooler()->getGender()==2) ? $driver->setGender('male') : null;
            
            $driver->setSeats($result->getSeatsDriver());
            $driver->setState(($carpoolerIsDriver) ? 1 : 0);

            if (count($result->getCarpooler()->getImages())>0) {
                $driver->setImage($result->getCarpooler()->getImages()[0]->getVersions()[self::IMAGE_VERSION]);
            }
            $journey->setDriver($driver);

            $passenger = new RdexPassenger($result->getCarpooler()->getId());
            $passenger->setUuid($result->getCarpooler()->getId());
            $passenger->setAlias($result->getCarpooler()->getGivenName()." ".$result->getCarpooler()->getShortFamilyName());
            
            ($result->getCarpooler()->getGender()==1) ? $passenger->setGender('female') : ($result->getCarpooler()->getGender()==2) ? $passenger->setGender('male') : null;
            
            $passenger->setState(($carpoolerIsPassenger) ? 1 : 0);

            if (count($result->getCarpooler()->getImages())>0) {
                $passenger->setImage($result->getCarpooler()->getImages()[0]->getVersions()[self::IMAGE_VERSION]);
            }
            $journey->setPassenger($passenger);


            $from = new RdexAddress();
            // We need to get the right address in resultsDriver or resultsPassenger given the situation
            // The requester only sent Lat/Lon so we can't use his request
            // We get some datas that relies on being passenger or driver
            $fromAddress = $resultItem->getOutward()->getOrigin();
            $toAddress = $resultItem->getOutward()->getDestination();
            $distance = $resultItem->getOutward()->getCommonDistance()+$result->getResultPassenger()->getOutward()->getDetourDistance();
            $kilometersPrice = $resultItem->getOutward()->getDriverPriceKm();


            $from->setAddress($fromAddress->getStreetAddress());
            $from->setPostalcode($fromAddress->getPostalCode());
            $from->setCity($fromAddress->getAddressLocality());
            $from->setCountry($fromAddress->getAddressCountry());
            $from->setLatitude($fromAddress->getLatitude());
            $from->setLongitude($fromAddress->getLongitude());
            $journey->setFrom($from);

            $to = new RdexAddress();
            $to->setAddress($toAddress->getStreetAddress());
            $to->setPostalcode($toAddress->getPostalCode());
            $to->setCity($toAddress->getAddressLocality());
            $to->setCountry($toAddress->getAddressCountry());
            $to->setLatitude($toAddress->getLatitude());
            $to->setLongitude($toAddress->getLongitude());
            $journey->setTo($to);

            
            // Metrics / Prices
            $journey->setDistance($distance);
//            $journey->setCost(['fixed'=>$result->getRoundedPrice()]);
            $journey->setCost(['variable'=>$kilometersPrice]);

            // Frequency
            $journey->setFrequency(($result->getFrequency()==1) ? 'puntual' : 'regular');
            
            

            // there's always an outward
            $infos = $this->buildJourneyDetails($result, $roleRequester, "outward");
            $journey->setDays($infos['days']);
            $journey->setOutward($infos['journey']);

            // If there is a return
            // TO DO : We don't treat return matching so we don't do it in RDEX also. Maybe one day...
            if (isset($parameters["return"]) && !is_null($parameters["return"]) && $result->hasReturn()) {
                $infos = $this->buildJourneyDetails($result, $roleRequester, "return");
                $journey->setReturn($infos['journey']);
            }

            $returnArray[] = $journey;
        }
        return $returnArray;
    }

    /**
     * Compute the min and max time considering the margin time
     *
     * @param \DateTime $time   Base time
     * @param integer $margin   Margin in seconds  to compute min and max time
     * @return array
     */
    private function computeMinMaxTime(\DateTime $time, int $margin)
    {
        $mintime = clone($time);
        $mintime->sub(new \DateInterval("PT" . $margin. "S"));
        $maxtime = clone($time);
        $maxtime->add(new \DateInterval("PT" . $margin. "S"));

        return [$mintime, $maxtime];
    }

    /**
     * Build the time infos of punctual or regular journey
     *
     * @param Result $result    The result from which we build the infos
     * @param string $role      The role of the requester
     * @param string $way       "outward" or "return" journey
     * @return array
     */
    private function buildJourneyDetails(Result $result, string $role, string $way)
    {
        if ($role=="passenger") {
            ($way=="outward") ? $journey = $result->getResultPassenger()->getOutward() : $journey = $result->getResultPassenger()->getReturn();
        } else {
            ($way=="outward") ? $journey = $result->getResultDriver()->getOutward() : $journey = $result->getResultDriver()->getReturn();
        }
        $days = new RdexDay();
        $infos = new RdexTripDate();
        $frequency = $result->getFrequency();

        if ($frequency==1) {
            // Punctual
            $puntualTime = $result->getTime();
            $punctualMargin = $journey->getMarginDuration();
            $date = $result->getDate();
            switch ($date->format("w")) {
                case 0: {
                    $days->setSunday(1);
                    $minMaxTime = $this->computeMinMaxTime($puntualTime, $punctualMargin);
                    $rdexDayTime = new RdexDayTime();
                    $rdexDayTime->setMintime($minMaxTime[0]->format("H:i:s"));
                    $rdexDayTime->setMaxtime($minMaxTime[1]->format("H:i:s"));
                    $infos->setSunday($rdexDayTime);
                    break;
                }
                case 1: {
                    $days->setMonday(1);
                    $minMaxTime = $this->computeMinMaxTime($puntualTime, $punctualMargin);
                    $rdexDayTime = new RdexDayTime();
                    $rdexDayTime->setMintime($minMaxTime[0]->format("H:i:s"));
                    $rdexDayTime->setMaxtime($minMaxTime[1]->format("H:i:s"));
                    $infos->setMonday($rdexDayTime);
                    break;
                }
                case 2: {
                    $days->setTuesday(1);
                    $minMaxTime = $this->computeMinMaxTime($puntualTime, $punctualMargin);
                    $rdexDayTime = new RdexDayTime();
                    $rdexDayTime->setMintime($minMaxTime[0]->format("H:i:s"));
                    $rdexDayTime->setMaxtime($minMaxTime[1]->format("H:i:s"));
                    $infos->setTuesday($rdexDayTime);
                    break;
                }
                case 3: {
                    $days->setWednesday(1);
                    $minMaxTime = $this->computeMinMaxTime($puntualTime, $punctualMargin);
                    $rdexDayTime = new RdexDayTime();
                    $rdexDayTime->setMintime($minMaxTime[0]->format("H:i:s"));
                    $rdexDayTime->setMaxtime($minMaxTime[1]->format("H:i:s"));
                    $infos->setWednesday($rdexDayTime);
                    break;
                }
                case 4: {
                    $days->setThursday(1);
                    $minMaxTime = $this->computeMinMaxTime($puntualTime, $punctualMargin);
                    $rdexDayTime = new RdexDayTime();
                    $rdexDayTime->setMintime($minMaxTime[0]->format("H:i:s"));
                    $rdexDayTime->setMaxtime($minMaxTime[1]->format("H:i:s"));
                    $infos->setThursday($rdexDayTime);
                    break;
                }
                case 5: {
                    $days->setFriday(1);
                    $minMaxTime = $this->computeMinMaxTime($puntualTime, $punctualMargin);
                    $rdexDayTime = new RdexDayTime();
                    $rdexDayTime->setMintime($minMaxTime[0]->format("H:i:s"));
                    $rdexDayTime->setMaxtime($minMaxTime[1]->format("H:i:s"));
                    $infos->setFriday($rdexDayTime);
                    break;
                }
                case 6: {
                    $days->setSaturday(1);
                    $minMaxTime = $this->computeMinMaxTime($puntualTime, $punctualMargin);
                    $rdexDayTime = new RdexDayTime();
                    $rdexDayTime->setMintime($minMaxTime[0]->format("H:i:s"));
                    $rdexDayTime->setMaxtime($minMaxTime[1]->format("H:i:s"));
                    $infos->setSaturday($rdexDayTime);
                    break;
                }
            }

            $infos->setMindate($date->format("Y-m-d"));
            $infos->setMaxdate($date->format("Y-m-d"));
        } else {
            // Regular
            if ($result->isMonCheck() && !is_null($journey->getMonTime())) {
                $days->setMonday(1);
                $rdexDayTime = new RdexDayTime();
                $minMaxTime = $this->computeMinMaxTime($journey->getMonTime(), $journey->getMonMarginDuration());
                $rdexDayTime->setMintime($minMaxTime[0]->format("H:i:s"));
                $rdexDayTime->setMaxtime($minMaxTime[0]->format("H:i:s"));
                $infos->setMonday($rdexDayTime);
            }
            if ($result->isTueCheck() && !is_null($journey->getTueTime())) {
                $days->setTuesday(1);
                $rdexDayTime = new RdexDayTime();
                $minMaxTime = $this->computeMinMaxTime($journey->getTueTime(), $journey->getTueMarginDuration());
                $rdexDayTime->setMintime($minMaxTime[0]->format("H:i:s"));
                $rdexDayTime->setMaxtime($minMaxTime[0]->format("H:i:s"));
                $infos->setMonday($rdexDayTime);
            }
            if ($result->isWedCheck() && !is_null($journey->getWedTime())) {
                $days->setWednesday(1);
                $rdexDayTime = new RdexDayTime();
                $minMaxTime = $this->computeMinMaxTime($journey->getWedTime(), $journey->getWedMarginDuration());
                $rdexDayTime->setMintime($minMaxTime[0]->format("H:i:s"));
                $rdexDayTime->setMaxtime($minMaxTime[0]->format("H:i:s"));
                $infos->setWednesday($rdexDayTime);
            }
            if ($result->isThuCheck() && !is_null($journey->getThuTime())) {
                $days->setThursday(1);
                $rdexDayTime = new RdexDayTime();
                $minMaxTime = $this->computeMinMaxTime($journey->getThuTime(), $journey->getThuMarginDuration());
                $rdexDayTime->setMintime($minMaxTime[0]->format("H:i:s"));
                $rdexDayTime->setMaxtime($minMaxTime[0]->format("H:i:s"));
                $infos->setThursday($rdexDayTime);
            }
            if ($result->isFriCheck() && !is_null($journey->getFriTime())) {
                $days->setFriday(1);
                $rdexDayTime = new RdexDayTime();
                $minMaxTime = $this->computeMinMaxTime($journey->getFriTime(), $journey->getFriMarginDuration());
                $rdexDayTime->setMintime($minMaxTime[0]->format("H:i:s"));
                $rdexDayTime->setMaxtime($minMaxTime[0]->format("H:i:s"));
                $infos->setFriday($rdexDayTime);
            }
            if ($result->isSatCheck() && !is_null($journey->getSatTime())) {
                $days->setSaturday(1);
                $rdexDayTime = new RdexDayTime();
                $minMaxTime = $this->computeMinMaxTime($journey->getSatTime(), $journey->getSatMarginDuration());
                $rdexDayTime->setMintime($minMaxTime[0]->format("H:i:s"));
                $rdexDayTime->setMaxtime($minMaxTime[0]->format("H:i:s"));
                $infos->setSaturday($rdexDayTime);
            }
            if ($result->isSunCheck() && !is_null($journey->getSunTime())) {
                $days->setSunday(1);
                $rdexDayTime = new RdexDayTime();
                $minMaxTime = $this->computeMinMaxTime($journey->getSunTime(), $journey->getSunMarginDuration());
                $rdexDayTime->setMintime($minMaxTime[0]->format("H:i:s"));
                $rdexDayTime->setMaxtime($minMaxTime[0]->format("H:i:s"));
                $infos->setSunday($rdexDayTime);
            }

            $infos->setMindate($journey->getFromDate()->format("Y-m-d"));
            $infos->setMaxdate($journey->getToDate()->format("Y-m-d"));
        }

        return ["days"=>$days, "journey"=>$infos];
    }

    /**
     * Validate a rdex connection post request
     *
     * @param Request $request
     * @return RdexError|bool True if validation is ok, error if not
     */
    public function validateConnection(Request $request)
    {
        // var_dump($request->get('driver'));die;

        if (is_null($request->get("timestamp"))) {
            return new RdexError("timestamp", RdexError::ERROR_MISSING_MANDATORY_FIELD);
        }

        // we check if the client exists in config
        if (!file_exists(self::RDEX_CONFIG_FILE)) {
            return new RdexError(null, RdexError::ERROR_MISSING_CONFIG);
        }
        $clientList = json_decode(file_get_contents(self::RDEX_CONFIG_FILE), true);
        $apikeyFound = false;
        $urlApikey = $request->get('apikey');
        $privateApiKey = null;
        foreach ($clientList as $currentClientKey => $currentClient) {
            if ($currentClient["public_key"]==$urlApikey) {
                $apikeyFound = true;
                $this->clientKey = $currentClientKey;
                $privateApiKey = $currentClient["private_key"];
            }
            
            if (!$apikeyFound) {
                return new RdexError("apikey", RdexError::ERROR_ACCESS_DENIED, "Invalid apikey");
            }
        }


        // Is there a driver
        if (
            is_null($request->get("driver")) ||
            !is_array($request->get("driver"))
        ) {
            return new RdexError("driver", RdexError::ERROR_MISSING_MANDATORY_FIELD);
        } else {
            // We check if state in in the authorized values
            if (!in_array($request->get("driver")['state'], RdexConnection::AUTHORIZED_STATE)) {
                return new RdexError("driver[state]", RdexError::ERROR_INVALID_INPUT, "driver[state] value must be in (".implode(",", RdexConnection::AUTHORIZED_STATE).")");
            } else {
                // If the driver is the recipient, the uuid must be given
                if ($request->get("driver")['state']==="recipient") {
                    if (
                        !isset($request->get("driver")['uuid']) ||
                        trim($request->get("driver")['uuid'])==="" ||
                        !is_numeric($request->get("driver")['uuid'])
                    ) {
                        return new RdexError("driver['uuid']", RdexError::ERROR_MISSING_MANDATORY_FIELD);
                    }
                }
            }
        }

        // Is there a passenger
        if (
            is_null($request->get("passenger")) ||
            !is_array($request->get("passenger"))
        ) {
            return new RdexError("passenger", RdexError::ERROR_MISSING_MANDATORY_FIELD);
        } else {
            // We check if state in in the authorized values
            if (!in_array($request->get("passenger")['state'], RdexConnection::AUTHORIZED_STATE)) {
                return new RdexError("passenger[state]", RdexError::ERROR_INVALID_INPUT, "passenger[state] value must be in (".implode(",", RdexConnection::AUTHORIZED_STATE).")");
            } else {
                // If the driver is the recipient, the uuid must be given
                if ($request->get("passenger")['state']==="recipient") {
                    if (
                        !isset($request->get("passenger")['uuid']) ||
                        trim($request->get("passenger")['uuid'])==="" ||
                        !is_numeric($request->get("passenger")['uuid'])
                    ) {
                        return new RdexError("passenger['uuid']", RdexError::ERROR_MISSING_MANDATORY_FIELD);
                    }
                }
            }
        }

        // There is a journeyId
        if (
            is_null($request->get("journeys")) ||
            !is_array($request->get("journeys")) ||
            (!isset($request->get('journeys')['uuid'])) ||
            is_null($request->get('journeys')['uuid']) ||
            $request->get('journeys')['uuid'] === ""
        ) {
            return new RdexError("journeys", RdexError::ERROR_MISSING_MANDATORY_FIELD);
        }

        // There is a message and it's not empty
        if (trim($request->get("details")) === "") {
            return new RdexError("details", RdexError::ERROR_MISSING_MANDATORY_FIELD);
        } else {
            if (strlen($request->get("details"))>RdexConnection::MAX_LENGTH_DETAILS) {
                return new RdexError("details", RdexError::ERROR_INVALID_INPUT, "Details length must be ".RdexConnection::MAX_LENGTH_DETAILS." maximum");
            }
        }

        // Finally we check the signature
        $checkSignature = $this->checkSignature($request, $privateApiKey);
        if ($checkSignature instanceof RdexError) {
            return $checkSignature;
        }

        return true;
    }

    /**
     * Handle a RDEX Connection request
     * For now, we are sending an email to the recipient
     *
     * @param Request $request
     * @return RdexError|bool True if validation is ok, error if not
     */
    public function sendConnection(Request $request)
    {
        // we check the operator file. It's the id of the site and it will be sent by RDEX response
        // we check if the client exists in config
        if (!file_exists(self::RDEX_OPERATOR_FILE)) {
            return new RdexError(null, RdexError::ERROR_MISSING_OPERATOR);
        } else {
            $this->operator = json_decode(file_get_contents(self::RDEX_OPERATOR_FILE), true);
        }

        $rdexConnection = new RdexConnection();

        // The message
        $rdexConnection->setDetails($request->get("details"));

        $rdexConnection->setOperator($this->operator['name']);
        $rdexConnection->setOrigin($this->operator['origin']);

        
        // The futur recipient of the message
        $recipient = null;
        
        // Driver
        $rdexDriver = new RdexConnectionUser();
        $rdexDriver->setState($request->get("driver")['state']);
        if (isset($request->get("driver")['alias'])) {
            $rdexDriver->setAlias($request->get("driver")['alias']);
        }
        if ($request->get("driver")['state']=="recipient") {
            $rdexDriver->setUuid($request->get("driver")['uuid']);
            $recipient = $this->userManager->getUser($request->get("driver")['uuid']);
        }
        $rdexConnection->setDriver($rdexDriver);

        // Passenger
        $rdexPassenger = new RdexConnectionUser();
        $rdexPassenger->setState($request->get("passenger")['state']);
        if (isset($request->get("passenger")['alias'])) {
            $rdexPassenger->setAlias($request->get("passenger")['alias']);
        }
        if ($request->get("passenger")['state']=="recipient") {
            $rdexPassenger->setUuid($request->get("passenger")['uuid']);
            $recipient = $this->userManager->getUser($request->get("passenger")['uuid']);
        }
        $rdexConnection->setPassenger($rdexPassenger);

        // Journeys
        $rdexConnection->setJourneysId($request->get('journeys')['uuid']);

        // We dispatch a notification
        if (!is_null($recipient)) {
            $this->notificationManager->notifies(RdexConnectionEvent::NAME, $recipient, $rdexConnection);
        } else {
            return new RdexError("recipient", RdexError::ERROR_UNKNOWN_USER);
        }

        return true;
    }
}
