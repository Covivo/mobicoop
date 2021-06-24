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
 * Gamification : The Badge progress summary
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BadgeSummary
{
    /**
     * @var int Badge's id
     * @Groups({"readGamification"})
     */
    private $badgeId;

    /**
     * @var SequenceStatus Badge's sequence status
     * @Groups({"readGamification"})
     */
    private $sequence;


    public function getBadgeId(): ?int
    {
        return $this->badgeId;
    }

    public function setBadgeId(int $badgeId): self
    {
        $this->badgeId = $badgeId;

        return $this;
    }

    public function getSequence(): ?SequenceStatus
    {
        return $this->sequence;
    }

    public function setSequence(SequenceStatus $sequence): self
    {
        $this->sequence = $sequence;

        return $this;
    }
}
