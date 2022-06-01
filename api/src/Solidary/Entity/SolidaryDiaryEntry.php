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

namespace App\Solidary\Entity;

use App\Action\Entity\Diary;
use App\User\Entity\User;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * A solidary diary entry.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SolidaryDiaryEntry
{
    /**
     * @var Diary The diary entity of this entry
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $diary;

    /**
     * @var string The action name of this entry
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $action;

    /**
     * @var \DateTimeInterface Date of this entry
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $date;

    /**
     * @var User The author of this entry
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $author;

    /**
     * @var User The user associated to this entry
     * @Groups({"readSolidary","writeSolidary"})
     */
    private $user;

    public function getDiary(): ?Diary
    {
        return $this->diary;
    }

    public function setDiary(?Diary $diary): self
    {
        $this->diary = $diary;

        return $this;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function setAction(string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function setAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
