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

namespace App\MassCommunication\Service;

use App\Communication\Entity\Medium;
use App\MassCommunication\Entity\Campaign;
use App\MassCommunication\MassEmailProvider\MandrillProvider;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Campaign manager service.
 */
class CampaignManager
{
    private $entityManager;
    private $massEmailProvider;
    private $massSmsProvider;

    const MAIL_PROVIDER_MANDRILL = 'mandrill';

    /**
     * Constructor.
     */
    public function __construct(string $mailerProvider, string $smsProvider, EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        switch ($mailerProvider) {
            case self::MAIL_PROVIDER_MANDRILL:
                $this->massEmailProvider = new MandrillProvider();
                break;
        }
    }

    /**
     * Send messages for a campaign.
     *
     * @param Campaign $campaign    The campaign to send the messages for
     * @return Campaign The campaign modified with the result of the send.
     */
    public function send(Campaign $campaign)
    {
        if ($campaign->getStatus() == Campaign::STATUS_CREATED) {
            switch ($campaign->getMedium()->getId()) {
                case Medium::MEDIUM_EMAIL:
                    return $this->sendMassEmail($campaign);
                    break;
                case Medium::MEDIUM_SMS:
                    return $this->sendMassSms($campaign);
                    break;
                default:
                    break;
            }
        }
        return $campaign;
    }
    
    /**
     * Send messages for a campaign by email.
     *
     * @param Campaign $campaign    The campaign to send the messages for
     * @return Campaign The campaign modified with the result of the send.
     */
    private function sendMassEmail(Campaign $campaign)
    {
        // call the service
        $this->massEmailProvider->send($campaign->getSubject(), $campaign->getFrom(), $campaign->getEmail(), $campaign->getReplyTo(), $this->getFormedEmailBody($campaign->getBody()), $this->getRecipientsFromDeliveries($campaign->getDeliveries()));
        
        // if the result of the send is returned here
        // foreach ($campaign->getDeliveries() as $delivery) {
        //     $delivery->setStatus(Delivery::STATUS_SENT);
        // }

        // persist the result depending of the status
        $campaign->setStatus(Campaign::STATUS_SENT);
        $this->entityManager->persist($campaign);
        
        return $campaign;
    }

    /**
     * Send messages for a campaign by sms.
     *
     * @param Campaign $campaign    The campaign to send the messages for
     * @return Campaign The campaign modified with the result of the send.
     */
    private function sendMassSms(Campaign $campaign)
    {
        // call the service
        
        // if the result of the send is returned here
        // foreach ($campaign->getDeliveries() as $delivery) {
        //     $delivery->setStatus(Delivery::STATUS_SENT);
        // }

        // persist the result depending of the status
        $campaign->setStatus(Campaign::STATUS_SENT);
        $this->entityManager->persist($campaign);
        
        return $campaign;
    }

    /**
     * Create a well-formed body for email send.
     *
     * @param string $body
     * @return void
     */
    private function getFormedEmailBody(?string $body): string
    {
        // todo
        return $body;
    }

    /**
     * Get an array of recipients email from an array of Delivery objects.
     *
     * @param array $deliveries
     * @return array
     */
    private function getRecipientsFromDeliveries(array $deliveries)
    {
        $recipients = [];
        foreach ($deliveries as $delivery) {
            $recipients[] = $delivery->getUser()->getEmail();
        }
        return $recipients;
    }
}
