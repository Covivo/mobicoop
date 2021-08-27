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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
* Gamification : A GamificationActionRule
* @author Maxime Bardot <maxime.bardot@mobicoop.org>
*
* @ORM\Entity
* @ORM\HasLifecycleCallbacks
*/
class GamificationActionRule
{
    /**
     * @var int The GamificationActionRule's id
     *
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"readGamification"})
     * @MaxDepth(1)
     */
    private $id;

    /**
     * @var string The GamificationAction's name (for internal purpose)
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"readGamification","writeGamification"})
     * @MaxDepth(1)
     */
    private $name;

    /**
     * @var ArrayCollection|null A GamificationAction can be included in multiple SequenceItems
     *
     * @ORM\OneToMany(targetEntity="\App\Gamification\Entity\GamificationAction", mappedBy="gamificationActionRule", cascade={"persist","remove"})
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"readGamification","writeGamification"})
     * @MaxDepth(1)
     */
    private $gamificationActions;

    public function __construct()
    {
        $this->gamificationActions = new ArrayCollection();
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

    public function getname(): ?string
    {
        return $this->name;
    }

    public function setname(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getGamificationActions()
    {
        return $this->gamificationActions->getValues();
    }

    public function addGamificationAction(GamificationAction $gamificationAction): self
    {
        if (!$this->gamificationActions->contains($gamificationAction)) {
            $this->gamificationActions[] = $gamificationAction;
            $gamificationAction->getAction($this);
        }

        return $this;
    }

    public function removeGamificationAction(GamificationAction $gamificationAction): self
    {
        if ($this->gamificationActions->contains($gamificationAction)) {
            $this->gamificationActions->removeElement($gamificationAction);
        }

        return $this;
    }
}
