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

namespace Mobicoop\Bundle\MobicoopBundle\Stats\Entity;

use Mobicoop\Bundle\MobicoopBundle\Api\Entity\ResourceInterface;

/**
 * A short statistic indicator
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */

class Indicator implements ResourceInterface, \JsonSerializable
{

    /**
     * @var int The id of this Indicator
     */
    private $id;
    
    /**
     * @var string|null The iri of this Indicator.
     *
     */
    private $iri;

    /**
     * @var string The label of this Indicator
     */
    private $label;

    /**
     * @var float The value of this Indicator
     */
    private $value;

    /**
     * @var boolean True if this Indicator is used on Home Page
     */
    private $home;
    
    public function __construct($id=null)
    {
        if ($id) {
            $this->setId($id);
            $this->setIri("/indicators/".$id);
        }
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

    public function getIri()
    {
        return $this->iri;
    }
    
    public function setIri($iri)
    {
        $this->iri = $iri;
    }
    
    public function getLabel(): ?String
    {
        return $this->label;
    }

    public function setLabel(?String $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getValue(): ?float
    {
        return $this->value;
    }

    public function setvalue(?float $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function isHome(): ?bool
    {
        return $this->home;
    }

    public function setHome(?bool $home): self
    {
        $this->home = $home;

        return $this;
    }

    public function jsonSerialize()
    {
        return
            [
                'id'                            => $this->getId(),
                'iri'                           => $this->getIri(),
                'label'                         => $this->getLabel(),
                'value'                         => $this->getValue(),
                'home'                          => $this->isHome()
            ];
    }
}
