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

namespace App\Image\Admin\Service;

use App\Image\Entity\Image;
use App\Event\Entity\Event;
use App\MassCommunication\Entity\Campaign;
use App\MassCommunication\Repository\CampaignRepository;
use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Repository\RelayPointRepository;
use App\User\Entity\User;
use App\Community\Entity\Community;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FileManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use App\Event\Repository\EventRepository;
use App\Community\Repository\CommunityRepository;
use App\User\Repository\UserRepository;
use App\Image\Repository\ImageRepository;
use App\Image\Exception\OwnerNotFoundException;
use App\Image\Exception\ImageException;
use App\Image\Repository\IconRepository;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use ProxyManager\Exception\FileNotWritableException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Icon manager in administration context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class IconManager
{
    private $iconRepository;

    /**
     * Constructor.
     *
     */
    public function __construct(
        IconRepository $iconRepository
    ) {
        $this->iconRepository = $iconRepository;
    }

    /**
     * Get all icons
     *
     * @return void
     */
    public function getIcons(): array
    {
        return $this->iconRepository->findAll();
        // $return = [];

        // foreach ($icons as $icon) {
            
        // }

        // return $return;
    }
}
