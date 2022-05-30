<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace App\User\Security;

use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use App\Solidary\Entity\SolidaryUser;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\User\Ressource\Review;
use App\User\Ressource\ReviewDashboard;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class ReviewVoter extends Voter
{
    const REVIEW_CREATE = 'review_create';
    const REVIEW_READ = 'review_read';
    const REVIEW_UPDATE = 'review_update';
    const REVIEW_DELETE = 'review_delete';
    const REVIEW_LIST = 'review_list';
    
    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::REVIEW_CREATE,
            self::REVIEW_READ,
            self::REVIEW_UPDATE,
            self::REVIEW_DELETE,
            self::REVIEW_LIST,
            ])) {
            return false;
        }
      
        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::REVIEW_CREATE,
            self::REVIEW_READ,
            self::REVIEW_UPDATE,
            self::REVIEW_DELETE,
            self::REVIEW_LIST,
            ]) && !($subject instanceof Paginator) && !$subject instanceof Review) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::REVIEW_CREATE:
                return $this->canCreateReview($subject);
            case self::REVIEW_READ:
                return $this->canReadReview();
            case self::REVIEW_UPDATE:
                return $this->canUpdateReview($subject);
            case self::REVIEW_DELETE:
                return $this->canDeleteReview($subject);
            case self::REVIEW_LIST:
                return $this->canListReview();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateReview(Review $review)
    {
        return $this->authManager->isAuthorized(self::REVIEW_CREATE, ['review'=>$review]);
    }

    private function canReadReview()
    {
        return $this->authManager->isAuthorized(self::REVIEW_READ);
    }

    private function canUpdateReview(Review $review)
    {
        return $this->authManager->isAuthorized(self::REVIEW_UPDATE, ['review'=>$review]);
    }
    
    private function canDeleteReview(Review $review)
    {
        return $this->authManager->isAuthorized(self::REVIEW_DELETE, ['review'=>$review]);
    }
    
    private function canListReview()
    {
        return $this->authManager->isAuthorized(self::REVIEW_LIST);
    }
}
