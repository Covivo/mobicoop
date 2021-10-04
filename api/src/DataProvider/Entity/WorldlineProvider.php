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

use App\DataProvider\Service\DataProvider;
use App\Payment\Entity\CarpoolItem;
use App\User\Entity\User;
use App\User\Interfaces\ConsumptionFeedbackInterface;

/**
 * Worldline Provider
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class WorldlineProvider implements ConsumptionFeedbackInterface
{
    const AUTHORIZATION_URL = "auth/realms/Partners/protocol/openid-connect/token";
    const GRANT_TYPE = "client_credentials";
    const CONSUMPTION_TYPE = "FIXED_FEE";

    const STEPS_TYPE = "TRAVEL";
    const STEPS_TRANSPORT_MODE = "MOVICI";
    const STEPS_IS_PM_CHARGEABLE = false;

    const ADDITIONAL_INFOS = [
        ["key" => "TYPE", "value" => "CONSUMPTION"]
    ];

    private $clientId;
    private $clientSecret;
    private $baseUrl;
    private $authChain;

    /**
     * @var CarpoolItem
     */
    private $consumptionCarpoolItem;
    /**
     * @var User
     */
    private $consumptionUser;
    

    public function __construct(string $clientId, string $clientSecret, string $baseUrl)
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->baseUrl = $baseUrl;
        $this->authChain = "Basic ".base64_encode($clientId.":".$clientSecret);
    }

    /**
     * Get the auth token
     *
     * @return string The auth token
     */
    public function auth(): string
    {
        $dataProvider = new DataProvider($this->baseUrl."/".self::AUTHORIZATION_URL);

        $body['grant_type'] = self::GRANT_TYPE;

        $headers = [
            "Authorization" => $this->authChain
        ];

        $response = $dataProvider->postCollection($body, $headers, null, DataProvider::BODY_TYPE_FORM_PARAMS);
        if ($response->getCode() == 200) {
            $data = json_decode($response->getValue(), true);
        } else {
            throw new \LogicException("Auth failed");
        }
        
        return $data['access_token'];
    }

    /**
     * Send a consumption feedback
     *
     * @return CarpoolItem The CarpoolItem related to this consumption
     */
    public function sendConsumptionFeedback(CarpoolItem $carpoolItem)
    {
        $this->consumptionCarpoolItem = $carpoolItem;

        $this->consumptionUser = $carpoolItem->getDebtorUser();
        //if($this->checkUserForSso()){
        var_dump(json_encode($this->buildConsumptionFeedbackForUser()));
        //}

        $this->consumptionUser = $carpoolItem->getCreditorUser();
        //if($this->checkUserForSso()){
        var_dump(json_encode($this->buildConsumptionFeedbackForUser()));
        //}
        
        die;
    }

    /**
     * Check if the User has been created by Sso
     *
     * @param User $user
     * @return boolean
     */
    private function checkUserForSso(): bool
    {
        return is_null($this->consumptionUser->getSsoId());
    }


    private function buildConsumptionFeedbackForUser(): array
    {
        if ($this->consumptionUser->getId()==$this->consumptionCarpoolItem->getAsk()->getMatching()->getProposalOffer()->getUser()->getId()) {
            $externalActivityId = $this->consumptionCarpoolItem->getAsk()->getMatching()->getProposalOffer()->getId();
        } elseif ($this->consumptionUser->getId()==$this->consumptionCarpoolItem->getAsk()->getMatching()->getProposalRequest()->getUser()->getId()) {
            $externalActivityId = $this->consumptionCarpoolItem->getAsk()->getMatching()->getProposalRequest()->getId();
        } else {
            return [];
        }
        
        return [
            "accoundId" => $this->consumptionUser->getSsoId(),
            "consumptionType" => self::CONSUMPTION_TYPE,
            "externalActivityId" => $externalActivityId,
            "steps" => [
                [
                    "beginDate" => "",
                    "endDate" => "",
                    "type" => self::STEPS_TYPE,
                    "transportMode" => self::STEPS_TRANSPORT_MODE,
                    "financialData" => [
                        "initialAmount" => 0,
                        "initialAmountExcdTax" => 0.0,
                        "isPMChargeable" => self::STEPS_IS_PM_CHARGEABLE
                    ]
                ]
            ],
            "additionalInformations" => self::ADDITIONAL_INFOS
        ];
    }
}
