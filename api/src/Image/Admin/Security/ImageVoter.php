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

namespace App\Image\Admin\Security;

use App\Auth\ServiceAdmin\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Image\Entity\Image;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;

class ImageVoter extends Voter
{
    const ADMIN_IMAGE_POST = 'admin_image_post';

    private $request;
    private $authManager;

    public function __construct(
        RequestStack $requestStack,
        AuthManager $authManager
    ) {
        $this->request = $requestStack->getCurrentRequest();
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ADMIN_IMAGE_POST,
            ])) {
            return false;
        }
        // only vote on Image objects inside this voter
        // only for items actions
        // if (in_array($attribute, [
        //     self::ADMIN_IMAGE_POST
        //     ]) && !$subject instanceof Image) {
        //     return false;
        // }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $requester = $token->getUser();
        
        switch ($attribute) {
            case self::ADMIN_IMAGE_POST:
                return $this->canPost($requester, $this->request);
        
        }

        throw new \LogicException('This code should not be reached!');
    }
    
    private function canPost(UserInterface $requester, Request $request)
    {
        return true;
    }
}
