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
use App\Service\FileManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;

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
    private $types;
    
    private $filterManager;
    private $dataManager;
    private $container;
    
    public function __construct(EntityManagerInterface $entityManager, FileManager $fileManager, ContainerInterface $container, array $types)
    {
        $this->entityManager = $entityManager;
        $this->fileManager = $fileManager;
        $this->types = $types;
        $this->filterManager = $container->get('liip_imagine.filter.manager');
        $this->dataManager = $container->get('liip_imagine.data.manager');
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
     * Generates the different versions of the image (thumbnails).
     * Returns the names of the generated versions
     * @param Image $image
     * @param object $owner
     * @return array
     */
    public function generateVersions(Image $image, object $owner)
    {
        $versions = [];
        $types = $this->types[strtolower((new \ReflectionClass($owner))->getShortName())];
        foreach ($types['thumbnail']['sizes'] as $thumbnail) {
            $fileName = $image->getFileName();
            $extension = null;
            if ($extension = $this->fileManager->getExtension($fileName)) {
                $fileName = substr($fileName, 0, -(strlen($extension)+1));
            }
            $version = $this->generateVersion(
                $image,
                $types['folder']['plain'],
                $types['folder']['thumbnail'],
                $fileName,
                $thumbnail['filterSet'],
                $extension ? $extension : 'nc',
                $thumbnail['prefix']
            );
                $versions[] = $version;
        }
        // TODO : verify each version
        return $versions;
    }
    
    /**
     * Get the different versions of the image (thumbnails).
     * Returns the names of the generated versions
     * @param Image $image
     * @return array
     */
    public function getVersions(Image $image)
    {
        $versions = [];
        $types = $this->types[strtolower((new \ReflectionClass($owner))->getShortName())];
        foreach ($types['thumbnail']['sizes'] as $thumbnail) {
            $fileName = $image->getFileName();
            $extension = null;
            if ($extension = $this->fileManager->getExtension($fileName)) {
                $fileName = substr($fileName, 0, -(strlen($extension)+1));
            }
            $versionName = $thumbnail['prefix'] . $fileName . "." . $extension;
            if (file_exists($types['folder']['thumbnail'] . "/" . $versionName)) $versions[] = $versionName;
        }
        // TODO : verify each version
        return $versions;
    }
    
    /**
     * Generates a version of an image.
     * @param Image $image
     * @param string $folderOrigin
     * @param string $folderDestination
     * @param string $fileName
     * @param string $filter
     * @param string $extension
     * @param string $prefix
     * @return string
     */
    private function generateVersion(
        Image $image,
        string $folderOrigin,
        string $folderDestination,
        string $fileName,
        string $filter,
        string $extension,
        string $prefix
        ) {
        $versionName = $prefix . $fileName . "." . $extension;
        
        $liipImage = $this->dataManager->find($filter, $folderOrigin.'/'.$image->getFileName());

        $resized = $this->filterManager->applyFilter($liipImage, $filter)->getContent();
        self::saveImage($resized, $versionName, $folderDestination);
        
        return $versionName;
    }
    
    /**
     * Save a binay to a file.
     *
     * @param String $blob      The binary string
     * @param String $fileName  The file
     * @param String $directory The folder
     */
    private function saveImage($blob,$fileName,$directory) {
        $file = fopen($directory."/".$fileName, 'w');
        fwrite($file, $blob);
        fclose($file);
    }
    
    // TODO : create methods to modify the position and filename of an image set if an image of the set is deleted, or if the position changes (switch between images) etc...
    
    
}
