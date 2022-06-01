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
 */

namespace App\Rdex\Entity;

use Symfony\Component\Serializer\Annotation\Groups;

/**
 * An RDEX Connection User (sender or recipient).
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class RdexConnectionUser
{
    /**
     * @var string the uuid of the user
     *
     * @Groups("rdex")
     */
    private $uuid;

    /**
     * @var string The alias of the user
     *
     * @Groups("rdex")
     */
    private $alias;

    /**
     * @var string The state of the user (recipient or driver)
     *
     * @Groups("rdex")
     */
    private $state;

    public function getUuid(): ?int
    {
        return $this->uuid;
    }

    public function setUuid(int $uuid): self
    {
        $this->uuid = $uuid;

        return $this;
    }

    public function getAlias(): ?string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): self
    {
        $this->alias = $alias;

        return $this;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }
}
