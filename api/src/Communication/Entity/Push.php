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

namespace App\Communication\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * A push notification.
 */
class Push
{
    /**
     * @var array Recipient device ids
     * @Assert\NotBlank
     */
    private $recipientDeviceIds;

    /**
     * @var string Notification title
     * @Assert\NotBlank
     */
    private $title;

    /**
     * @var string Notification message
     * @Assert\NotBlank
     */
    private $message;

    public function getRecipientDeviceIds(): array
    {
        return $this->recipientDeviceIds;
    }

    public function setRecipientDeviceIds(array $recipientDeviceIds): self
    {
        $this->recipientDeviceIds = $recipientDeviceIds;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
