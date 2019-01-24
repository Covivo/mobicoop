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

namespace App\Image\Service;

use App\Image\Entity\Image;
use App\Event\Entity\Event;
use Doctrine\ORM\EntityManagerInterface;
use App\Image\Entity\ImageType;
use App\Service\FileManager;

/**
 * Image manager.
 *
 * This service contains methods related to image manipulations.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ImageManager
{
    private $entityManager;
    private $fileManager;
    
    public function __construct(EntityManagerInterface $entityManager, FileManager $fileManager)
    {
        $this->entityManager = $entityManager;
        $this->fileManager = $fileManager;
    }
    
    /**
     * Get the owner of the image.
     * Returns the owner or false if no valid owner is found.
     * @param Image $image
     * @return object|false
     */
    public function getOwner(Image $image)
    {
        if (!is_null($image->getEventId())) {
            // the image is an image for an event
            return $this->entityManager->getRepository(Event::class)->find($image->getEventId());
        }
        return false;
    }
    
    /**
     * Returns the position of an image for an object.
     * @param Image $image
     * @param object $owner
     * @return int
     */
    public function getNextPosition(Image $image, object $owner)
    {
        return $this->entityManager->getRepository(Image::class)->findNextPosition($owner);
    }
    
    /**
     * Generates a filename depending on the class of the image owner.
     * @param Image $image
     * @param object $owner
     * @return string|boolean
     */
    public function generateFilename(Image $image, object $owner)
    {
        // note : the file extension will be automatically added
        switch (get_class($owner)) {
            case Event::class:
                // TODO : define a standard for the naming of the images (name of the owner + position ? uuid ?)
                // for now, for an event, the filename will be the sanitized name of the event and the position of the image in the set
                return $this->fileManager->sanitize($owner->getName() . " " . $image->getPosition());
                break;
            default:
                break;
        }
        return false;
    }
    
    /**
     * Get the image type depending on the class of the image owner.
     * @param Image $image
     * @param object $owner
     * @return object|NULL|boolean
     */
    public function getImageType(Image $image, object $owner)
    {
        switch (get_class($owner)) {
            case Event::class:
                return $this->entityManager->getRepository(ImageType::class)->find(ImageType::TYPE_EVENT);
                break;
            default:
                break;
        }
        return false;
    }
    
    /**
     * Apply treatments to the provided image (it should already be saved).
     * Depending of image type (= the entity associated with the image : User, Event...), different treatments can be applied.
     * @param Image $image
     * @return \App\Image\Entity\Image
     */
    public function treat(Image $image)
    {
        switch ($image->getImageType()->getId()) {
            case ImageType::TYPE_EVENT:
                // Prevent the serialization of the file property
                $image->setEventFile(null);
                // TODO : resize, thumbnails...
                break;
        }
        return $image;
    }
    
    // TODO : create methods to modify the position and filename of an image set if an image of the set is deleted, or if the position changes (swicth between images) etc...
}
