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

use App\MassCommunication\Interfaces\MassEmailProviderInterface;
use Mandrill;
use Mandrill_Error;

/**
 * Mandrill mass email sender service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 */
class MandrillProvider implements MassEmailProviderInterface
{
    private $key;

    public function __construct(string $key)
    {
        $this->key = $key;
    }

    /**
     * {@inheritdoc}
     */
    public function send(string $subject, string $fromName, string $fromEmail, string $replyTo, string $body, array $recipients)
    {
        // todo : check the best method to send a email from mandrill, and how to get the results (sync / async ? webhook ?)
        $to = [];
        $merge = [];
        foreach ($recipients as $email=>$context) {
            $to[] = [
                'email' => $email,
                'type' => 'to'
            ];
            $vars = [];
            foreach ($context as $key=>$value) {
                $vars[] = [
                    'name' => $key,
                    'content' => $value
                ];
            }
            $merge[] = [
                'rcpt' => $email,
                'vars' => $vars
            ];
        }
        try {
            $mandrill = new Mandrill($this->key);
            $message = [
                'html' => $body,
                'text' => '',
                'subject' => $subject,
                'from_email' => $fromEmail,
                'from_name' => $fromName,
                'to' => $to,
                'headers' => ['Reply-To' => $replyTo],
                'important' => false,
                'track_opens' => null,
                'track_clicks' => null,
                'auto_text' => null,
                'auto_html' => null,
                'inline_css' => null,
                'url_strip_qs' => null,
                'preserve_recipients' => null,
                'view_content_link' => null,
                'bcc_address' => null,
                'tracking_domain' => null,
                'signing_domain' => null,
                'return_path_domain' => null,
                'merge' => true,
                'merge_language' => 'mailchimp',
                'global_merge_vars' => null,
                'merge_vars' => $merge,
                'tags' => null,
                'subaccount' => null,
                'google_analytics_domains' => null,
                'google_analytics_campaign' => null,
                'metadata' => null,
                'recipient_metadata' => null,
                'attachments' => null,
                'images' => null
            ];
            $async = true;
            $ip_pool = 'Main Pool';
            $send_at = new \DateTime();
            $result = $mandrill->messages->send($message, $async, $ip_pool, $send_at->format('YmdHis'));
        } catch(Mandrill_Error $e) {
            // Mandrill errors are thrown as exceptions
            echo 'A mandrill error occurred: ' . get_class($e) . ' - ' . $e->getMessage();
            // A mandrill error occurred: Mandrill_Unknown_Subaccount - No subaccount exists with the id 'customer-123'
            throw $e;
        }

    }
}
