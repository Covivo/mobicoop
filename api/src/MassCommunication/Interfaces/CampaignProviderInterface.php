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
 */

namespace App\MassCommunication\Interfaces;

use App\MassCommunication\Entity\Sender;

/**
 * Campaign provider interface.
 *
 * A campaign provider entity class must implement all these methods in order to be used by campaign services.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
interface CampaignProviderInterface
{
    /**
     * Create an email campaign.
     *
     * @param string      $name       The name of the campaign
     * @param Sender      $sender     The sender of the campaign
     * @param string      $subject    The subject of the email
     * @param string      $body       The body of the email
     * @param Recipient[] $recipients The recipients to send the email to
     */
    public function createCampaign(string $name, Sender $sender, string $subject, string $body, array $recipients);

    /**
     * Send an email campaign.
     *
     * @param string $name The name of the campaign
     */
    public function sendCampaign(string $name, int $id);

    /**
     * Send an email test for an email campaign.
     *
     * @param string $name   The name of the campaign
     * @param array  $emails The emails to send the test to
     */
    public function sendCampaignTest(string $name, int $id, array $emails);
}
