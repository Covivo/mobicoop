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

use App\User\Entity\User;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

/**
* Gamification : A Validation Step to determine of a specific SequenceItem is valid
* @author Maxime Bardot <maxime.bardot@mobicoop.org>
*
* @ORM\Entity
* @ORM\HasLifecycleCallbacks
*/
class ValidationStep
{
    /**
     * @var SequenceItem The SequenceItem we check
     */
    private $sequenceItem;

    /**
     * @var User The User we check the SequenceItem
     */
    private $user;

    /**
     * @var boolean Indicate if the sequenceItem is validated during the validation process (used only for internal purpose)
     */
    private $valid;

    public function getSequenceItem(): ?SequenceItem
    {
        return $this->sequenceItem;
    }

    public function setSequenceItem(?SequenceItem $sequenceItem): self
    {
        $this->sequenceItem = $sequenceItem;

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
    
    public function isValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(?bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }
}
