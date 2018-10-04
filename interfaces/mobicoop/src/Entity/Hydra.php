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

namespace App\Entity;

/**
 * A hydra collection object (returned by a JSON-LD Rest API).
 */
class Hydra
{
    
    /**
     * @var string $context The context of the collection.
     */
    private $context;
    
    /**
     * @var int $id The id of the collection.
     */
    private $id;
    
    /**
     * @var string $type The type of the collection.
     */
    private $type;
    
    /**
     * @var array $member The hydra member.
     */
    private $member;
    
    /**
     * @var int $totalItems The total number of items of the collection.
     */
    private $totalItems;
    
    /**
     * @var HydraView $view The view of the collection.
     */
    private $view;
        
    public function getId()
    {
        return $this->id;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMember()
    {
        return $this->member;
    }

    public function getTotalItems()
    {
        return $this->totalItems;
    }

    public function getView()
    {
        return $this->view;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setContext($context)
    {
        $this->context = $context;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    public function setMember($member)
    {
        $this->member = $member;
    }
    
    public function setTotalItems($totalItems)
    {
        $this->totalItems = $totalItems;
    }

    public function setView($view)
    {
        $this->view = $view;
    }
}
