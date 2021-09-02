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

namespace App\Image\Service;

use App\Image\Entity\Image;
use App\Event\Entity\Event;
use App\MassCommunication\Entity\Campaign;
use App\MassCommunication\Repository\CampaignRepository;
use App\RelayPoint\Entity\RelayPoint;
use App\RelayPoint\Repository\RelayPointRepository;
use App\User\Entity\User;
use App\Gamification\Entity\Badge;
use App\Community\Entity\Community;
use Doctrine\Common\Util\ClassUtils;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FileManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Filesystem\Filesystem;
use App\Event\Repository\EventRepository;
use App\Community\Repository\CommunityRepository;
use App\Editorial\Entity\Editorial;
use App\User\Repository\UserRepository;
use App\Image\Repository\ImageRepository;
use App\Image\Exception\OwnerNotFoundException;
use App\Image\Exception\ImageException;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use ProxyManager\Exception\FileNotWritableException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Gamification\Repository\BadgeRepository;
use App\Editorial\Repository\EditorialRepository;

/**
 * Image manager.
 *
 * This service contains methods related to image manipulations.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class ImageManager
{
    private $eventRepository;
    private $communityRepository;
    private $userRepository;
    private $imageRepository;
    private $relayPointRepository;
    private $campaignRepository;

    private $fileManager;
    private $types;
    private $filterManager;
    private $dataManager;
    private $loggerMaintenance;
    private $entityManager;
    private $dataUri;
    private $badgeRepository;
    private $editorialRepository;


    /**
     * Constructor.
     *
     * @param EventRepository $eventRepository
     * @param CommunityRepository $communityRepository
     * @param UserRepository $userRepository
     * @param ImageRepository $imageRepository
     * @param FileManager $fileManager
     * @param ContainerInterface $container
     * @param LoggerInterface $loggerMaintenance
     * @param CampaignRepository $campaign
     * @param BadgeRepository $badge
     * @param EditorialRepository $editorial
     * @param array $types
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        RelayPointRepository $relayPointRepository,
        EventRepository $eventRepository,
        UserRepository $userRepository,
        CommunityRepository $communityRepository,
        ImageRepository $imageRepository,
        FileManager $fileManager,
        ContainerInterface $container,
        LoggerInterface $maintenanceLogger,
        array $types,
        CampaignRepository $campaignRepository,
        string $dataUri,
        BadgeRepository $badgeRepository,
        EditorialRepository $editorialRepository
    ) {
        $this->entityManager = $entityManager;
        $this->eventRepository = $eventRepository;
        $this->communityRepository = $communityRepository;
        $this->userRepository = $userRepository;
        $this->imageRepository = $imageRepository;
        $this->relayPointRepository = $relayPointRepository;
        $this->campaignRepository = $campaignRepository;

        $this->fileManager = $fileManager;
        $this->types = $types;
        $this->filterManager = $container->get('liip_imagine.filter.manager');
        $this->dataManager = $container->get('liip_imagine.data.manager');
        $this->loggerMaintenance = $maintenanceLogger;
        $this->dataUri = $dataUri;
        $this->badgeRepository = $badgeRepository;
        $this->editorialRepository = $editorialRepository;
    }
    
    /**
     * Get the owner of the image.
     * @param Image $image
     * @throws OwnerNotFoundException
     */
    public function getOwner(Image $image)
    {
        if (!is_null($image->getEventId())) {
            // the image is an image for an event
            return $this->eventRepository->find($image->getEventId());
        } elseif (!is_null($image->getEvent())) {
            // the image is an image for an event
            return $this->eventRepository->find($image->getEvent()->getId());
        } elseif (!is_null($image->getCommunityId())) {
            // the image is an image for a community
            return $this->communityRepository->find($image->getCommunityId());
        } elseif (!is_null($image->getCommunity())) {
            // the image is an image for a community
            return $this->communityRepository->find($image->getCommunity()->getId());
        } elseif (!is_null($image->getUserId())) {
            // the image is an image for a user
            return $this->userRepository->find($image->getUserId());
        } elseif (!is_null($image->getUser())) {
            // the image is an image for a user
            return $this->userRepository->find($image->getUser()->getId());
        } elseif (!is_null($image->getRelayPoint())) {
            // the image is an image for a relay point
            return $this->relayPointRepository->find($image->getRelayPoint()->getId());
        } elseif (!is_null($image->getRelayPointId())) {
            // the image is an image for a relay point
            return $this->relayPointRepository->find($image->getRelayPointId());
        } elseif (!is_null($image->getCampaign())) {
            // the image is an image for a campaign
            return $this->campaignRepository->find($image->getCampaign()->getId());
        } elseif (!is_null($image->getCampaignId())) {
            // the image is an image for a campaign
            return $this->campaignRepository->find($image->getCampaignId());
        } elseif (!is_null($image->getBadgeIcon())) {
            // the icon is an image for a badge
            return $this->badgeRepository->find($image->getBadgeIcon()->getId());
        } elseif (!is_null($image->getBadgeIconId())) {
            // the icon is an image for a badge
            return $this->badgeRepository->find($image->getBadgeIconId());
        } elseif (!is_null($image->getBadgeDecoratedIcon())) {
            // the icon is an image for a badge
            return $this->badgeRepository->find($image->getBadgeDecoratedIcon()->getId());
        } elseif (!is_null($image->getBadgeDecoratedIconId())) {
            // the icon is an image for a badge
            return $this->badgeRepository->find($image->getBadgeDecoratedIconId());
        } elseif (!is_null($image->getBadgeImage())) {
            // the image is an image for a badge
            return $this->badgeRepository->find($image->getBadgeImage()->getId());
        } elseif (!is_null($image->getBadgeImageId())) {
            // the image is an image for a badge
            return $this->badgeRepository->find($image->getBadgeImageId());
        } elseif (!is_null($image->getBadgeImageLight())) {
            // the imageLight is an image for a badge
            return $this->badgeRepository->find($image->getBadgeImageLight()->getId());
        } elseif (!is_null($image->getBadgeImageLightId())) {
            // the imageLight is an image for a badge
            return $this->badgeRepository->find($image->getBadgeImageLightId());
        } elseif (!is_null($image->getEditorialId())) {
            // the image is an image for an editorial
            return $this->editorialRepository->find($image->getEditorialId());
        } elseif (!is_null($image->getEditorial())) {
            // the image is an image for an editorial
            return $this->editorialRepository->find($image->getEditorial()->getId());
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
        $getRealClass =  ClassUtils::getClass($owner);
        switch ($getRealClass) {
            case Event::class:
                // TODO : define a standard for the naming of the images (name of the owner + position ? uuid ?)
                // for now, for an event, the filename will be the sanitized name of the event and the position of the image in the set
                if ($fileName = $this->fileManager->sanitize($owner->getName() . " " . $image->getPosition())) {
                    return $fileName;
                }
                break;
            
            case Community::class:
                // TODO : define a standard for the naming of the images (name of the owner + position ? uuid ?)
                // for now, for a community, the filename will be the sanitized name of the community and the position of the image in the set
                if ($fileName = $this->fileManager->sanitize($owner->getName() . " " . $image->getPosition())) {
                    return $fileName;
                }
                break;
            case RelayPoint::class:
                // TODO : define a standard for the naming of the images (name of the owner + position ? uuid ?)
                // for now, for a community, the filename will be the sanitized name of the community and the position of the image in the set
                if ($fileName = $this->fileManager->sanitize($owner->getName() . " " . $image->getPosition())) {
                    return $fileName;
                }
                break;

            case User::class:
                // TODO : define a standard for the naming of the images (name of the owner + position ? uuid ?)
                // for now, for an user, the filename will be the sanitized name of the user and the position of the image in the set
                if ($fileName = $this->fileManager->sanitize($this->generateRandomName() . " " . $image->getPosition())) {
                    return $fileName;
                }
                break;
            case Campaign::class:
                // TODO : define a standard for the naming of the images (name of the owner + position ? uuid ?)
                // for now, for an event, the filename will be the sanitized name of the event and the position of the image in the set
                if ($fileName = $this->fileManager->sanitize($owner->getName() . " " . $image->getPosition())) {
                    return $fileName;
                }

                break;
            case Badge::class:
                // TODO : define a standard for the naming of the images (name of the owner + position ? uuid ?)
                // for now, for a badge, the filename will be the sanitized name of the event and the position of the image in the set
                if ($fileName = $this->fileManager->sanitize($owner->getName() . " " . $image->getPosition())) {
                    return $fileName;
                }

                break;
            case Editorial::class:
                // TODO : define a standard for the naming of the images (name of the owner + position ? uuid ?)
                // for now, for a badge, the filename will be the sanitized name of the event and the position of the image in the set
                if ($fileName = $this->fileManager->sanitize($owner->getTitle() . " " . $image->getPosition())) {
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
            $versions[$version['filterSet']] = $this->dataUri . $generatedVersion;
        }
        return $versions;
    }

    /**
     * Regen all images versions
     *
     * @return void
     */
    public function regenerateVersions()
    {
        set_time_limit(3600);
        $images = $this->imageRepository->findAll();
        foreach ($images as $image) {
            $this->generateVersions($image);
        }
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
                $versions[$version['filterSet']] = $this->dataUri . $versionName;
            }
        }

        // Add the original version
        $versions['original'] = $this->dataUri."".$types['folder']['plain']."".$image->getFileName();

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
     * Delete the image base and delete the Entry in DB
     * @param Image $image  The image to delete
     * @param bool $flush   Flush immediately
     * @throws \ReflectionException
     */
    public function deleteBase(Image $image, bool $flush = true): void
    {
        $owner = $this->getOwner($image);
        $types = $this->types[strtolower((new \ReflectionClass($owner))->getShortName())];

        $baseImage = $types['folder']['base'].$types['folder']['plain']. $image->getFileName();
        if (file_exists($baseImage)) {
            unlink($baseImage);
        }
        $this->entityManager->remove($image);
        if ($flush) {
            $this->entityManager->flush();
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
        if (file_exists(dirname(__FILE__)."/../../../public/".$baseFolder.$folderOrigin.$image->getFileName())) {
            $liipImage = $this->dataManager->find($filter, $baseFolder.$folderOrigin.$image->getFileName());
            $resized = $this->filterManager->applyFilter($liipImage, $filter)->getContent();
            $this->saveImage($resized, $versionName, dirname(__FILE__)."/../../../public/".$baseFolder.$folderDestination);
        }
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

    /**
     * Generate random file name
     *
     * @param integer $int
     * @return void
     */
    public function generateRandomName(int $int=15)
    {
        $randomName =  bin2hex(random_bytes($int));
    
        return $randomName;
    }

    /**
     * Remove the image at the given position, for the owner of the image
     *
     * @param object $image     The owner
     * @param integer $position The position of the image
     * @return void
     */
    public function removeImageAtPosition(object $owner, int $position)
    {
        if (method_exists($owner, 'getImages')) {
            foreach ($owner->getImages() as $image) {
                if ($image->getPosition() == $position) {
                    $this->deleteVersions($image);
                    $this->deleteBase($image, true);
                    break;
                }
            }
        }
    }

    /**
     * Remove all images without associated file
     *
     * @return void
     */
    public function removeFileless()
    {
        $images = $this->imageRepository->findAll();
        foreach ($images as $image) {
            $owner = $this->getOwner($image);
            $getRealClass =  ClassUtils::getClass($owner);
            switch ($getRealClass) {
                case Event::class:
                    if (!file_exists(dirname(__FILE__)."/../../../public/upload/events/images/".$image->getFileName())) {
                        $this->loggerMaintenance->info("ImageManager : remove image ". $image->getFileName() ." without associated file of the Event n°". $owner->getId() . " ." . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                        $this->deleteVersions($image);
                        $this->deleteBase($image, false);
                    }
                    break;
                case Community::class:
                    if (!file_exists(dirname(__FILE__)."/../../../public/upload/communities/images/".$image->getFileName())) {
                        $this->loggerMaintenance->info("ImageManager : remove image ". $image->getFileName() ." without associated file of the Community n°". $owner->getId() . " ." . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                        $this->deleteVersions($image);
                        $this->deleteBase($image, false);
                    }
                    break;
                case RelayPoint::class:
                    if (!file_exists(dirname(__FILE__)."/../../../public/upload/relaypoints/images/".$image->getFileName())) {
                        $this->loggerMaintenance->info("ImageManager : remove image ". $image->getFileName() ." without associated file of the RelayPoint n°". $owner->getId() . " ." . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                        $this->deleteVersions($image);
                        $this->deleteBase($image, false);
                    }
                    break;
                case User::class:
                    if (!file_exists(dirname(__FILE__)."/../../../public/upload/users/images/".$image->getFileName())) {
                        $this->loggerMaintenance->info("ImageManager : remove image ". $image->getFileName() ." without associated file of the User n°". $owner->getId() . " ." . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                        $this->deleteVersions($image);
                        $this->deleteBase($image, false);
                    }
                    break;
                case Campaign::class:
                    if (!file_exists(dirname(__FILE__)."/../../../public/upload/masscommunication/images/".$image->getFileName())) {
                        $this->loggerMaintenance->info("ImageManager : remove image ". $image->getFileName() ." without associated file of the Campaign n°". $owner->getId() . " ." . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                        $this->deleteVersions($image);
                        $this->deleteBase($image, false);
                    }
                    break;
                case Badge::class:
                    if (!file_exists(dirname(__FILE__)."/../../../public/upload/badges/images/".$image->getFileName())) {
                        $this->loggerMaintenance->info("ImageManager : remove image ". $image->getFileName() ." without associated file of the Badge n°". $owner->getId() . " ." . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                        $this->deleteVersions($image);
                        $this->deleteBase($image, false);
                    }
                    break;
                case Editorial::class:
                    if (!file_exists(dirname(__FILE__)."/../../../public/upload/editorials/images/".$image->getFileName())) {
                        $this->loggerMaintenance->info("ImageManager : remove image ". $image->getFileName() ." without associated file of the Editorial n°". $owner->getId() . " ." . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
                        $this->deleteVersions($image);
                        $this->deleteBase($image, false);
                    }
                    break;
                default:
                    break;
            }
        }
        $this->entityManager->flush();
    }

    /** TODO : create methods to :
     * - modify the position and filename of images of a set if positions change (switch between images)
     * - modify the position and filename of images of a set if an image of the set is deleted
     * - recreate all images of a set (if an error occurs, or if an image is accidentally deleted, or if the name of the owner changes)
     */
}
