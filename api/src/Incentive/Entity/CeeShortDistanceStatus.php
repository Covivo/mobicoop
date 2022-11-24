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

namespace App\Incentive\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class CeeShortDistanceStatus
{
    /**
     * @var int Nb pending class C proofs
     *
     * @Groups({"readIncentive"})
     */
    private $nbPendingProofs;

    /**
     * @var int Nb validated class C proofs
     *
     * @Groups({"readIncentive"})
     */
    private $nbValidatedProofs;

    /**
     * @var int Nb rejected class C proofs
     *
     * @Groups({"readIncentive"})
     */
    private $nbRejectedProofs;

    public function __construct()
    {
        $this->nbPendingProofs = 0;
        $this->nbValidatedProofs = 0;
        $this->nbRejectedProofs = 0;
    }

    public function getNbPendingProofs(): ?int
    {
        return $this->nbPendingProofs;
    }

    public function setNbPendingProofs(int $nbPendingProofs): self
    {
        $this->nbPendingProofs = $nbPendingProofs;

        return $this;
    }

    public function getNbValidatedProofs(): ?int
    {
        return $this->nbValidatedProofs;
    }

    public function setNbValidatedProofs(int $nbValidatedProofs): self
    {
        $this->nbValidatedProofs = $nbValidatedProofs;

        return $this;
    }

    public function getNbRejectedProofs(): ?int
    {
        return $this->nbRejectedProofs;
    }

    public function setNbRejectedProofs(int $nbRejectedProofs): self
    {
        $this->nbRejectedProofs = $nbRejectedProofs;

        return $this;
    }
}
