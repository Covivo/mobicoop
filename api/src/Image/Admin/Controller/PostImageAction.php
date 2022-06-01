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
 */

namespace App\Image\Admin\Controller;

use App\Image\Entity\Image;
use App\Image\Service\ImageManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class PostImageAction
{
    private $imageManager;

    public function __construct(ImageManager $imageManager)
    {
        $this->imageManager = $imageManager;
    }

    public function __invoke(Request $request): Image
    {
        $image = new Image();

        // check if file is present
        if ($request->files->get('userFile') && $request->request->get('userId')) {
            // User image
            $image->setUserFile($request->files->get('userFile'));
            $image->setUserId($request->request->get('userId'));
        } elseif ($request->files->get('communityFile') && $request->request->get('communityId')) {
            // Community image
            $image->setCommunityFile($request->files->get('communityFile'));
            $image->setCommunityId($request->request->get('communityId'));
        } elseif ($request->files->get('eventFile') && $request->request->get('eventId')) {
            // Event image
            $image->setEventFile($request->files->get('eventFile'));
            $image->setEventId($request->request->get('eventId'));
        } elseif ($request->files->get('relayPointFile') && $request->request->get('relayPointId')) {
            // RelayPoint image
            $image->setRelayPointFile($request->files->get('relayPointFile'));
            $image->setRelayPointId($request->request->get('relayPointId'));
        } elseif ($request->files->get('relayPointTypeFile') && $request->request->get('relayPointTypeId')) {
            // RelayPointType image
            $image->setRelayPointTypeFile($request->files->get('relayPointTypeFile'));
            $image->setRelayPointTypeId($request->request->get('relayPointTypeId'));
        } elseif ($request->files->get('campaignFile') && $request->request->get('campaignId')) {
            // Campaign image
            $image->setCampaignFile($request->files->get('campaignFile'));
            $image->setCampaignId($request->request->get('campaignId'));
        } elseif ($request->files->get('editorialFile') && $request->request->get('editorialId')) {
            // Editorial image
            $image->setEditorialFile($request->files->get('editorialFile'));
            $image->setEditorialId($request->request->get('editorialId'));
        } else {
            throw new BadRequestHttpException('A valid file is required');
        }

        $image->setName($request->request->get('name'));
        $image->setOriginalName($request->request->get('originalName'));
        $image->setTitle($request->request->get('title'));
        $image->setAlt($request->request->get('alt'));
        $image->setCropX1($request->request->get('cropX1'));
        $image->setCropX2($request->request->get('cropX2'));
        $image->setCropY1($request->request->get('cropY1'));
        $image->setCropY2($request->request->get('cropY2'));

        // we search the future owner of the image (user ? event ?...)
        if ($owner = $this->imageManager->getOwner($image)) {
            // we associate the owner and the image
            $owner->addImage($image);

            // we rename the image depending on the owner and the position
            if (is_null($request->request->get('position'))) {
                $image->setPosition($this->imageManager->getNextPosition($image));
            } else {
                // the image position is provided, we remove the existing image at this position
                $image->setPosition($request->request->get('position'));
            }
            $image->setFileName($this->imageManager->generateFilename($image));
            if (is_null($image->getName())) {
                $image->setName($image->getFileName());
            }

            if (!is_null($request->request->get('position'))) {
                // the image position is provided, we remove the existing image at this position
                $this->imageManager->removeImageAtPosition($owner, $request->request->get('position'));
            }
        }

        return $image;
    }
}
