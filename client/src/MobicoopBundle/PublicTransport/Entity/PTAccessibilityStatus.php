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
 **************************/

namespace Mobicoop\Bundle\MobicoopBundle\PublicTransport\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * A public accessiblity status.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PTAccessibilityStatus
{
    /**
     * @var int id of this accessibility status
     */
    private $id;

    /**
     * @var int blind Accessibility
     */
    private $blindAccess;

    /**
     * @var int deaf Accessibility
     */
    private $deafAccess;

    /**
     * @var int mental illness Accessibility
     */
    private $mentalIllnessAccess;

    /**
     * @var int wheelchair Accessibility
     * @Groups("pt")
     */
    private $wheelChairAccess;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getBlindAccess(): int
    {
        return $this->blindAccess;
    }

    public function setBlindAccess(int $blindAccess): self
    {
        $this->blindAccess = $blindAccess;

        return $this;
    }

    public function getDeafAccess(): int
    {
        return $this->deafAccess;
    }

    public function setDeafAccess(int $deafAccess): self
    {
        $this->deafAccess = $deafAccess;

        return $this;
    }

    public function getMentalIllnessAccess(): int
    {
        return $this->mentalIllnessAccess;
    }

    public function setMentalIllnessAccess(int $mentalIllnessAccess): self
    {
        $this->mentalIllnessAccess = $mentalIllnessAccess;

        return $this;
    }

    public function getWheelChairAccess(): int
    {
        return $this->wheelChairAccess;
    }

    public function setWheelChairAccess(int $wheelChairAccess): self
    {
        $this->wheelChairAccess = $wheelChairAccess;

        return $this;
    }
}
