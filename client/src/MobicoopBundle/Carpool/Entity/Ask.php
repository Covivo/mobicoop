<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;
use Mobicoop\Bundle\MobicoopBundle\Gamification\Entity\GamificationEntity;

/**
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class Ask extends GamificationEntity implements ResourceInterface, \JsonSerializable
{
    
    /**
     * @var int The id of this ask.
     */
    private $id;

    /**
     * @var array The weeks with a pending payment.
     */
    private $weekItems;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id)
    {
        $this->id = $id;
    }

    public function getWeekItems(): ?array
    {
        return $this->weekItems;
    }

    public function setWeekItems(array $weekItems): self
    {
        $this->weekItems = $weekItems;

        return $this;
    }

    public function jsonSerialize()
    {
        return
            [
                'id' => $this->getId(),
                'weekItems'=> $this->getWeekItems(),
                'gamificationNotifications' => $this->getGamificationNotifications(),
            ];
    }
}
