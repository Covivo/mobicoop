<?php

/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

namespace App\Icentive\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CeeLongDistanceStatus
{
    /**
     * @var int Nb Electronically payment pending journeys
     *
     * @Groups({"readIncentive"})
     */
    private $nbElectronicallyPending;

    /**
     * @var int Nb Electronically paid journeys
     *
     * @Groups({"readIncentive"})
     */
    private $nbElectronicallyPaid;

    public function getNbElectronicallyPaid(): ?int
    {
        return $this->nbElectronicallyPaid;
    }

    public function setNbElectronicallyPaid(int $nbElectronicallyPaid): self
    {
        $this->nbElectronicallyPaid = $nbElectronicallyPaid;

        return $this;
    }

    public function getNbElectronicallyPending(): ?int
    {
        return $this->nbElectronicallyPending;
    }

    public function setNbElectronicallyPending(int $nbElectronicallyPending): self
    {
        $this->nbElectronicallyPending = $nbElectronicallyPending;

        return $this;
    }
}
