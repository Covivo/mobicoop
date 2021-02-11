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

namespace App\MassCommunication\MassEmailProvider;

use App\MassCommunication\Entity\Sender;
use App\MassCommunication\Interfaces\CampaignProviderInterface;
use SendinBlue\Client as SendinBlueClient;
use GuzzleHttp\Client;
use DateTime;
use Exception;

/**
 * Sendinblue mass email sender service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 *
 */
class SendinblueProvider implements CampaignProviderInterface
{
    private $key;
    private $folderId;
    private $replyTo;
    private $senderEmail;
    private $templateId;
    private $contactsApi;
    private $emailCampaignApi;

    const CAMPAIGN_NAME="Campaign";
    const CONTACT_EMAIL="EMAIL";
    const CONTACT_FAMILYNAME="NOM";
    const CONTACT_GIVENNAME="PRENOM";
    const SIZE_LIMIT_CONTACT_IMPORT=8388608;


    
    /**
     * Constructor
     *
     * @param string $key           The api key
     * @param integer $folderId     The ID for the Sendinblue folder
     * @param string $replyTo       The replayTo email
     * @param string $sender        The sender Email
     * @param integer $templateId   The ID for the SendinBlue template
     */
    public function __construct(string $key, int $folderId, string $replyTo, string $senderEmail, int $templateId)
    {
        $this->key = $key;
        $this->folderId = $folderId;
        $this->replyTo = $replyTo;
        $this->senderEmail = $senderEmail;
        $this->templateId = $templateId;
        // implement sendinBlue php library
        $config = SendinBlueClient\Configuration::getDefaultConfiguration()->setApiKey('api-key', $key);
        $this->contactsApi = new SendinBlueClient\Api\ContactsApi(
            new Client(),
            $config
        );
        $this->emailCampaignApi = new SendinBlueClient\Api\EmailCampaignsApi(
            new Client(),
            $config
        );
    }

    /**
     * Method to create a SendinBlue mailing campaign
     *
     * @param string $name
     * @param Sender $sender
     * @param string $subject
     * @param string $body
     * @param array $lists of teh recipients (deliveries)
     * @return void
     */
    public function createCampaign(string $name, Sender $sender, string $subject, string $body, array $lists)
    {
        // we create a campaign
        //  we create the list
        $createList = new SendinBlueClient\Model\CreateList();
        $createList['name'] = self::CAMPAIGN_NAME.date_format(new DateTime(), 'YmdHis');
        $createList['folderId'] = $this->folderId;
        try {
            $result = $this->contactsApi->createList($createList);
            $list = $result;
        } catch (Exception $e) {
            echo 'Exception when calling ContactsApi->createList: ', $e->getMessage(), PHP_EOL;
        }

        // we import contacts
        $contactsList[0] = [self::CONTACT_EMAIL,self::CONTACT_FAMILYNAME,self::CONTACT_GIVENNAME];
        // we add the sender infos because the sender need to be in the list to receive the test email
        $contactsList[1] = [$sender->getUser()->getEmail(), $sender->getUser()->getFamilyName(), $sender->getUser()->getGivenName()];
        $i = 2;
        // We add reciepients to the contacts list
        foreach ($lists as $contact) {
            $contactsList[$i++] = [$contact->getUser()->getEmail(), $contact->getUser()->getFamilyName(), $contact->getUser()->getGivenName()];
        }
        //  We format the contacts list
        $contacts=[];
        foreach ($contactsList as $line) {
            $contact = implode(";", $line);
            $contacts[]=$contact;
        }
        $formatedContacts = implode("\n", $contacts);

        // we check if the contact list doesn't exceed size limit
        if (strlen($formatedContacts) > self::SIZE_LIMIT_CONTACT_IMPORT) {
            throw new Exception("Your contact list exceed size limit");
        }
        
        // we import contacts
        $requestContactImport = new SendinBlueClient\Model\RequestContactImport();
        $requestContactImport['fileBody'] = $formatedContacts;
        $requestContactImport['listIds'] = [$list['id']];
        $requestContactImport['emailBlacklist'] = false;
        $requestContactImport['smsBlacklist'] = false;
        $requestContactImport['updateExistingContacts'] = true;
        $requestContactImport['emptyContactsAttributes'] = false;

        try {
            $result = $this->contactsApi->importContacts($requestContactImport);
        } catch (Exception $e) {
            echo 'Exception when calling ContactsApi->importContacts: ', $e->getMessage(), PHP_EOL;
        }
        // We create the campaign
        $emailCampaigns = new SendinBlueClient\Model\CreateEmailCampaign();
        $emailCampaigns['sender'] = ['name' => $sender->getUser()->getGivenName().' '.$sender->getUser()->getShortFamilyName(), 'email' => $this->senderEmail];
        $emailCampaigns['name'] = $createList['name'];
        $emailCampaigns['htmlContent'] = $body;
        // Keep it in case client want to use a temlplate
        // $emailCampaigns['templateId'] = $this->templateId;
        $emailCampaigns['subject'] = $subject;
        $emailCampaigns['replyTo'] = $this->replyTo;
        $emailCampaigns['recipients'] =  ['listIds' => [$list['id']]];
        $emailCampaigns['type'] = 'classic';

        try {
            $result = $this->emailCampaignApi->createEmailCampaign($emailCampaigns);
            return $result;
        } catch (Exception $e) {
            echo 'Exception when calling EmailCampaignsApi->createEmailCampaign: ', $e->getMessage(), PHP_EOL;
        }
    }

    /**
     * Method to send the campaign now
     *
     * @param string $name (not usefull for sendinBlue)
     * @param integer $campaignId
     * @return void
     */
    public function sendCampaign(string $name, int $campaignId)
    {
        // We send the campaign
        try {
            $result = $this->emailCampaignApi->sendEmailCampaignNow($campaignId);
            return $result;
        } catch (Exception $e) {
            echo 'Exception when calling EmailCampaignsApi->sendEmailCampaignNow: ', $e->getMessage(), PHP_EOL;
        }
    }

    /**
     * Method to send a test email
     *
     * @param string $name (not usefull for sendinBlue)
     * @param integer $campaignId
     * @param array $emails
     * @return void
     */
    public function sendCampaignTest(string $name, int $campaignId, array $emails)
    {
        $emailTo = new SendinBlueClient\Model\SendTestEmail();
        $emailTo['emailTo'] = $emails;

        try {
            $result = $this->emailCampaignApi->sendTestEmail($campaignId, $emailTo);
            return $result;
        } catch (Exception $e) {
            echo 'Exception when calling EmailCampaignsApi->sendTestEmail: ', $e->getMessage(), PHP_EOL;
        }
    }
}
