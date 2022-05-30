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
use App\User\Entity\User;
use App\MassCommunication\Entity\Delivery;
use App\MassCommunication\Entity\Campaign;
use App\MassCommunication\Exception\CampaignNotFoundException;
use App\MassCommunication\Repository\CampaignRepository;
use App\Community\Repository\CommunityRepository;
use App\MassCommunication\Entity\Sender;
use App\MassCommunication\CampaignProvider\SendinBlueProvider;
use App\User\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Environment;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Campaign manager service.
 */
class CampaignManager
{
    private $templating;
    private $userRepository;
    private $communityRepository;
    private $entityManager;
    private $massEmailProvider;
    private $massSmsProvider;
    private $mailTemplate;
    private $campaignRepository;
    private $translator;

    const MAIL_PROVIDER_SENDINBLUE = 'SendinBlue';

    /**
     * Constructor.
     */
    public function __construct(
        Environment $templating,
        EntityManagerInterface $entityManager,
        string $mailerProvider,
        string $mailerApiUrl,
        string $mailerApiKey,
        string $mailerClientName,
        string $mailerClientId,
        string $mailerClientTemplateId,
        string $mailerReplyTo,
        string $mailerSenderEmail,
        string $mailerSenderName,
        string $mailerIp,
        string $mailerDomain,
        string $smsProvider,
        string $mailTemplate,
        CampaignRepository $campaignRepository,
        TranslatorInterface $translator,
        UserRepository $userRepository,
        CommunityRepository $communityRepository
    ) {
        $this->entityManager = $entityManager;
        $this->communityRepository = $communityRepository;
        $this->userRepository = $userRepository;
        $this->mailTemplate = $mailTemplate;
        $this->templating = $templating;
        $this->campaignRepository = $campaignRepository;
        $this->translator = $translator;
        $this->mailerProvider = $mailerProvider;
        $this->mailerApiUrl = $mailerApiUrl;
        $this->mailerApiKey = $mailerApiKey;
        $this->mailerClientName = $mailerClientName;
        $this->mailerClientId = $mailerClientId;
        $this->mailerClientTemplateId = $mailerClientTemplateId;
        $this->mailerReplyTo = $mailerReplyTo;
        $this->mailerSenderEmail = $mailerSenderEmail;
        $this->mailerSenderName = $mailerSenderName;
        $this->mailerIp = $mailerIp;
        $this->mailerDomain = $mailerDomain;
        switch ($mailerProvider) {
            case self::MAIL_PROVIDER_SENDINBLUE:
                $this->massEmailProvider = new SendinBlueProvider($mailerApiKey, $mailerClientId, $mailerSenderName, $mailerSenderEmail, $mailerReplyTo, $mailerClientTemplateId);
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
     * Send  the test messages for a campaign, to the sender
     *
     * @param Campaign $campaign    The campaign to send the messages for
     * @return Campaign The campaign modified with the result of the send.
     */
    public function sendTest(Campaign $campaign)
    {
        if (in_array($campaign->getStatus(), array( Campaign::STATUS_PENDING, Campaign::STATUS_CREATED))) {
            switch ($campaign->getMedium()->getId()) {
                case Medium::MEDIUM_EMAIL:
                    return $this->sendMassEmailTest($campaign);
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
        if ($sendAll = $campaign->getSendAll() != null) {
            //We try to send an email to all user, no matter the community
            if ($campaign->getSendAll() == 0) {
            }
        }
        // call the service
        $this->massEmailProvider->sendCampaign($campaign->getName(), $campaign->getProviderCampaignId());

        // persist the result depending of the status
        $campaign->setStatus(Campaign::STATUS_SENT);
        $this->entityManager->persist($campaign);
        $this->entityManager->flush();

        return $campaign;
    }

    /**
     * Send messages test for a campaign by email.
     *
     * @param Campaign $campaign    The campaign to test
     * @return Campaign The campaign modified with the result of the test.
     */
    private function sendMassEmailTest(Campaign $campaign, $lang='fr_FR')
    {
        // we set the sender
        $sender = new Sender;
        $sender->setUser($campaign->getUser());

        // we check if we have already create a provider campaign before
        if (is_null($campaign->getProviderCampaignId())) {
            // we create the campaign on provider side
            $providerCampaign = $this->massEmailProvider->createCampaign($campaign->getName(), $sender, $campaign->getSubject(), $this->getFormedEmailBody($campaign->getBody()), $campaign->getDeliveries());
            // We ad to the campaign the campaign provider id associated
            $campaign->setProviderCampaignId($providerCampaign['id']);
        }

        // We send the test email with as reciepient the email of the creator of the campaign
        $this->massEmailProvider->sendCampaignTest($campaign->getName(), $campaign->getProviderCampaignId(), [$campaign->getUser()->getEmail()]);

        $campaign->setStatus(Campaign::STATUS_CREATED);
        $this->entityManager->persist($campaign);
        $this->entityManager->flush();

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
     * Note : the context variables should be present in the template.
     *
     * @param string $body
     * @return void
     */
    private function getFormedEmailBody(?string $body):string
    {
        $encodedBody = json_decode($body);
        $arrayForTemplate = array();

        foreach ($encodedBody as $parts) {
            foreach ($parts as $type => $content) {
                $arrayForTemplate[] = array('type' => $type , 'content' => $content);
            }
        }

        return $this->templating->render(
            $this->mailTemplate,
            array('arrayForTemplate' => $arrayForTemplate)
        );
    }

    /**
     * Get an array of recipients with its context variables from an array of Delivery objects.
     *
     * @param array $deliveries
     * @return array
     */
    private function getRecipientsFromDeliveries(array $deliveries)
    {
        $recipients = [];
        foreach ($deliveries as $delivery) {
            $recipients[$delivery->getUser()->getEmail()] = [
                // put here the list of needed variables !
                "givenName" => $delivery->getUser()->getGivenName(),
                "familyName" => $delivery->getUser()->getFamilyName(),
                "email" => $delivery->getUser()->getEmail(),
                "unsubscribeToken" => $delivery->getUser()->getUnsubscribeToken(),
            ];
        }
        return $recipients;
    }

    /**
     * Build an array for send email to the sender
     *
     * @param User $user
     * @return array
     */
    private function setRecipientsIsOwner(User $user)
    {
        $recipients[$user->getEmail()] = [
            // put here the list of needed variables !
            "givenName" => $user->getGivenName(),
            "familyName" => $user->getFamilyName(),
            "email" => $user->getEmail(),
            "unsubscribeToken" => $user->getUnsubscribeToken(),
        ];

        return $recipients;
    }

    /**
     * Get the id of the owner of a campaign.
     *
     * @param integer $campaignId               The campaign id
     * @return int|CampaignNotFoundException    The user id
     */
    public function getCampaignOwner(int $campaignId)
    {
        if ($campaign = $this->campaignRepository->find($campaignId)) {
            return $campaign->getUser()->getId();
        }
        return new CampaignNotFoundException('Campaign not found');
    }


    /**
     * Set all user who accepted email to delieveries
     * Maybye TODO : set a value in db to check if we already set the deliveries like isSetAll
     *
     * @param Campaign $campaign
     * @return Campaign
     */
    public function setDeliveriesCampaignToAll(Campaign $campaign)
    {
        // we use raw sql as the request can deal with a huge amount of data
        $conn = $this->entityManager->getConnection();

        // clear previously selected users (todo : check if it's really necessary !!!)
        $sql = "DELETE FROM delivery where campaign_id = " . $campaign->getId();
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery();

        if ($campaign->getSendAll() === 0) {
            // Associate the campaign to all users who accepted email
            $now = new \DateTime();
            $sql = "INSERT INTO delivery (campaign_id, user_id, status, created_date)
            (SELECT " . $campaign->getId() . ",id,0,\"" . $now->format('Y-m-d H:i:s') . "\" FROM user WHERE news_subscription = 1)";
            $stmt = $conn->prepare($sql);
            $stmt->executeQuery();
            $now = new \DateTime();
        } else {
            $community = $this->communityRepository->find($campaign->getSendAll());
            $allUsers = $this->userRepository->getUserInCommunity($community, true);
            foreach ($allUsers as $user) {
                $delivery = new Delivery();
                $delivery->setUser($user);
                $delivery->setCampaign($campaign);
                $delivery->setStatus(0);
                $this->entityManager->persist($delivery);
            };
        }

        // we always flush to keep the possible update on the campaign properties
        $this->entityManager->flush();

        return $campaign;
    }
}
