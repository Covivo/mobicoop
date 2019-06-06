<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\Permission\Entity;

/**
 * A permission.
 */
class Permission
{
    /**
     * @var int The id of this permission.
     */
    private $id;

    /**
     * @var string|null The iri of this permission.
     */
    private $iri;

    /**
     * @var boolean The permission
     */
    private $permission;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIri()
    {
        return $this->iri;
    }

    public function setIri($iri)
    {
        $this->iri = $iri;
    }

    public function getPermission(): bool
    {
        return $this->permission;
    }

    public function setPermission(bool $permission): self
    {
        $this->permission = $permission;

        return $this;
    }
}
