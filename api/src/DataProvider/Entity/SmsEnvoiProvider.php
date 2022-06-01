<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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
 */

namespace App\DataProvider\Entity;

use App\Communication\Entity\Sms;
use App\DataProvider\Interfaces\ProviderInterface;
use App\DataProvider\Service\DataProvider;

class SmsEnvoiProvider implements ProviderInterface
{
    private const URI = 'https://api.smsenvoi.com/';
    private const COLLECTION_RESSOURCE_SMS = 'API/v1.0/REST/sms';
    private const COLLECTION_RESSOURCE_AUTH = 'API/v1.0/REST/login';

    private $collection;
    private $username;
    private $password;
    private $sender;

    public function __construct(string $username, string $password, string $sender)
    {
        $this->collection = [];
        $this->username = $username;
        $this->password = $password;
        $this->sender = $sender;
    }

    /**
     * {@inheritdoc}
     */
    public function postCollection(Sms $sms)
    {
        // call api to authentication
        $dataProvider = new DataProvider(self::URI, self::COLLECTION_RESSOURCE_AUTH);
        $response = $dataProvider->getItem(['username' => $this->username, 'password' => $this->password]);
        if (200 == $response->getCode()) {
            $dataProvider->setResource(self::COLLECTION_RESSOURCE_SMS);

            $headers = [];
            $headers['user_key'] = explode(';', $response->getValue())[0];
            $headers['Session_key'] = explode(';', $response->getValue())[1];
            $body = [
                'message_type' => 'PRM',
                'encoding' => 'ucs2',
                'message' => $sms->getMessage(),
                'recipient' => [
                    $sms->getRecipientTelephone(),
                ],
                'sender' => $this->sender,
            ];
            $dataProvider->postCollection($body, $headers);
        }

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
        $this->logger->info('SMS Envoi API return');
    }
}
