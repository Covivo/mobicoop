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

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
* Gamification : A SequenceItem a user need to validate in order to be reward by a Badge
* @author Maxime Bardot <maxime.bardot@mobicoop.org>
*
* @ORM\Entity
* @ORM\HasLifecycleCallbacks
*/
class SequenceItem
{

    /**
     * @var int The SequenceItem's id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"readGamification"})
     * @MaxDepth(1)
     */
    private $id;

    /**
     * @var int The SequenceItem's order position in the global sequence
     *
     * @ORM\Column(type="smallint")
     * @Groups({"readGamification","writeGamification"})
     * @MaxDepth(1)
     */
    private $position;

    /**
     * @var int Minimum iteration/quantity of the sequenceItem to earn the badge
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"readGamification","writeGamification"})
     * @MaxDepth(1)
     */
    private $minCount;

    /**
     * @var int Minimum different iteration/quantity of the sequenceItem to earn the badge
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"readGamification","writeGamification"})
     * @MaxDepth(1)
     */
    private $minUniqueCount;

    /**
     * @var boolean Indicate if the sequenceItem need to be validated during a specific laps of time
     *
     * @ORM\Column(type="boolean", nullable=true)
     * @Groups({"readGamification","writeGamification"})
     * @MaxDepth(1)
     */
    private $inDateRange;

    /**
     * @var Badge The Badge this SequenceItem is required to be earned
     *
     * @ORM\ManyToOne(targetEntity="\App\Gamification\Entity\Badge", inversedBy="sequenceItems")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readGamification","writeGamification"})
     * @MaxDepth(1)
     */
    private $badge;

    /**
     * @var GamificationAction The GamificationAction this SequenceItem is linked
     *
     * @ORM\ManyToOne(targetEntity="\App\Gamification\Entity\GamificationAction", inversedBy="sequenceItems")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readGamification","writeGamification"})
     * @MaxDepth(1)
     */
    private $gamificationAction;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getPosition(): ?int
    {
        return $this->position;
    }

    public function setPosition(?int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getMinCount(): ?int
    {
        return $this->minCount;
    }

    public function setMinCount(?int $minCount): self
    {
        $this->minCount = $minCount;

        return $this;
    }

    public function getMinUniqueCount(): ?int
    {
        return $this->minUniqueCount;
    }

    public function setMinUniqueCount(?int $minUniqueCount): self
    {
        $this->minUniqueCount = $minUniqueCount;

        return $this;
    }

    public function isInDateRange(): ?bool
    {
        return $this->inDateRange;
    }

    public function setinDateRange(?bool $inDateRange): self
    {
        $this->inDateRange = $inDateRange;

        return $this;
    }

    public function getBadge(): ?Badge
    {
        return $this->badge;
    }

    public function setBadge(?Badge $badge): self
    {
        $this->badge = $badge;

        return $this;
    }

    public function getGamificationAction(): ?GamificationAction
    {
        return $this->gamificationAction;
    }

    public function setGamificationAction(?GamificationAction $gamificationAction): self
    {
        $this->gamificationAction = $gamificationAction;

        return $this;
    }
}
