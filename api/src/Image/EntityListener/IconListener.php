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

namespace App\Image\EntityListener;

use App\Image\Entity\Icon;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;

class IconListener
{
    private $types;
    private $dataUri;

    public function __construct(array $types, string $dataUri)
    {
        $this->types = $types;
        $this->dataUri = $dataUri;
    }

    /** @ORM\PostLoad */
    public function postLoadHandler(Icon $icon, LifecycleEventArgs $args)
    {
        $icon->setUrl($this->dataUri.$this->types['icons']['folder']['plain'].$icon->getFileName());
    }
}
