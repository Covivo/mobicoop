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

namespace App\MassCommunication\CampaignProvider;

use App\MassCommunication\Entity\Sender;
use App\MassCommunication\Exception\CampaignException;
use App\MassCommunication\Interfaces\CampaignProviderInterface;
use SendinBlue\Client as SendinBlueClient;
use GuzzleHttp\Client;
use DateTime;
use Exception;

/**
 * SendinBlue mass email sender service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 *
 */
class SendinBlueProvider implements CampaignProviderInterface
{
    private $folderId;
    private $senderName;
    private $senderEmail;
    private $replyTo;
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
     * @param int $folderId         The ID for the SendinBlue folder
     * @param string $senderName    The sender name
     * @param string $senderEmail   The sender email
     * @param string $replyTo       The replyTo email
     * @param string $templateId    The ID for the SendinBlue template
     */
    public function __construct(string $key, int $folderId, string $senderName, string $senderEmail, string $replyTo, string $templateId)
    {
        $this->folderId = $folderId;
        $this->replyTo = $replyTo;
        $this->senderEmail = $senderEmail;
        $this->senderName = $senderName;
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
     * Create a SendinBlue mailing campaign
     *
     * @param string $name              The name of the campaign
     * @param Sender $sender            The sender
     * @param string $subject           The subject
     * @param string $body              The body
     * @param Recipient[] $recipients   The list of recipients (as Recipient objects)
     * @return void
     */
    public function createCampaign(string $name, Sender $sender, string $subject, string $body, array $recipients)
    {
        // we create the list
        $createList = new SendinBlueClient\Model\CreateList();
        $createList['name'] = self::CAMPAIGN_NAME.date_format(new DateTime(), 'YmdHis');
        $createList['folderId'] = $this->folderId;
        try {
            $list = $this->contactsApi->createList($createList);
        } catch (Exception $e) {
            throw new CampaignException('Exception when calling SendinBlue ContactsApi->createList: ' . $e->getMessage());
        }

        // we import contacts
        $contactsList[0] = [self::CONTACT_EMAIL,self::CONTACT_FAMILYNAME,self::CONTACT_GIVENNAME];
        // we add the sender infos because the sender needs to be in the list to receive the test email
        $contactsList[1] = [$sender->getUser()->getEmail(), $sender->getUser()->getFamilyName(), $sender->getUser()->getGivenName()];
        $i = 2;
        // We add recipients to the contacts list
        foreach ($recipients as $recipient) {
            $contactsList[$i++] = [$recipient->getEmail(), $recipient->getFamilyName(), $recipient->getGivenName()];
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
            throw new CampaignException("Your contact list exceeds the size limit of " . self::SIZE_LIMIT_CONTACT_IMPORT);
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
            $this->contactsApi->importContacts($requestContactImport);
        } catch (Exception $e) {
            throw new CampaignException('Exception when calling SendinBlue ContactsApi->importContacts: ' . $e->getMessage());
        }

        // We create the campaign
        $emailCampaigns = new SendinBlueClient\Model\CreateEmailCampaign();
        $emailCampaigns['sender'] = ['name' => $this->senderName, 'email' => $this->senderEmail];
        $emailCampaigns['name'] = $createList['name'];
        $emailCampaigns['htmlContent'] = $body;
        if ($this->templateId != '') {
            $emailCampaigns['templateId'] = $this->templateId;
        }
        $emailCampaigns['subject'] = $subject;
        $emailCampaigns['replyTo'] = $this->replyTo;
        $emailCampaigns['recipients'] =  ['listIds' => [$list['id']]];
        $emailCampaigns['type'] = 'classic';

        try {
            return $this->emailCampaignApi->createEmailCampaign($emailCampaigns);
        } catch (Exception $e) {
            throw new CampaignException('Exception when calling SendinBlue EmailCampaignsApi->createEmailCampaign: ' . $e->getMessage());
        }
    }

    /**
     * Method to send the campaign now
     *
     * @param string $name          The name of the campaign (not useful for SendinBlue)
     * @param integer $campaignId   The campaign ID
     * @return void
     */
    public function sendCampaign(string $name, int $campaignId)
    {
        try {
            return $this->emailCampaignApi->sendEmailCampaignNow($campaignId);
        } catch (Exception $e) {
            throw new CampaignException('Exception when calling SendinBlue EmailCampaignsApi->sendEmailCampaignNow: ' . $e->getMessage());
        }
    }

    /**
     * Method to send a test email
     *
     * @param string $name (not usefull for SendinBlue)
     * @param integer $campaignId
     * @param array $emails
     * @return void
     */
    public function sendCampaignTest(string $name, int $campaignId, array $emails)
    {
        $emailTo = new SendinBlueClient\Model\SendTestEmail();
        $emailTo['emailTo'] = $emails;

        try {
            return $this->emailCampaignApi->sendTestEmail($campaignId, $emailTo);
        } catch (Exception $e) {
            throw new CampaignException('Exception when calling SendinBlue EmailCampaignsApi->sendTestEmail: ' . $e->getMessage());
        }
    }
}
