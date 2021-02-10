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
    private $templateId;
    private $contactsApi;
    private $emailCampaignApi;


    /**
     * Constructor
     *
     * @param string $key           The api key
     * @param string $folderPrefix  The prefix for the Sendinblue folder
     * @param string $domain        The domain for the senders
     * @param string $ip            The ip for the senders
     */
    public function __construct(string $key, int $folderId, string $replyTo, int $templateId)
    {
        $this->key = $key;
        $this->folderId = $folderId;
        $this->replyTo = $replyTo;
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
        $createList['name'] = 'Campaign'.date_format(new DateTime(), 'YmdHis');
        $createList['folderId'] = $this->folderId;
        try {
            $result = $this->contactsApi->createList($createList);
            $list = $result;
        } catch (Exception $e) {
            echo 'Exception when calling ContactsApi->createList: ', $e->getMessage(), PHP_EOL;
        }

        // we import contacts
        $contactsList[0] = ['EMAIL','FAMILYNAME','GIVENNAME'];
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
        $formatedContacts = "'".implode("\n", $contacts)."'";
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
        $emailCampaigns['sender'] = ['name' => $sender->getUser()->getGivenName().' '.$sender->getUser()->getShortFamilyName(), 'email' => 'qualite@mobicoop.org'];
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


    // /**
    //  * {@inheritdoc}
    //  */
    // public function send(string $subject, string $fromName, string $fromEmail, string $replyTo, string $body, array $recipients)
    // {
    //     // todo : check the best method to send a email from send in blue, and how to get the results (sync / async ? webhook ?)
    //     $to = [];
    //     $merge = [];

    //     foreach ($recipients as $email=>$context) {
    //         $to[] = [
    //             'email' => $email,
    //             'type' => 'bcc'
    //         ];
    //         $vars = [];
    //         foreach ($context as $key=>$value) {
    //             $vars[] = [
    //                 'name' => $key,
    //                 'content' => $value
    //             ];
    //         }
    //         $merge[] = [
    //             'rcpt' => $email,
    //             'vars' => $vars
    //         ];
    //     }
    //     try {
    //         $mandrill = new Mandrill($this->key);
    //         $message = [
    //             'html' => $body,
    //             'text' => '',
    //             'subject' => $subject,
    //             'from_email' => $fromEmail,
    //             'from_name' => $fromName,
    //             'to' => $to,
    //             'headers' => ['Reply-To' => $replyTo],
    //             'important' => false,
    //             'track_opens' => true,
    //             'track_clicks' => null,
    //             'auto_text' => null,
    //             'auto_html' => null,
    //             'inline_css' => null,
    //             'url_strip_qs' => null,
    //             'preserve_recipients' => true,
    //             'view_content_link' => null,
    //             'bcc_address' => null,
    //             'tracking_domain' => null,
    //             'signing_domain' => null,
    //             'return_path_domain' => null,
    //             'merge' => true,
    //             'merge_language' => 'mailchimp',
    //             'global_merge_vars' => null,
    //             'merge_vars' => $merge,
    //             'tags' => null,
    //             'subaccount' => null,
    //             'google_analytics_domains' => null,
    //             'google_analytics_campaign' => null,
    //             'metadata' => null,
    //             'recipient_metadata' => null,
    //             'attachments' => null,
    //             'images' => null
    //         ];
    //         $async = true;
    //         $ip_pool = 'Main Pool';
    //         $send_at = new \DateTime();
            
    //         $result = $mandrill->messages->send($message, $async, $ip_pool, $send_at->format('YmdHis'));

    //         //Format des données retournées
    //         // $resultTest = array();
    //         // $resultTest[0]= array('email' => "julien.deschampt@mobicoop.org","status"=>"sent","_id" => "5d40ea17e5b64a1d93179026a87f17d2","reject_reason" => NULL);
    //         return $result;
    //     } catch (Mandrill_Error $e) {
    //         // Mandrill errors are thrown as exceptions
    //         echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
    //         // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
    //         throw $e;
    //     }
    // }
}
