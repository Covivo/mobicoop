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

namespace App\DataProvider\Entity;

use App\Communication\Entity\Push;
use App\DataProvider\Interfaces\ProviderInterface;
//use Fcm\FcmClient;
//use Fcm\Push\Notification;
use phpFCMv1\Client;
use phpFCMv1\Notification;
use phpFCMv1\Recipient;

/**
 * Firebase management service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 */
class FirebaseProvider implements ProviderInterface
{
    private $client;
    

    public function __construct($serviceAccount)
    {
        $this->client = new Client($serviceAccount);
    }

    /**
     * {@inheritdoc}
     */
    public function postCollection(Push $push)
    {
        $notification = new Notification();
        $notification->setNotification($push->getTitle(),$push->getMessage());
        // send the notification
        foreach ($push->getRecipientDeviceIds() as $recipientId){
            $recipient = new Recipient();
            $recipient->setSingleRecipient($recipientId);
            $this->client->build($recipient, $notification);
            $this->client -> fire();
        }
        // todo : get the response and treat the bad device ids
        return new Response();
    }

    /**
     * {@inheritdoc}
     */
    public function getCollection(string $class, string $apikey, array $params)
    {
    }

    /**
    * {@inheritdoc}
    */
    public function getItem(string $class, string $apikey, array $params)
    {
    }

    /**
    * {@inheritdoc}
    */
    public function deserialize(string $class, array $data)
    {
        $this->logger->info("Firebase API return");
    }
}
