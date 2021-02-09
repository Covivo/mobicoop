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
use App\MassCommunication\Exception\MassMailingException;
use DateTime;

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
    private $templateId;
    private $replyTo;
    private $accountApi;
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
    public function __construct(string $key, int $folderId, int $templateId, string $replyTo)
    {
        $this->key = $key;
        $this->folderId = $folderId;
        $this->templateId = $templateId;
        $this->replyTo = $replyTo;
        // implement sendinBlue php library
        $config = SendinBlueClient\Configuration::getDefaultConfiguration()->setApiKey('api-key', $key);
        $this->accountApi = new SendinBlueClient\Api\AccountApi(
            new Client(),
            $config
        );
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
     * {@inheritdoc}
     */
    public function createCampaign(string $name, Sender $sender, string $subject, string $body, array $lists)
    {
        $account=null;
        $list=null;
        try {
            $result = $this->accountApi->getAccount();
            $account = $result;
        } catch (MassMailingException $massMailingException) {
            throw new MassMailingException("Account not found");
        }
        //  we create the list
        $createList = new SendinBlueClient\Model\CreateList();
        $createList['name'] = 'Campaign'.date_format(new DateTime(), 'YmdHis');
        $createList['folderId'] = $this->folderId;
        try {
            $result = $this->contactsApi->createList($createList);
            $list = $result;
        } catch (MassMailingException $massMailingException) {
            throw new MassMailingException("List creation failed");
        }

        // we add contacts in that list
        foreach ($lists as $contact) {
            $createContact = new SendinBlueClient\Model\CreateContact(); // Values to create a contact
            $createContact['email'] =  $contact;
            $createContact['listIds'] = [$list['id']];
            try {
                $result = $this->contactsApi->createContact($createContact);
            } catch (MassMailingException $massMailingException) {
                throw new MassMailingException("Contact import failed");
            }
        }
        // We create the campaign
        $emailCampaigns = new SendinBlueClient\Model\CreateEmailCampaign();
        $emailCampaigns['sender'] = ['name' => $sender->getUser()->getGivenName().' '.$sender->getUser()->getShortFamilyName(), 'email' => 'qualite@mobicoop.org'];
        $emailCampaigns['name'] = $createList['name'];
        $emailCampaigns['templateId'] = $this->templateId;
        $emailCampaigns['subject'] = $subject;
        $emailCampaigns['replyTo'] = $this->replyTo;
        $emailCampaigns['recipients'] =  ['listIds' => [$list['id']]];
        $emailCampaigns['type'] = 'classic';

        try {
            $result = $this->emailCampaignApi->createEmailCampaign($emailCampaigns);
            $campaign = $result;
        } catch (MassMailingException $massMailingException) {
            throw new MassMailingException("Campaign creation failed");
        }
      
        return $campaign;
    }

    /**
     * {@inheritdoc}
     */
    public function sendCampaign(string $name, int $campaignId)
    {
        $account=null;
        try {
            $result = $this->accountApi->getAccount();
            $account = $result;
        } catch (MassMailingException $massMailingException) {
            throw new MassMailingException("Account not found");
        }

        $campaignId = 1;

        try {
            $result = $this->emailCampaignApi->sendEmailCampaignNow($campaignId);
            $campaign = $result;
        } catch (MassMailingException $massMailingException) {
            throw new MassMailingException("Test failed");
        }

        return $campaign;
    }

    /**
     * {@inheritdoc}
     */
    public function sendCampaignTest(string $name, int $campaignId, array $emails)
    {
        $account=null;
        try {
            $result = $this->accountApi->getAccount();
            $account = $result;
        } catch (MassMailingException $massMailingException) {
            throw new MassMailingException("Account not found");
        }
        $campaignId = 1;
        $emailTo = new SendinBlueClient\Model\SendTestEmail();
        $emailTo['emailTo'] = $emails;

        try {
            $result = $this->emailCampaignApi->sendTestEmail($campaignId, $emailTo);
            $test = $result;
        } catch (MassMailingException $massMailingException) {
            throw new MassMailingException("Test failed");
        }

        return $test;
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
