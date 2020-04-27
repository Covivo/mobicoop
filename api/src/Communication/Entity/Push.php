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

namespace App\Communication\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * A push notification
 */
class Push
{
    /**
     * @var string Recipient device id
     * @Assert\NotBlank
     */
    private $recipientDeviceId;

    /**
     * @var string Notification message
     * @Assert\NotBlank
     */
    private $message;

    public function getRecipientDeviceId(): string
    {
        return $this->recipientDeviceId;
    }

    public function setRecipientDeviceId(string $recipientDeviceId): self
    {
        $this->recipientDeviceId = $recipientDeviceId;

        return $this;
    }

    public function getMessage():? string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
