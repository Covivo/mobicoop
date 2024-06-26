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

namespace Mobicoop\Bundle\MobicoopBundle\Travel\Entity;

/**
 * Travel : travel mode.
 */
class TravelMode
{
    /**
     * @var int The id of this travel mode.
     */
    private $id;
    
    /**
     * @var string|null The iri of this travel mode.
     */
    private $iri;

    /**
     * @var string Name of the travel mode.
     */
    private $name;

    /**
     * @var string The Material design icon code of this travel mode
     */
    private $mdiIcon;

    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function setId(int $id)
    {
        $this->id = $id;
    }
    
    public function getIri()
    {
        return $this->iri;
    }
    
    public function setIri($iri)
    {
        $this->iri = $iri;
    }
    
    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getMdiIcon(): ?string
    {
        return $this->mdiIcon;
    }

    public function setMdiIcon(string $mdiIcon): self
    {
        $this->mdiIcon = $mdiIcon;

        return $this;
    }
}
