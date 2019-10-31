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

namespace App\User\EventListener;

use App\User\Entity\User;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Image\Entity\Image;
use App\Image\Service\ImageManager;

/**
 * User Event listener
 */
class UserLoadListener
{
    private $avatarVersion;
    private $avatarDefault;
    private $imageManager;

    public function __construct(ImageManager $imageManager, $params)
    {
        $this->avatarVersion = $params['avatarVersion'];
        $this->avatarDefault = $params['avatarDefault'];
        $this->imageManager = $imageManager;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $user = $args->getEntity();
        if ($user instanceof User) {
            $images = $user->getImages();
            if (isset($images[0])) {
                $user->setAvatar($this->imageManager->getVersions($images[0])[$this->avatarVersion]);
            } else {
                $user->setAvatar($this->avatarDefault);
            }
        }
    }
}
