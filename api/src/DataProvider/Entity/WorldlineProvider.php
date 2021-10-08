<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\DataProvider\Entity;

use App\Carpool\Entity\Criteria;
use App\DataProvider\Service\DataProvider;
use App\Payment\Entity\CarpoolItem;
use App\User\Entity\User;
use App\User\Interfaces\ConsumptionFeedbackInterface;
use Psr\Log\LoggerInterface;

/**
 * Worldline Provider
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class WorldlineProvider implements ConsumptionFeedbackInterface
{
    const GRANT_TYPE = "client_credentials";
    const CONSUMPTION_TYPE = "FIXED_FEE";

    const STEPS_TYPE = "TRAVEL";
    const STEPS_TRANSPORT_MODE = "MOVICI";
    const STEPS_IS_PM_CHARGEABLE = false;

    //const TEST_SSO_ACCOUNT_ID = "36";

    const ADDITIONAL_INFOS = [
        ["key" => "TYPE", "value" => "CONSUMPTION"]
    ];

    private $clientId;
    private $clientSecret;
    private $baseUrlAuth;
    private $baseUrl;
    private $apiKey;
    private $appId;
    private $authChain;
    private $logger;

    /**
     * @var string
     */
    private $accessToken;

    /**
     * @var CarpoolItem
     */
    private $consumptionCarpoolItem;
    
    /**
     * @var User
     */
    private $consumptionUser;
    
    /**
     * @var array
     */
    private $requestBody;

    /**
     * @var string
     */
    private $externalActivityId;

    public function __construct(string $clientId, string $clientSecret, string $baseUrlAuth, string $baseUrl, string $apiKey, int $appId, LoggerInterface $logger)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->baseUrlAuth = $baseUrlAuth;
        $this->baseUrl = $baseUrl;
        $this->authChain = "Basic ".base64_encode($clientId.":".$clientSecret);
        $this->apiKey = $apiKey;
        $this->appId = $appId;
        $this->requestBody = [];
        $this->logger = $logger;
    }

    /**
     * Get the auth token
     */
    public function auth()
    {
        $dataProvider = new DataProvider($this->baseUrlAuth);

        $body['grant_type'] = self::GRANT_TYPE;

        $headers = [
            "Authorization" => $this->authChain
        ];

        $this->logger->info("Authentification : ".$this->baseUrlAuth);

        $response = $dataProvider->postCollection($body, $headers, null, DataProvider::BODY_TYPE_FORM_PARAMS);

        $this->logger->info("Result Code : ".$response->getCode());

        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
        } else {
            throw new \LogicException("Auth failed");
        }
        $this->setAccessToken($data['access_token']);
    }

    /**
     * Send a consumption feedback
     */
    public function sendConsumptionFeedback()
    {
        $this->setConsumptionUser($this->getConsumptionCarpoolItem()->getDebtorUser());
        if ($this->checkUserForSso() && $this->getConsumptionCarpoolItem()->getDebtorConsumptionFeedbackReturnCode()!==200) {
            $this->sendConsumptionFeedbackRequest();
        }

        $this->setConsumptionUser($this->getConsumptionCarpoolItem()->getCreditorUser());
        if ($this->checkUserForSso() && $this->getConsumptionCarpoolItem()->getDebtorConsumptionFeedbackReturnCode()!==200) {
            $this->sendConsumptionFeedbackRequest();
        }

        $this->setRequestBody([]);
    }

    /**
     * Check if the User has been created by Sso
     *
     * @param User $user
     * @return boolean
     */
    private function checkUserForSso(): bool
    {
        return is_null($this->getConsumptionUser()->getSsoId()) && !is_null($this->getConsumptionUser()->getAppDelegate()) && $this->getConsumptionUser()->getAppDelegate()->getId() === $this->appId;
    }


    /**
     * Build the body of the request of consumption feedback
     */
    private function buildConsumptionFeedbackForUser()
    {
        $this->setExternalActivityId(null);
        if ($this->getConsumptionUser()->getId()==$this->getConsumptionCarpoolItem()->getAsk()->getMatching()->getProposalOffer()->getUser()->getId()) {
            $externalActivityId = $this->getConsumptionCarpoolItem()->getAsk()->getMatching()->getProposalOffer()->getId();
            $price =  $this->getConsumptionCarpoolItem()->getAsk()->getCriteria()->getDriverComputedRoundedPrice();
        } elseif ($this->getConsumptionUser()->getId()==$this->getConsumptionCarpoolItem()->getAsk()->getMatching()->getProposalRequest()->getUser()->getId()) {
            $externalActivityId = $this->getConsumptionCarpoolItem()->getAsk()->getMatching()->getProposalRequest()->getId();
            $price =  $this->getConsumptionCarpoolItem()->getAsk()->getCriteria()->getPassengerComputedRoundedPrice();
        } else {
            return [];
        }
        
        // Just a fail safe but if we have a carpool item, it's obviously a carpooled day
        $carpooled = false;

        // start date
        $askCriteria = $this->getConsumptionCarpoolItem()->getAsk()->getCriteria();

        $beginDate = $this->getConsumptionCarpoolItem()->getItemDate();
        if ($askCriteria->getFrequency() == Criteria::FREQUENCY_PUNCTUAL) {
            $beginDate->setTime($askCriteria->getFromTime()->format('H'), $askCriteria->getFromTime()->format('i'), $askCriteria->getFromTime()->format('s'));
            $endDate = clone $beginDate;
            $endDate = $endDate->modify("+".$this->getConsumptionCarpoolItem()->getAsk()->getMatching()->getNewDuration()." second");
            $carpooled = true;
        } else {
            switch ($this->getConsumptionCarpoolItem()->getItemDate()->format("w")) {
                case 0:
                    if ($askCriteria->isSunCheck()) {
                        $beginDate->setTime($askCriteria->getSunTime()->format('H'), $askCriteria->getSunTime()->format('i'), $askCriteria->getSunTime()->format('s'));
                        $carpooled = true;
                    }
                    break;
                case 1:
                    if ($askCriteria->isMonCheck()) {
                        $beginDate->setTime($askCriteria->getMonTime()->format('H'), $askCriteria->getMonTime()->format('i'), $askCriteria->getMonTime()->format('s'));
                        $carpooled = true;
                    }
                    break;
                case 2:
                    if ($askCriteria->isTueCheck()) {
                        $beginDate->setTime($askCriteria->getTueTime()->format('H'), $askCriteria->getTueTime()->format('i'), $askCriteria->getTueTime()->format('s'));
                        $carpooled = true;
                    }
                    break;
                case 3:
                    if ($askCriteria->isWedCheck()) {
                        $beginDate->setTime($askCriteria->getWedTime()->format('H'), $askCriteria->getWedTime()->format('i'), $askCriteria->getWedTime()->format('s'));
                        $carpooled = true;
                    }
                    break;
                case 4:
                    if ($askCriteria->isThuCheck()) {
                        $beginDate->setTime($askCriteria->getThuTime()->format('H'), $askCriteria->getThuTime()->format('i'), $askCriteria->getThuTime()->format('s'));
                        $carpooled = true;
                    }
                    break;
                case 5:
                    if ($askCriteria->isFriCheck()) {
                        $beginDate->setTime($askCriteria->getFriTime()->format('H'), $askCriteria->getFriTime()->format('i'), $askCriteria->getFriTime()->format('s'));
                        $carpooled = true;
                    }
                    break;
                case 6:
                    if ($askCriteria->isSatCheck()) {
                        $beginDate->setTime($askCriteria->getSatTime()->format('H'), $askCriteria->getSatTime()->format('i'), $askCriteria->getSatTime()->format('s'));
                        $carpooled = true;
                    }
                    break;
                default: break;
            }
            
            if ($carpooled) {
                $endDate = clone $beginDate;
                $endDate = $endDate->modify("+".$this->getConsumptionCarpoolItem()->getAsk()->getMatching()->getNewDuration()." second");
            }
        }


        if ($carpooled) {
            $this->setExternalActivityId((microtime(true)*10000)."|".$externalActivityId);
            $this->setRequestBody([
                "accountId" => (defined('static::TEST_SSO_ACCOUNT_ID')) ? self::TEST_SSO_ACCOUNT_ID : $this->getConsumptionUser()->getId(),
                "consumptionType" => self::CONSUMPTION_TYPE,
                "externalActivityId" => $this->getExternalActivityId(),
                "steps" => [
                    [
                        "beginDate" => $beginDate->format('c'),
                        "endDate" => $endDate->format('c'),
                        "type" => self::STEPS_TYPE,
                        "transportMode" => self::STEPS_TRANSPORT_MODE,
                        "financialData" => [
                            "initialAmount" => round($price, 2),
                            "initialAmountExcdTax" => round($price / (1 + 0.20), 2),
                            "isPMChargeable" => self::STEPS_IS_PM_CHARGEABLE
                        ]
                    ]
                ],
                "additionalInformations" => self::ADDITIONAL_INFOS
            ]);
        }
    }

    /**
     * Send the consumption feedback to the API
     */
    private function sendConsumptionFeedbackRequest()
    {
        $this->buildConsumptionFeedbackForUser();

        if (is_null($this->getRequestBody()) || count($this->getRequestBody())==0) {
            return;
        }

        $dataProvider = new DataProvider($this->baseUrl);

        $headers = [
            "Authorization" => "Bearer ".$this->getAccessToken(),
            "x-apikey" => $this->apiKey
        ];

        $this->logger->info("Send consumption feedback for User ".$this->getConsumptionUser()->getId()." externalActivityId : ".$this->getExternalActivityId());
        $this->logger->info(json_encode($this->getRequestBody()));

        $response = $dataProvider->putItem($this->getRequestBody(), $headers, null, DataProvider::BODY_TYPE_JSON);

        $this->logger->info("Result Code : ".$response->getCode());
        
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
        } else {
            $this->logger->info("Request failed ! ");
            die;
            //throw new \LogicException("Request failed");
        }

        // Store some data
        if ($this->getConsumptionCarpoolItem()->getDebtorUser()->getId()===$this->getConsumptionUser()->getId()) {
            $this->getConsumptionCarpoolItem()->setDebtorConsumptionFeedbackExternalId($this->getExternalActivityId());
            $this->getConsumptionCarpoolItem()->setDebtorConsumptionFeedbackDate(new \DateTime('now'));
            $this->getConsumptionCarpoolItem()->setDebtorConsumptionFeedbackReturnCode($response->getCode());
        } else {
            $this->getConsumptionCarpoolItem()->setCreditorConsumptionFeedbackExternalId($this->getExternalActivityId());
            $this->getConsumptionCarpoolItem()->setCreditorConsumptionFeedbackDate(new \DateTime('now'));
            $this->getConsumptionCarpoolItem()->setCreditorConsumptionFeedbackReturnCode($response->getCode());
        }
    }

    
    // Getters / Setters
    
    public function getConsumptionUser(): ?User
    {
        return $this->consumptionUser;
    }

    public function setConsumptionUser(?User $consumptionUser)
    {
        $this->consumptionUser = $consumptionUser;
    }

    public function getConsumptionCarpoolItem(): ?CarpoolItem
    {
        return $this->consumptionCarpoolItem;
    }

    public function setConsumptionCarpoolItem(?CarpoolItem $consumptionCarpoolItem)
    {
        $this->consumptionCarpoolItem = $consumptionCarpoolItem;
    }

    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    public function getRequestBody(): ?array
    {
        return $this->requestBody;
    }

    public function setRequestBody(array $requestBody)
    {
        $this->requestBody = $requestBody;
    }

    public function getExternalActivityId(): ?string
    {
        return $this->externalActivityId;
    }

    public function setExternalActivityId(?string $externalActivityId)
    {
        $this->externalActivityId = $externalActivityId;
    }
}
