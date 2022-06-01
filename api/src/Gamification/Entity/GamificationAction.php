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
 */

namespace App\Gamification\Entity;

use App\Action\Entity\Action;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
 * Gamification : A GamificationAction.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class GamificationAction
{
    // List of the translatable items of this entity
    public const TRANSLATABLE_ITEMS = [
        'title',
    ];

    /**
     * @var int The GamificationAction's id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"readGamification"})
     * @MaxDepth(1)
     */
    private $id;

    /**
     * @var string The GamificationAction's title (can be translated)
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"readGamification","writeGamification"})
     * @MaxDepth(1)
     */
    private $title;

    /**
     * @var null|ArrayCollection A GamificationAction can be included in multiple SequenceItems
     *
     * @ORM\OneToMany(targetEntity="\App\Gamification\Entity\SequenceItem", mappedBy="gamificationAction", cascade={"persist"})
     * @Groups({"writeGamification"})
     * @MaxDepth(1)
     */
    private $sequenceItems;

    /**
     * @var Action The Action related to this GamificationAction
     *
     * @ORM\ManyToOne(targetEntity="\App\Action\Entity\Action", inversedBy="gamificationActions")
     * @ORM\JoinColumn(nullable=false, onDelete="CASCADE")
     * @MaxDepth(1)
     */
    private $action;

    /**
     * @var GamificationActionRule The GamificationActionRule related to this GamificationAction
     *
     * @ORM\ManyToOne(targetEntity="\App\Gamification\Entity\GamificationActionRule", inversedBy="gamificationActions")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @MaxDepth(1)
     */
    private $gamificationActionRule;

    public function __construct()
    {
        $this->sequenceItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSequenceItems()
    {
        return $this->sequenceItems->getValues();
    }

    public function addSequenceItem(SequenceItem $sequenceItem): self
    {
        if (!$this->sequenceItems->contains($sequenceItem)) {
            $this->sequenceItems[] = $sequenceItem;
            $sequenceItem->getBadge($this);
        }

        return $this;
    }

    public function removeSequenceItem(SequenceItem $sequenceItem): self
    {
        if ($this->sequenceItems->contains($sequenceItem)) {
            $this->sequenceItems->removeElement($sequenceItem);
        }

        return $this;
    }

    public function getAction(): ?Action
    {
        return $this->action;
    }

    public function setAction(?Action $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getGamificationActionRule(): ?GamificationActionRule
    {
        return $this->gamificationActionRule;
    }

    public function setGamificationActionRule(?GamificationActionRule $gamificationActionRule): self
    {
        $this->gamificationActionRule = $gamificationActionRule;

        return $this;
    }
}
