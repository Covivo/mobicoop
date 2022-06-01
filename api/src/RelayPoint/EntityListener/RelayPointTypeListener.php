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

namespace App\RelayPoint\EntityListener;

use App\Image\Entity\Icon;
use App\Image\Repository\IconRepository;
use App\RelayPoint\Entity\RelayPointType;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Event\LifecycleEventArgs;

class RelayPointTypeListener
{
    private $dataUri;
    private $iconRepository;

    public function __construct(string $dataUri, IconRepository $iconRepository)
    {
        $this->dataUri = $dataUri;
        $this->iconRepository = $iconRepository;
    }

    /** @ORM\PostLoad */
    public function postLoadHandler(RelayPointType $relaypointype, LifecycleEventArgs $args)
    {
        if (is_null($relaypointype->getIcon())) {
            $relaypointype->setIcon($this->iconRepository->find(Icon::DEFAULT_ICON_ID));
        }
    }
}
