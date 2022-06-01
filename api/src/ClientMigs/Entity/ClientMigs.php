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

namespace App\ClientMigs\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * A client migration.
 *
 * @ORM\Entity
 * @ORM\Table(name="client_migs")
 */
class ClientMigs
{
    /**
     * @var string The id of this migration
     *
     * @ORM\Id
     * @ORM\Column(type="string")
     */
    private $version;

    /**
     * @var \DateTime The date and time of this migration
     *
     * @ORM\Column(type="datetime")
     */
    private $executedAt;

    public function getVersion(): string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getExecutedAt(): ?\DateTimeInterface
    {
        return $this->executedAt;
    }

    public function setExecutedAt(\DateTimeInterface $executedAt): self
    {
        $this->executedAt = $executedAt;

        return $this;
    }
}
