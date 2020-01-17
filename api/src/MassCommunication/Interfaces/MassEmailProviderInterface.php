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

namespace App\MassCommunication\Interfaces;

/**
 * Mass email provider interface.
 *
 * A mass email provider entity class must implement all these methods in order to be used by campaign services.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 */
interface MassEmailProviderInterface
{
    /**
     * Send an email to multiple recipients.
     *
     * @param string $subject   The subject of the email
     * @param string $fromName  The name of the sender
     * @param string $fromEmail The email of the sender
     * @param string $replyTo   The reply to email
     * @param string $body      The body of the message
     * @param array $recipients The array of recipients email, with its context variables (under the form [$email => [$context]])
     * @return mixed
     */
    public function send(string $subject, string $fromName, string $fromEmail, string $replyTo, string $body, array $recipients);
}
