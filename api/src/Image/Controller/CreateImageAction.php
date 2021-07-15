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

namespace App\Image\Controller;

use App\TranslatorTrait;
use Symfony\Component\HttpFoundation\Request;
use App\Image\Service\ImageManager;
use App\Image\Entity\Image;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use App\Action\Event\ActionEvent;
use App\Action\Repository\ActionRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class CreateImageAction
{
    use TranslatorTrait;
    private $imageManager;
    private $logger;
    private $actionRepository;
    private $eventDispatcher;
    
    public function __construct(ImageManager $imageManager, LoggerInterface $logger, ActionRepository $actionRepository, EventDispatcherInterface $eventDispatcher)
    {
        $this->imageManager = $imageManager;
        $this->logger = $logger;
        $this->actionRepository = $actionRepository;
        $this->eventDispatcher = $eventDispatcher;
    }
    
    public function __invoke(Request $request): Image
    {
        if (is_null($request)) {
            throw new \InvalidArgumentException($this->translator->trans("Bad request"));
        }

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
        } elseif ($request->files->get('badgeFile') && $request->request->get('badgeId')) {
            // Badge icon
            $image->setBadgeFile($request->files->get('badgeFile'));
            $image->setBadgeId($request->request->get('badgeId'));
        } elseif ($request->files->get('badgeFile') && $request->request->get('badgeImageId')) {
            // Badge image
            $image->setBadgeFile($request->files->get('badgeFile'));
            $image->setBadgeImageId($request->request->get('badgeImageId'));
        } elseif ($request->files->get('badgeFile') && $request->request->get('badgeImageLightId')) {
            // Badge image light
            $image->setBadgeFile($request->files->get('badgeFile'));
            $image->setBadgeImageLightId($request->request->get('badgeImageLightId'));
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
            if (!is_null($image->getBadgeId())) {
                $image->setBadge($owner);
            } elseif (!is_null($image->getBadgeImageId())) {
                $image->setBadgeImage($owner);
            } elseif (!is_null($image->getBadgeImageLightId())) {
                $image->setBadgeImageLight($owner);
            } else {
                // we associate the owner and the image
                $owner->addImage($image);
            }
            
            // we search the position of the image if not provided
            if (is_null($image->getPosition())) {
                $image->setPosition($this->imageManager->getNextPosition($image));
            } else {
                // the image position is provided, we remove the existing image at this position
                $this->imageManager->removeImageAtPosition($owner, $image->getPosition());
            }
            
            // we rename the image depending on the owner
            $image->setFileName($this->imageManager->generateFilename($image));
            if (is_null($image->getName())) {
                $image->setName($image->getFileName());
            }
        }

        //  we dispatch the gamification event associated
        if ($image->getUser()) {
            $action = $this->actionRepository->findOneBy(['name'=>'user_avatar_uploaded']);
            $actionEvent = new ActionEvent($action, $image->getUser());
            $this->eventDispatcher->dispatch($actionEvent, ActionEvent::NAME);
        }

        return $image;
    }
}
