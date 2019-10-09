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
 **************************/

namespace App\DataProvider\Entity;

use App\DataProvider\Service\DataProvider;
use App\Communication\Entity\Sms;

class SmsEnvoiProvider 
{
    private const URI = "https://api.smsenvoi.com/";
    private const COLLECTION_RESSOURCE_SMS = "API/v1.0/REST/sms";
   
    private $collection;

    public function __construct()
    {
        $this->collection = [];
    }

    /**
     * {@inheritdoc}
     */
    public function postCollection(array $params)
    {
        $dataProvider = new DataProvider(self::URI, self::COLLECTION_RESSOURCE_SMS);
        $dataProvider->setClass(Sms::class);
        $params=[];
        $params['user_key'] = "USER_KEY";
        $params['Access_token'] = "ACCESS_TOKEN}";
        
        $dataProvider->postCollection($params);

    }

}
