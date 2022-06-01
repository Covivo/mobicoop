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

namespace App\Communication\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * A Sms.
 */
class Sms
{
    /**
     * @var string recipient phone number of the sms
     * @Assert\NotBlank
     */
    private $recipientTelephone;

    /**
     * @var string message body of the telephone
     * @Assert\NotBlank
     */
    private $message;

    public function getRecipientTelephone(): string
    {
        return $this->recipientTelephone;
    }

    public function setRecipientTelephone(string $recipientTelephone): self
    {
        $this->recipientTelephone = $recipientTelephone;

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
