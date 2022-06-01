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

namespace App\Auth\Rule;

use App\Auth\Interfaces\AuthRuleInterface;
use App\Communication\Entity\Message;

/**
 *  Check that the requester is involved in the related Message (=author or recipient).
 */
class MessageActor implements AuthRuleInterface
{
    /**
     * {@inheritdoc}
     */
    public function execute($requester, $item, $params): bool
    {
        if (!isset($params['message'])) {
            return false;
        }

        /**
         * @var Message $message
         */
        $message = $params['message'];
        // If the requester is the sender
        if ($message->getUser()->getId() == $requester->getId()) {
            return true;
        }

        // If the requester is one of the recipients
        $recipients = $message->getRecipients();
        foreach ($recipients as $recipient) {
            if ($recipient->getUser()->getId() == $requester->getId()) {
                return true;
            }
        }

        return false;
    }
}
