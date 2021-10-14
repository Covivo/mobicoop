<?php

/**
 * Copyright (c) 2021, MOBICOOP. All rights reserved.
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

namespace App\Gamification\Entity;

use App\Gamification\Entity\Badge;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Gamification : A SequenceStatus
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class SequenceStatus
{
    /**
     * @var int sequenceItemId of the related SequenceItem
     * @Groups({"readGamification"})
     */
    private $sequenceItemId;

    /**
     * @var bool sequenceItem validated or not
     * @Groups({"readGamification"})
     */
    private $validated;

    /**
     * @var string Sequence's title according the gamificationAction title
     * @Groups({"readGamification"})
     */
    private $title;


    public function getSequenceItemId(): ?int
    {
        return $this->sequenceItemId;
    }

    public function setSequenceItemId(int $sequenceItemId): self
    {
        $this->sequenceItemId = $sequenceItemId;

        return $this;
    }
    
    public function isValidated(): ?bool
    {
        return $this->validated;
    }

    public function setValidated(bool $validated): self
    {
        $this->validated = $validated;

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
}
