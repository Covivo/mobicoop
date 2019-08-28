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
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use App\Event\Repository\EventRepository;
use App\Image\Repository\ImageRepository;
use App\Image\Exception\OwnerNotFoundException;
use App\Image\Exception\ImageException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use ProxyManager\Exception\FileNotWritableException;

/**
 * Image manager.
 *
 * This service contains methods related to image manipulations.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ImageManager
{
    private $eventRepository;
    private $imageRepository;
    private $fileManager;
    private $types;
    
    private $filterManager;
    private $dataManager;
    private $logger;
    
    /**
     * Constructor.
     *
     * @param EventRepository $eventRepository
     * @param ImageRepository $imageRepository
     * @param FileManager $fileManager
     * @param ContainerInterface $container
     * @param LoggerInterface $logger
     * @param array $types
     */
    public function __construct(EventRepository $eventRepository, ImageRepository $imageRepository, FileManager $fileManager, ContainerInterface $container, LoggerInterface $logger, array $types)
    {
        $this->eventRepository = $eventRepository;
        $this->imageRepository = $imageRepository;
        $this->fileManager = $fileManager;
        $this->types = $types;
        $this->filterManager = $container->get('liip_imagine.filter.manager');
        $this->dataManager = $container->get('liip_imagine.data.manager');
        $this->logger = $logger;
    }
    
    /**
     * Get the owner of the image.
     * @param Image $image
     * @throws OwnerNotFoundException
     * @return object
     */
    public function getOwner(Image $image): object
    {
        if (!is_null($image->getEventId())) {
            // the image is an image for an event
            return $this->eventRepository->find($image->getEventId());
        } elseif (!is_null($image->getEvent())) {
            // the image is an image for an event
            return $this->eventRepository->find($image->getEvent()->getId());
        }
        throw new OwnerNotFoundException('The owner of this image cannot be found');
    }
    
    /**
     * Returns the future position of a new image for an object.
     * @param Image $image
     * @return int
     */
    public function getNextPosition(Image $image)
    {
        return $this->imageRepository->findNextPosition($this->getOwner($image));
    }
    
    /**
     * Generates a filename depending on the class of the image owner.
     * @param Image $image
     * @throws ImageException
     * @return string
     */
    public function generateFilename(Image $image)
    {
        // note : the file extension will be added later (usually automatically) so we don't need to treat it now
        $owner = $this->getOwner($image);
        switch (get_class($owner)) {
            case Event::class:
                // TODO : define a standard for the naming of the images (name of the owner + position ? uuid ?)
                // for now, for an event, the filename will be the sanitized name of the event and the position of the image in the set
                if ($fileName = $this->fileManager->sanitize($owner->getName() . " " . $image->getPosition())) {
                    return $fileName;
                }
                break;
            default:
                break;
        }
        throw new ImageException('Cannot generate image filename for the owner of class ' . get_class($owner));
    }
    
    /**
     * Generates the different versions of the image (thumbnails).
     * Returns the names of the generated versions
     * @param Image $image
     * @return array
     * @throws \ReflectionException
     */
    public function generateVersions(Image $image)
    {
        $owner = $this->getOwner($image);
        $versions = [];
        $types = $this->types[strtolower((new \ReflectionClass($owner))->getShortName())];
        foreach ($types['versions'] as $version) {
            $fileName = $image->getFileName();
            $extension = null;
            if ($extension = $this->fileManager->getExtension($fileName)) {
                $fileName = substr($fileName, 0, -(strlen($extension)+1));
            }
            $generatedVersion = $this->generateVersion(
                $image,
                $types['folder']['base'],
                $types['folder']['plain'],
                $types['folder']['versions'],
                $fileName,
                $version['filterSet'],
                $extension ? $extension : 'nc',
                $version['prefix']
            );
            $versions[$version['filterSet']] = getenv('DATA_URI') . $generatedVersion;
        }
        return $versions;
    }
    
    /**
     * Get the different versions of the image (thumbnails).
     * Returns the names of the generated versions
     * @param Image $image
     * @return array
     * @throws \ReflectionException
     */
    public function getVersions(Image $image)
    {
        $versions = [];
        $owner = $this->getOwner($image);
        $types = $this->types[strtolower((new \ReflectionClass($owner))->getShortName())];
        foreach ($types['versions'] as $version) {
            $fileName = $image->getFileName();
            $extension = null;
            if ($extension = $this->fileManager->getExtension($fileName)) {
                $fileName = substr($fileName, 0, -(strlen($extension)+1));
            }
            $versionName = $types['folder']['versions'] . $version['prefix'] . $fileName . "." . $extension;
            if (file_exists($types['folder']['base'].$versionName)) {
                $versions[$version['filterSet']] = getenv('DATA_URI') . $versionName;
            }
        }
        return $versions;
    }
    
    /**
     * Delete the different versions
     * @param Image $image
     * @throws \ReflectionException
     */
    public function deleteVersions(Image $image): void
    {
        $owner = $this->getOwner($image);
        $types = $this->types[strtolower((new \ReflectionClass($owner))->getShortName())];
        foreach ($types['versions'] as $version) {
            $fileName = $image->getFileName();
            $extension = null;
            if ($extension = $this->fileManager->getExtension($fileName)) {
                $fileName = substr($fileName, 0, -(strlen($extension)+1));
            }
            $versionName = $types['folder']['base'].$types['folder']['versions'] . $version['prefix'] . $fileName . "." . $extension;
            if (file_exists($versionName)) {
                unlink($versionName);
            }
        }
    }
    
    /**
     * Generates a version of an image.
     * @param Image $image
     * @param string $baseFolder
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
        string $baseFolder,
        string $folderOrigin,
        string $folderDestination,
        string $fileName,
        string $filter,
        string $extension,
        string $prefix
    ) {
        $versionName = $prefix . $fileName . "." . $extension;
        $liipImage = $this->dataManager->find($filter, $baseFolder.$folderOrigin.$image->getFileName());
        $resized = $this->filterManager->applyFilter($liipImage, $filter)->getContent();
        $this->saveImage($resized, $versionName, $baseFolder.$folderDestination);
        return $versionName;
    }
    
    /**
     * Save a binay to a file.
     *
     * @param String $blob      The binary string
     * @param String $fileName  The file
     * @param String $directory The folder
     * @throws \DomainException
     * @throws FileNotWritableException
     */
    private function saveImage($blob, $fileName, $directory)
    {
        if ($blob == '') {
            throw new \DomainException('Empty blob string');
        }
        if (!$file = fopen($directory.$fileName, 'w')) {
            throw new FileNotWritableException('File ' . $directory . $fileName . ' is not writable');
        }
        if (!fwrite($file, $blob)) {
            throw new FileNotWritableException('Cannot write to ' . $directory . $fileName);
        }
        fclose($file);
    }
    
    /** TODO : create methods to :
     * - modify the position and filename of images of a set if positions change (switch between images)
     * - modify the position and filename of images of a set if an image of the set is deleted
     * - recreate all images of a set (if an error occurs, or if an image is accidentally deleted, or if the name of the owner changes)
     */
}
