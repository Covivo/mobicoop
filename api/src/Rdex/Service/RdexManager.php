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

use App\Carpool\Service\ProposalManager;
use App\Rdex\Entity\RdexJourney;
use App\Rdex\Entity\RdexError;
use App\Carpool\Entity\Proposal;
use App\Rdex\Entity\RdexDriver;
use App\Rdex\Entity\RdexPassenger;
use App\Rdex\Entity\RdexAddress;
use App\Carpool\Entity\Criteria;
use App\Carpool\Service\AdManager;
use App\Rdex\Entity\RdexDay;
use App\Rdex\Entity\RdexTripDate;
use App\Rdex\Entity\RdexDayTime;
use Symfony\Component\HttpFoundation\Request;

/**
 * Rdex operations manager.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class RdexManager
{
    private const RDEX_CONFIG_FILE = "../config/rdex/clients.json";
    private const RDEX_OPERATOR_KEY = "rdexOperator";
    private const RDEX_HASH = "sha256";         // hash algorithm
    private const MIN_TIMESTAMP_MINUTES = 60;   // accepted minutes for timestamp in the past
    private const MAX_TIMESTAMP_MINUTES = 60;   // accepted minutes for timestamp in the future
    // for testing purpose only
    private const CHECK_SIGNATURE = true;
    
    private $proposalManager;
    private $adManager;

    private $clientKey; // Current client key in configuration file (clients.json)
    
    /**
     * Constructor.
     *
     * @param ProposalManager $proposalManager
     */
    public function __construct(ProposalManager $proposalManager, AdManager $adManager)
    {
        $this->proposalManager = $proposalManager;
        $this->adManager = $adManager;
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
            // if (array_key_exists("publicKey", $keys) && array_key_exists("privateKey", $keys) && $keys["publicKey"] == $apikey) {
            //     $url = $request->getUri();
            //     // we search if the signature is not the first parameter
            //     $posSignature = strpos($url, "&signature=");
            //     if ($posSignature === false) {
            //         // the signature is the first parameter
            //         $posSignature = strpos($url, "signature=");
            //     }
            // we search for the end of the signature (we add 1 to avoid getting the current &)
            // $posEndSignature = strpos($url, "&", $posSignature+1);
            // if ($posEndSignature !== false) {
            //     $unsignedUrl = substr_replace($url, '', $posSignature, ($posEndSignature-$posSignature));
            // } else {
            //     $unsignedUrl = substr_replace($url, '', $posSignature);
            // }
            //     $expectedSignature = hash_hmac(self::RDEX_HASH, $unsignedUrl, $keys["privateKey"]);
            //     //echo $expectedSignature;exit;
            //     if ($expectedSignature != $signature) {
            //         return new RdexError("signature", RdexError::ERROR_SIGNATURE_MISMATCH, "Invalid signature");
            //     }
            //     $apikeyFound = true;
            //     break;
            // }
            
            if ($currentClient["public_key"]==$urlApikey) {
                $apikeyFound = true;
                $this->clientKey = $currentClientKey;
                $privateApiKey = $currentClient["private_key"];
            }
            
            if (!$apikeyFound) {
                return new RdexError("apikey", RdexError::ERROR_ACCESS_DENIED, "Invalid apikey");
            }
        }

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
        echo $this->clientKey;
        die;
        $returnArray = [];

        $config = json_decode(file_get_contents(self::RDEX_CONFIG_FILE), true);
        $operator = $config[self::RDEX_OPERATOR_KEY];
        
        $ads = $this->adManager->getAdsForRdex(
            $parameters["driver"]["state"],
            $parameters["passenger"]["state"],
            $parameters["from"]["longitude"],
            $parameters["from"]["latitude"],
            $parameters["to"]["longitude"],
            $parameters["to"]["latitude"],
            isset($parameters["frequency"]) ? $parameters["frequency"] : null,
            isset($parameters["outward"]["mindate"]) ? \DateTime::createFromFormat("Y-m-d", $parameters["outward"]["mindate"]) : null,
            isset($parameters["outward"]["maxdate"]) ? \DateTime::createFromFormat("Y-m-d", $parameters["outward"]["maxdate"]) : null,
            isset($parameters["outward"]["monday"]["mintime"]) ? $parameters["outward"]["monday"]["mintime"] : null,
            isset($parameters["outward"]["monday"]["maxtime"]) ? $parameters["outward"]["monday"]["maxtime"] : null,
            isset($parameters["outward"]["tuesday"]["mintime"]) ? $parameters["outward"]["tuesday"]["mintime"] : null,
            isset($parameters["outward"]["tuesday"]["maxtime"]) ? $parameters["outward"]["tuesday"]["maxtime"] : null,
            isset($parameters["outward"]["wednesday"]["mintime"]) ? $parameters["outward"]["wednesday"]["mintime"] : null,
            isset($parameters["outward"]["wednesday"]["maxtime"]) ? $parameters["outward"]["wednesday"]["maxtime"] : null,
            isset($parameters["outward"]["thursday"]["mintime"]) ? $parameters["outward"]["thursday"]["mintime"] : null,
            isset($parameters["outward"]["thursday"]["maxtime"]) ? $parameters["outward"]["thursday"]["maxtime"] : null,
            isset($parameters["outward"]["friday"]["mintime"]) ? $parameters["outward"]["friday"]["mintime"] : null,
            isset($parameters["outward"]["friday"]["maxtime"]) ? $parameters["outward"]["friday"]["maxtime"] : null,
            isset($parameters["outward"]["saturday"]["mintime"]) ? $parameters["outward"]["saturday"]["mintime"] : null,
            isset($parameters["outward"]["saturday"]["maxtime"]) ? $parameters["outward"]["saturday"]["maxtime"] : null,
            isset($parameters["outward"]["sunday"]["mintime"]) ? $parameters["outward"]["sunday"]["mintime"] : null,
            isset($parameters["outward"]["sunday"]["maxtime"]) ? $parameters["outward"]["sunday"]["maxtime"] : null
        );


        die;

        foreach ($proposals as $proposal) {
            // @todo : create a rule for uuid creation
            $journey = new RdexJourney($proposal->getId());
            $journey->setOperator($operator['name']);
            $journey->setOrigin($operator['origin']);
            $journey->setUrl($operator['url']);
            // by default the type is one-way
            // if the proposal is the return of a round trip, it is considered as a one-way
            // the type will be round trip only if the proposal type is outward
            $journey->setType(RdexJourney::TYPE_ONE_WAY);
            if ($proposal->getJourneyType() == Proposal::JOURNEY_TYPE_OUTWARD) {
                $journey->setType(RdexJourney::TYPE_ROUND_TRIP);
            }
            $driver = new RdexDriver($proposal->getUSer()->getId());
            // @todo : add alias to user entity
            $driver->setAlias($proposal->getUser()->getGivenName());
            $driver->setGender($proposal->getUser()->getGender());
            $passenger = new RdexPassenger($proposal->getUSer()->getId());
            $passenger->setAlias($proposal->getUser()->getGivenName());
            $passenger->setGender($proposal->getUser()->getGender());
            if ($proposal->getProposalType() == Proposal::PROPOSAL_TYPE_OFFER) {
                $driver->setSeats($proposal->getCriteria()->getSeatsDriver());
                $driver->setState(1);
                $passenger->setState(0);
                $journey->setDriver($driver);
                $journey->setPassenger($passenger);
            } else {
                $driver->setState(0);
                $passenger->setState(1);
                $passenger->setPersons($proposal->getCriteria()->getSeatsPassenger());
            }
            $journey->setDriver($driver);
            $journey->setPassenger($passenger);
            foreach ($proposal->getPoints() as $point) {
                if ($point->getPosition() == 0) {
                    $from = new RdexAddress();
                    $from->setAddress($point->getAddress()->getStreetAddress());
                    $from->setPostalcode($point->getAddress()->getPostalCode());
                    $from->setCity($point->getAddress()->getAddressLocality());
                    $from->setCountry($point->getAddress()->getAddressCountry());
                    $from->setLatitude($point->getAddress()->getLatitude());
                    $from->setLongitude($point->getAddress()->getLongitude());
                    $journey->setFrom($from);
                } elseif ($point->getLastPoint()) {
                    $to = new RdexAddress();
                    $to->setAddress($point->getAddress()->getStreetAddress());
                    $to->setPostalcode($point->getAddress()->getPostalCode());
                    $to->setCity($point->getAddress()->getAddressLocality());
                    $to->setCountry($point->getAddress()->getAddressCountry());
                    $to->setLatitude($point->getAddress()->getLatitude());
                    $to->setLongitude($point->getAddress()->getLongitude());
                    $journey->setTo($to);
                }
                // if we have 'from' and 'to' we don't check for any other point
                if (!is_null($journey->getFrom()) && !is_null($journey->getTo())) {
                    break;
                }
            }
            $days = new RdexDay();
            // there's always an outward
            $outward = new RdexTripDate();
            $outward->setMindate($proposal->getCriteria()->getFromDate()->format("Y-m-d"));
            if ($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                $journey->setFrequency(RdexJourney::FREQUENCY_PUNCTUAL);
                $outward->setMaxdate($proposal->getCriteria()->getFromDate()->format("Y-m-d"));
                $daytime = new RdexDayTime();
                // we compute the min and max time using php Datetime methods
                $time = \DateTime::createFromFormat("Y-m-d H:i:s", $proposal->getCriteria()->getFromDate()->format("Y-m-d") . " " . $proposal->getCriteria()->getFromTime()->format("H:i:s"));
                $mintime = clone($time);
                $mintime->sub(new \DateInterval("PT" . $proposal->getCriteria()->getMarginTime(). "S"));
                $maxtime = clone($time);
                $maxtime->add(new \DateInterval("PT" . $proposal->getCriteria()->getMarginTime(). "S"));
                $daytime->setMintime($mintime->format('H:i:s'));
                $daytime->setMaxtime($maxtime->format('H:i:s'));
                switch ($proposal->getCriteria()->getFromDate()->format('w')) {
                    case 0:    $days->setSunday(1);
                                $outward->setSunday($daytime);
                                break;
                    case 1:    $days->setMonday(1);
                                $outward->setMonday($daytime);
                                break;
                    case 2:    $days->setTuesday(1);
                                $outward->setTuesday($daytime);
                                break;
                    case 3:    $days->setWednesday(1);
                                $outward->setWednesday($daytime);
                                break;
                    case 4:    $days->setThursday(1);
                                $outward->setThursday($daytime);
                                break;
                    case 5:    $days->setFriday(1);
                                $outward->setFriday($daytime);
                                break;
                    case 6:    $days->setSaturday(1);
                                $outward->setSaturday($daytime);
                                break;
                }
            } else {
                $journey->setFrequency(RdexJourney::FREQUENCY_REGULAR);
                $outward->setMaxdate($proposal->getCriteria()->getToDate()->format("Y-m-d"));
                if ($proposal->getCriteria()->getMonCheck()) {
                    $days->setMonday(1);
                    $daytime = new RdexDayTime();
                    // we compute the min and max time using php Datetime methods
                    // for that we use the FromDate as support for the time; we could use any other date as we only keep the time part at the end
                    $time = \DateTime::createFromFormat("Y-m-d H:i:s", $proposal->getCriteria()->getFromDate()->format("Y-m-d") . " " . $proposal->getCriteria()->getMonTime()->format("H:i:s"));
                    $mintime = clone($time);
                    $mintime->sub(new \DateInterval("PT" . $proposal->getCriteria()->getMarginTime(). "S"));
                    $maxtime = clone($time);
                    $maxtime->add(new \DateInterval("PT" . $proposal->getCriteria()->getMarginTime(). "S"));
                    $daytime->setMintime($mintime->format('H:i:s'));
                    $daytime->setMaxtime($maxtime->format('H:i:s'));
                    $outward->setMonday($daytime);
                }
                if ($proposal->getCriteria()->getTueCheck()) {
                    $days->setTuesday(1);
                    $daytime = new RdexDayTime();
                    // we compute the min and max time using php Datetime methods
                    // for that we use the FromDate as support for the time; we could use any other date as we only keep the time part at the end
                    $time = \DateTime::createFromFormat("Y-m-d H:i:s", $proposal->getCriteria()->getFromDate()->format("Y-m-d") . " " . $proposal->getCriteria()->getTueTime()->format("H:i:s"));
                    $mintime = clone($time);
                    $mintime->sub(new \DateInterval("PT" . $proposal->getCriteria()->getMarginTime(). "S"));
                    $maxtime = clone($time);
                    $maxtime->add(new \DateInterval("PT" . $proposal->getCriteria()->getMarginTime(). "S"));
                    $daytime->setMintime($mintime->format('H:i:s'));
                    $daytime->setMaxtime($maxtime->format('H:i:s'));
                    $outward->setTuesday($daytime);
                }
                if ($proposal->getCriteria()->getWedCheck()) {
                    $days->setWednesday(1);
                    $daytime = new RdexDayTime();
                    // we compute the min and max time using php Datetime methods
                    // for that we use the FromDate as support for the time; we could use any other date as we only keep the time part at the end
                    $time = \DateTime::createFromFormat("Y-m-d H:i:s", $proposal->getCriteria()->getFromDate()->format("Y-m-d") . " " . $proposal->getCriteria()->getWedTime()->format("H:i:s"));
                    $mintime = clone($time);
                    $mintime->sub(new \DateInterval("PT" . $proposal->getCriteria()->getMarginTime(). "S"));
                    $maxtime = clone($time);
                    $maxtime->add(new \DateInterval("PT" . $proposal->getCriteria()->getMarginTime(). "S"));
                    $daytime->setMintime($mintime->format('H:i:s'));
                    $daytime->setMaxtime($maxtime->format('H:i:s'));
                    $outward->setWednesday($daytime);
                }
                if ($proposal->getCriteria()->getThuCheck()) {
                    $days->setThursday(1);
                    $daytime = new RdexDayTime();
                    // we compute the min and max time using php Datetime methods
                    // for that we use the FromDate as support for the time; we could use any other date as we only keep the time part at the end
                    $time = \DateTime::createFromFormat("Y-m-d H:i:s", $proposal->getCriteria()->getFromDate()->format("Y-m-d") . " " . $proposal->getCriteria()->getThuTime()->format("H:i:s"));
                    $mintime = clone($time);
                    $mintime->sub(new \DateInterval("PT" . $proposal->getCriteria()->getMarginTime(). "S"));
                    $maxtime = clone($time);
                    $maxtime->add(new \DateInterval("PT" . $proposal->getCriteria()->getMarginTime(). "S"));
                    $daytime->setMintime($mintime->format('H:i:s'));
                    $daytime->setMaxtime($maxtime->format('H:i:s'));
                    $outward->setThursday($daytime);
                }
                if ($proposal->getCriteria()->getFriCheck()) {
                    $days->setFriday(1);
                    $daytime = new RdexDayTime();
                    // we compute the min and max time using php Datetime methods
                    // for that we use the FromDate as support for the time; we could use any other date as we only keep the time part at the end
                    $time = \DateTime::createFromFormat("Y-m-d H:i:s", $proposal->getCriteria()->getFromDate()->format("Y-m-d") . " " . $proposal->getCriteria()->getFriTime()->format("H:i:s"));
                    $mintime = clone($time);
                    $mintime->sub(new \DateInterval("PT" . $proposal->getCriteria()->getMarginTime(). "S"));
                    $maxtime = clone($time);
                    $maxtime->add(new \DateInterval("PT" . $proposal->getCriteria()->getMarginTime(). "S"));
                    $daytime->setMintime($mintime->format('H:i:s'));
                    $daytime->setMaxtime($maxtime->format('H:i:s'));
                    $outward->setFriday($daytime);
                }
                if ($proposal->getCriteria()->getSatCheck()) {
                    $days->setSaturday(1);
                    $daytime = new RdexDayTime();
                    // we compute the min and max time using php Datetime methods
                    // for that we use the FromDate as support for the time; we could use any other date as we only keep the time part at the end
                    $time = \DateTime::createFromFormat("Y-m-d H:i:s", $proposal->getCriteria()->getFromDate()->format("Y-m-d") . " " . $proposal->getCriteria()->getSatTime()->format("H:i:s"));
                    $mintime = clone($time);
                    $mintime->sub(new \DateInterval("PT" . $proposal->getCriteria()->getMarginTime(). "S"));
                    $maxtime = clone($time);
                    $maxtime->add(new \DateInterval("PT" . $proposal->getCriteria()->getMarginTime(). "S"));
                    $daytime->setMintime($mintime->format('H:i:s'));
                    $daytime->setMaxtime($maxtime->format('H:i:s'));
                    $outward->setSaturday($daytime);
                }
                if ($proposal->getCriteria()->getSunCheck()) {
                    $days->setSunday(1);
                    $daytime = new RdexDayTime();
                    // we compute the min and max time using php Datetime methods
                    // for that we use the FromDate as support for the time; we could use any other date as we only keep the time part at the end
                    $time = \DateTime::createFromFormat("Y-m-d H:i:s", $proposal->getCriteria()->getFromDate()->format("Y-m-d") . " " . $proposal->getCriteria()->getSunTime()->format("H:i:s"));
                    $mintime = clone($time);
                    $mintime->sub(new \DateInterval("PT" . $proposal->getCriteria()->getMarginTime(). "S"));
                    $maxtime = clone($time);
                    $maxtime->add(new \DateInterval("PT" . $proposal->getCriteria()->getMarginTime(). "S"));
                    $daytime->setMintime($mintime->format('H:i:s'));
                    $daytime->setMaxtime($maxtime->format('H:i:s'));
                    $outward->setSunday($daytime);
                }
            }
            $journey->setDays($days);
            $journey->setOutward($outward);
            if ($journey->getType() == RdexJourney::TYPE_ROUND_TRIP) {
                // creation of the return
                // we use the proposalLinkedJourney of the proposal
                $return = new RdexTripDate();
                $return->setMindate($proposal->getProposalLinkedJourney()->getCriteria()->getFromDate()->format("Y-m-d"));
                if ($proposal->getCriteria()->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
                    $return->setMaxdate($proposal->getProposalLinkedJourney()->getCriteria()->getFromDate()->format("Y-m-d"));
                    $daytime = new RdexDayTime();
                    // we compute the min and max time using php Datetime methods
                    $time = \DateTime::createFromFormat("Y-m-d H:i:s", $proposal->getProposalLinkedJourney()->getCriteria()->getFromDate()->format("Y-m-d") . " " . $proposal->getProposalLinkedJourney()->getCriteria()->getFromTime()->format("H:i:s"));
                    $mintime = clone($time);
                    $mintime->sub(new \DateInterval("PT" . $proposal->getProposalLinkedJourney()->getCriteria()->getMarginTime(). "S"));
                    $maxtime = clone($time);
                    $maxtime->add(new \DateInterval("PT" . $proposal->getProposalLinkedJourney()->getCriteria()->getMarginTime(). "S"));
                    $daytime->setMintime($mintime->format('H:i:s'));
                    $daytime->setMaxtime($maxtime->format('H:i:s'));
                    switch ($proposal->getCriteria()->getFromDate()->format('w')) {
                        case 0:    $return->setSunday($daytime);
                                    break;
                        case 1:    $return->setMonday($daytime);
                                    break;
                        case 2:    $return->setTuesday($daytime);
                                    break;
                        case 3:    $return->setWednesday($daytime);
                                    break;
                        case 4:    $return->setThursday($daytime);
                                    break;
                        case 5:    $return->setFriday($daytime);
                                    break;
                        case 6:    $return->setSaturday($daytime);
                                    break;
                    }
                } else {
                    $return->setMaxdate($proposal->getProposalLinkedJourney()->getCriteria()->getToDate()->format("Y-m-d"));
                    if ($proposal->getProposalLinkedJourney()->getCriteria()->getMonCheck()) {
                        $days->setMonday(1);
                        $daytime = new RdexDayTime();
                        // we compute the min and max time using php Datetime methods
                        // for that we use the FromDate as support for the time; we could use any other date as we only keep the time part at the end
                        $time = \DateTime::createFromFormat("Y-m-d H:i:s", $proposal->getProposalLinkedJourney()->getCriteria()->getFromDate()->format("Y-m-d") . " " . $proposal->getProposalLinkedJourney()->getCriteria()->getMonTime()->format("H:i:s"));
                        $mintime = clone($time);
                        $mintime->sub(new \DateInterval("PT" . $proposal->getProposalLinkedJourney()->getCriteria()->getMarginTime(). "S"));
                        $maxtime = clone($time);
                        $maxtime->add(new \DateInterval("PT" . $proposal->getProposalLinkedJourney()->getCriteria()->getMarginTime(). "S"));
                        $daytime->setMintime($mintime->format('H:i:s'));
                        $daytime->setMaxtime($maxtime->format('H:i:s'));
                        $return->setMonday($daytime);
                    }
                    if ($proposal->getProposalLinkedJourney()->getCriteria()->getTueCheck()) {
                        $days->setTuesday(1);
                        $daytime = new RdexDayTime();
                        // we compute the min and max time using php Datetime methods
                        // for that we use the FromDate as support for the time; we could use any other date as we only keep the time part at the end
                        $time = \DateTime::createFromFormat("Y-m-d H:i:s", $proposal->getProposalLinkedJourney()->getCriteria()->getFromDate()->format("Y-m-d") . " " . $proposal->getProposalLinkedJourney()->getCriteria()->getTueTime()->format("H:i:s"));
                        $mintime = clone($time);
                        $mintime->sub(new \DateInterval("PT" . $proposal->getProposalLinkedJourney()->getCriteria()->getMarginTime(). "S"));
                        $maxtime = clone($time);
                        $maxtime->add(new \DateInterval("PT" . $proposal->getProposalLinkedJourney()->getCriteria()->getMarginTime(). "S"));
                        $daytime->setMintime($mintime->format('H:i:s'));
                        $daytime->setMaxtime($maxtime->format('H:i:s'));
                        $return->setTuesday($daytime);
                    }
                    if ($proposal->getProposalLinkedJourney()->getCriteria()->getWedCheck()) {
                        $days->setWednesday(1);
                        $daytime = new RdexDayTime();
                        // we compute the min and max time using php Datetime methods
                        // for that we use the FromDate as support for the time; we could use any other date as we only keep the time part at the end
                        $time = \DateTime::createFromFormat("Y-m-d H:i:s", $proposal->getProposalLinkedJourney()->getCriteria()->getFromDate()->format("Y-m-d") . " " . $proposal->getProposalLinkedJourney()->getCriteria()->getWedTime()->format("H:i:s"));
                        $mintime = clone($time);
                        $mintime->sub(new \DateInterval("PT" . $proposal->getProposalLinkedJourney()->getCriteria()->getMarginTime(). "S"));
                        $maxtime = clone($time);
                        $maxtime->add(new \DateInterval("PT" . $proposal->getProposalLinkedJourney()->getCriteria()->getMarginTime(). "S"));
                        $daytime->setMintime($mintime->format('H:i:s'));
                        $daytime->setMaxtime($maxtime->format('H:i:s'));
                        $return->setWednesday($daytime);
                    }
                    if ($proposal->getProposalLinkedJourney()->getCriteria()->getThuCheck()) {
                        $days->setThursday(1);
                        $daytime = new RdexDayTime();
                        // we compute the min and max time using php Datetime methods
                        // for that we use the FromDate as support for the time; we could use any other date as we only keep the time part at the end
                        $time = \DateTime::createFromFormat("Y-m-d H:i:s", $proposal->getProposalLinkedJourney()->getCriteria()->getFromDate()->format("Y-m-d") . " " . $proposal->getProposalLinkedJourney()->getCriteria()->getThuTime()->format("H:i:s"));
                        $mintime = clone($time);
                        $mintime->sub(new \DateInterval("PT" . $proposal->getProposalLinkedJourney()->getCriteria()->getMarginTime(). "S"));
                        $maxtime = clone($time);
                        $maxtime->add(new \DateInterval("PT" . $proposal->getProposalLinkedJourney()->getCriteria()->getMarginTime(). "S"));
                        $daytime->setMintime($mintime->format('H:i:s'));
                        $daytime->setMaxtime($maxtime->format('H:i:s'));
                        $return->setThursday($daytime);
                    }
                    if ($proposal->getProposalLinkedJourney()->getCriteria()->getFriCheck()) {
                        $days->setFriday(1);
                        $daytime = new RdexDayTime();
                        // we compute the min and max time using php Datetime methods
                        // for that we use the FromDate as support for the time; we could use any other date as we only keep the time part at the end
                        $time = \DateTime::createFromFormat("Y-m-d H:i:s", $proposal->getProposalLinkedJourney()->getCriteria()->getFromDate()->format("Y-m-d") . " " . $proposal->getProposalLinkedJourney()->getCriteria()->getFriTime()->format("H:i:s"));
                        $mintime = clone($time);
                        $mintime->sub(new \DateInterval("PT" . $proposal->getProposalLinkedJourney()->getCriteria()->getMarginTime(). "S"));
                        $maxtime = clone($time);
                        $maxtime->add(new \DateInterval("PT" . $proposal->getProposalLinkedJourney()->getCriteria()->getMarginTime(). "S"));
                        $daytime->setMintime($mintime->format('H:i:s'));
                        $daytime->setMaxtime($maxtime->format('H:i:s'));
                        $return->setFriday($daytime);
                    }
                    if ($proposal->getProposalLinkedJourney()->getCriteria()->getSatCheck()) {
                        $days->setSaturday(1);
                        $daytime = new RdexDayTime();
                        // we compute the min and max time using php Datetime methods
                        // for that we use the FromDate as support for the time; we could use any other date as we only keep the time part at the end
                        $time = \DateTime::createFromFormat("Y-m-d H:i:s", $proposal->getProposalLinkedJourney()->getCriteria()->getFromDate()->format("Y-m-d") . " " . $proposal->getProposalLinkedJourney()->getCriteria()->getSatTime()->format("H:i:s"));
                        $mintime = clone($time);
                        $mintime->sub(new \DateInterval("PT" . $proposal->getProposalLinkedJourney()->getCriteria()->getMarginTime(). "S"));
                        $maxtime = clone($time);
                        $maxtime->add(new \DateInterval("PT" . $proposal->getProposalLinkedJourney()->getCriteria()->getMarginTime(). "S"));
                        $daytime->setMintime($mintime->format('H:i:s'));
                        $daytime->setMaxtime($maxtime->format('H:i:s'));
                        $return->setSaturday($daytime);
                    }
                    if ($proposal->getProposalLinkedJourney()->getCriteria()->getSunCheck()) {
                        $days->setSunday(1);
                        $daytime = new RdexDayTime();
                        // we compute the min and max time using php Datetime methods
                        // for that we use the FromDate as support for the time; we could use any other date as we only keep the time part at the end
                        $time = \DateTime::createFromFormat("Y-m-d H:i:s", $proposal->getProposalLinkedJourney()->getCriteria()->getFromDate()->format("Y-m-d") . " " . $proposal->getProposalLinkedJourney()->getCriteria()->getSunTime()->format("H:i:s"));
                        $mintime = clone($time);
                        $mintime->sub(new \DateInterval("PT" . $proposal->getProposalLinkedJourney()->getCriteria()->getMarginTime(). "S"));
                        $maxtime = clone($time);
                        $maxtime->add(new \DateInterval("PT" . $proposal->getProposalLinkedJourney()->getCriteria()->getMarginTime(). "S"));
                        $daytime->setMintime($mintime->format('H:i:s'));
                        $daytime->setMaxtime($maxtime->format('H:i:s'));
                        $return->setSunday($daytime);
                    }
                }
                $journey->setReturn($return);
            }
            $returnArray[] = $journey;
        }
        return $returnArray;
    }
}
