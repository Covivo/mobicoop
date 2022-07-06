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

namespace App\User\EventListener;

use App\User\Entity\IdentityProof;
use App\User\Entity\User;
use App\User\Service\UserManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * User Event listener.
 */
class UserLoadListener
{
    private $avatarSizes;
    private $avatarDefaultFolder;
    private $userReviewActive;
    private $userManager;
    private $identityValidation;

    public function __construct(UserManager $userManager, $params)
    {
        $this->avatarSizes = $params['avatarSizes'];
        $this->avatarDefaultFolder = $params['avatarDefaultFolder'];
        $this->userReviewActive = $params['userReview'];
        $this->userManager = $userManager;
        $this->identityValidation = $params['identityValidation'];
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $sizes = json_decode($this->avatarSizes, true);
        $user = $args->getEntity();

        if ($user instanceof User) {
            // keep the phone number in case of update
            $user->setOldTelephone($user->getTelephone());
            $user->setOldEmail($user->getEmail());

            $images = $user->getImages();
            foreach ($sizes as $size) {
                if (count($images) > 0 && count($images[0]->getVersions()) > 0 && isset($images[0]->getVersions()[$size])) {
                    $user->addAvatar($images[0]->getVersions()[$size]);
                }
            }
            if (is_null($user->getAvatars())) {
                foreach ($sizes as $size) {
                    if (in_array($size, User::AUTHORIZED_SIZES_DEFAULT_AVATAR)) {
                        $user->addAvatar($this->avatarDefaultFolder.$size.'.svg');
                    }
                }
            }
            $user->setUserReviewsActive($this->userReviewActive);
            $publicProfile = $this->userManager->getPublicProfile($user);
            $user->setExperienced((!is_null($publicProfile)) ? $publicProfile->getProfileSummary()->isExperienced() : false);
            $user->setSavedCo2((!is_null($publicProfile)) ? $publicProfile->getProfileSummary()->getSavedCo2() : false);
            $user->setVerifiedIdentity(null);
            if ($this->identityValidation && ($user->isHitchHikeDriver() || $user->isHitchHikePassenger())) {
                $user->setVerifiedIdentity(IdentityProof::STATUS_ACCEPTED == $user->getIdentityStatus());
            }
        }
    }
}
