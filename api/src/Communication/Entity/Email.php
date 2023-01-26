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
 * An Email.
 */
class Email
{
    /**
     * @var string sender of the email
     *
     * @Assert\NotBlank
     * @Assert\Email()
     */
    private $senderEmail;

    /**
     * @var string sender's name of the email
     *
     * @Assert\NotBlank
     */
    private $senderName;

    /**
     * @var string sender's first name of the email
     *
     * @Assert\NotBlank
     */
    private $senderFirstName;

    /**
     * @var mixed An array or string of recipient(s) of the email
     *
     * @Assert\Email()
     * @Assert\NotBlank
     */
    private $recipientEmail;

    /**
     * @var array recipient on copy of the email
     *
     * @Assert\Email()
     */
    private $recipientEmailCc;

    /**
     * @var array recipient on blind copy of the email
     *
     * @Assert\Email()
     */
    private $recipientEmailBcc;

    /**
     * @var string return email
     *
     * @Assert\Email()
     */
    private $returnEmail;

    /**
     * @var string object of the email
     *
     * @Assert\NotBlank
     */
    private $object;

    /**
     * @var string message body of the email
     *
     * @Assert\NotBlank
     */
    private $message;

    public function getSenderEmail(): ?string
    {
        return $this->senderEmail;
    }

    public function setSenderEmail(string $senderEmail): self
    {
        $this->senderEmail = $senderEmail;

        return $this;
    }

    public function getSenderName(): ?string
    {
        return $this->senderName;
    }

    public function setSenderName(string $senderName): self
    {
        $this->senderName = $senderName;

        return $this;
    }

    public function getSenderFirstName(): ?string
    {
        return $this->senderFirstName;
    }

    public function setSenderFirstName(string $senderFirstName): self
    {
        $this->senderFirstName = $senderFirstName;

        return $this;
    }

    public function getRecipientEmail()
    {
        return $this->recipientEmail;
    }

    public function setRecipientEmail($recipientEmail): self
    {
        $this->recipientEmail = $recipientEmail;

        return $this;
    }

    public function getRecipientEmailCc(): ?array
    {
        return $this->recipientEmailCc;
    }

    public function setRecipientEmailCc(array $recipientEmailCc): self
    {
        $this->recipientEmailCc = $recipientEmailCc;

        return $this;
    }

    public function getRecipientEmailBcc(): ?array
    {
        return $this->recipientEmailBcc;
    }

    public function setRecipientEmailBcc(array $recipientEmailBcc): self
    {
        $this->recipientEmailBcc = $recipientEmailBcc;

        return $this;
    }

    public function getReturnEmail(): ?string
    {
        return $this->returnEmail;
    }

    public function setReturnEmail(string $returnEmail): self
    {
        $this->returnEmail = $returnEmail;

        return $this;
    }

    public function getObject(): ?string
    {
        return $this->object;
    }

    public function setObject(string $object): self
    {
        $this->object = $object;

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
