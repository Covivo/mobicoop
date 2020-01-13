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

namespace Mobicoop\Bundle\MobicoopBundle\JsonLD\Entity;

/**
 * A hydra view from an hydra collection object.
 */
class HydraView implements \JsonSerializable
{
    
    /**
     * @var int $id The id of the view.
     */
    private $id;
    
    /**
     * @var string The type of the view.
     */
    private $type;
    
    /**
     * @var string The first item of the view.
     */
    private $first;

    /**
     * @var string The last item of the view.
     */
    private $last;
    
    /**
     * @var string The next item of the view.
     */
    private $next;
    
    /**
     * @var string The previous item of the view.
     */
    private $previous;

    public function getId()
    {
        return $this->id;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getFirst()
    {
        return $this->first;
    }

    public function getLast()
    {
        return $this->last;
    }

    public function getNext()
    {
        return $this->next;
    }

    public function getPrevious()
    {
        return $this->previous;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setFirst($first)
    {
        $this->first = $first;
    }

    public function setLast($last)
    {
        $this->last = $last;
    }

    public function setNext($next)
    {
        $this->next = $next;
    }

    public function setPrevious($previous)
    {
        $this->$previous = $previous;
    }

    public function jsonSerialize()
    {
        return
        [
            'id'                => $this->getId(),
            'type'                => $this->getType(),
            'first'                => $this->getFirst(),
            'last'                => $this->getLast(),
            'next'                => $this->getNext(),
            'previous'                => $this->getPrevious(),
        ];
    }
}
