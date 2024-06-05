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

namespace Mobicoop\Bundle\MobicoopBundle\Payment\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * An identity validation document.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ValidationDocument implements ResourceInterface, \JsonSerializable
{
    /**
     * @var int The id of this bank account
     */
    private $id;

    /**
     * @var null|string the iri of this event
     *
     * @Groups({"post","put"})
     */
    private $iri;

    /**
     * @var null|File
     *
     * @Groups({"post","put"})
     */
    private $file;

    /**
     * @var null|File
     *
     * @Groups({"post","put"})
     */
    private $file2;

    /**
     * @var User The document's owner
     *
     * @Groups({"readPayment","writePayment"})
     */
    private $user;

    public function __construct($id = null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri('/validation_documents/'.$id);
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getIri()
    {
        return $this->iri;
    }

    public function setIri($iri)
    {
        $this->iri = $iri;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file)
    {
        $this->file = $file;
    }

    public function getFile2(): ?File
    {
        return $this->file2;
    }

    public function setFile2(?File $file2)
    {
        $this->file2 = $file2;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user)
    {
        $this->user = $user;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => $this->getId(),
                'iri' => $this->getIri(),
                'user' => $this->getUser(),
            ];
    }
}
