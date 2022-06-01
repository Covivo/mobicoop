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
 */

namespace App\Image\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Auth\Service\AuthManager;
use App\Image\Entity\Icon;
use App\Image\Entity\Image;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ImageVoter extends Voter
{
    public const IMAGE_CREATE = 'image_create';
    public const IMAGE_READ = 'image_read';
    public const IMAGE_UPDATE = 'image_update';
    public const IMAGE_DELETE = 'image_delete';
    public const IMAGE_LIST = 'image_list';
    public const IMAGE_REGENVERSIONS = 'images_regenversions';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::IMAGE_CREATE,
            self::IMAGE_READ,
            self::IMAGE_UPDATE,
            self::IMAGE_DELETE,
            self::IMAGE_LIST,
            self::IMAGE_REGENVERSIONS,
        ])) {
            return false;
        }

        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::IMAGE_CREATE,
            self::IMAGE_READ,
            self::IMAGE_UPDATE,
            self::IMAGE_DELETE,
            self::IMAGE_LIST,
            self::IMAGE_REGENVERSIONS,
        ]) && !($subject instanceof Paginator) && !$subject instanceof Image && !$subject instanceof Icon) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::IMAGE_CREATE:
                return $this->canCreateImage();

            case self::IMAGE_READ:
                return ($subject instanceof Icon) ? $this->canReadIcon($subject) : $this->canReadImage($subject);

            case self::IMAGE_UPDATE:
                return $this->canUpdateImage($subject);

            case self::IMAGE_DELETE:
                return $this->canDeleteImage($subject);

            case self::IMAGE_LIST:
                return $this->canListImages();

            case self::IMAGE_REGENVERSIONS:
                return $this->canRegenVersions();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateImage()
    {
        return $this->authManager->isAuthorized(self::IMAGE_CREATE);
    }

    private function canReadImage(Image $image)
    {
        return $this->authManager->isAuthorized(self::IMAGE_READ, ['image' => $image]);
    }

    private function canReadIcon(Icon $icon)
    {
        return $this->authManager->isAuthorized(self::IMAGE_READ, ['icon' => $icon]);
    }

    private function canUpdateImage(Image $image)
    {
        return $this->authManager->isAuthorized(self::IMAGE_UPDATE, ['image' => $image]);
    }

    private function canDeleteImage(Image $image)
    {
        return $this->authManager->isAuthorized(self::IMAGE_DELETE, ['image' => $image]);
    }

    private function canListImages()
    {
        return $this->authManager->isAuthorized(self::IMAGE_LIST);
    }

    private function canRegenVersions()
    {
        return $this->authManager->isAuthorized(self::IMAGE_REGENVERSIONS);
    }
}
