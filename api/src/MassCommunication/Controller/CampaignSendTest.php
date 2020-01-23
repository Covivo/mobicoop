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

namespace App\MassCommunication\Controller;

use App\MassCommunication\Entity\Campaign;
use App\MassCommunication\Service\CampaignManager;
use App\TranslatorTrait;

/**
 * Controller class for campaign send.
 *
 */
class CampaignSendTest
{
    use TranslatorTrait;

    private $campaignManager;
    
    public function __construct(CampaignManager $campaignManager)
    {
        $this->campaignManager = $campaignManager;
    }

    public function __invoke(Campaign $data)
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad campaign id is provided"));
        }
        return $this->campaignManager->sendTest($data);
    }
}
