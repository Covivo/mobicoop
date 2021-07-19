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

namespace App\Editorial\Admin\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Editorial\Entity\Editorial;
use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class EditorialVoter extends Voter
{
    const ADMIN_EDITORIAL_CREATE = 'admin_article_create';
    const ADMIN_EDITORIAL_READ = 'admin_article_read';
    const ADMIN_EDITORIAL_UPDATE = 'admin_article_update';
    const ADMIN_EDITORIAL_DELETE = 'admin_article_delete';
    const ADMIN_EDITORIAL_LIST = 'admin_article_list';
    const EDITORIAL_CREATE = 'article_create';
    const EDITORIAL_READ = 'article_read';
    const EDITORIAL_UPDATE = 'article_update';
    const EDITORIAL_DELETE = 'article_delete';
    const EDITORIAL_LIST = 'article_list';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ADMIN_EDITORIAL_CREATE,
            self::ADMIN_EDITORIAL_READ,
            self::ADMIN_EDITORIAL_UPDATE,
            self::ADMIN_EDITORIAL_DELETE,
            self::ADMIN_EDITORIAL_LIST
            ])) {
            return false;
        }

        // only vote on Editorial objects inside this voter
        if (!in_array($attribute, [
            self::ADMIN_EDITORIAL_CREATE,
            self::ADMIN_EDITORIAL_READ,
            self::ADMIN_EDITORIAL_UPDATE,
            self::ADMIN_EDITORIAL_DELETE,
            self::ADMIN_EDITORIAL_LIST
            ]) && !($subject instanceof Paginator) && !($subject instanceof Editorial)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::ADMIN_EDITORIAL_CREATE:
                return $this->canCreateEditorial();
            case self::ADMIN_EDITORIAL_READ:
                return $this->canReadEditorial($subject);
            case self::ADMIN_EDITORIAL_UPDATE:
                return $this->canUpdateEditorial($subject);
            case self::ADMIN_EDITORIAL_DELETE:
                return $this->canDeleteEditorial($subject);
            case self::ADMIN_EDITORIAL_LIST:
                return $this->canListEditorial();
            }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateEditorial()
    {
        return $this->authManager->isAuthorized(self::EDITORIAL_CREATE);
    }

    private function canReadEditorial(Editorial $editorial)
    {
        return $this->authManager->isAuthorized(self::EDITORIAL_READ, ['editorial'=>$editorial]);
    }

    private function canUpdateEditorial(Editorial $editorial)
    {
        return $this->authManager->isAuthorized(self::EDITORIAL_UPDATE, ['editorial'=>$editorial]);
    }

    private function canDeleteEditorial(Editorial $editorial)
    {
        return $this->authManager->isAuthorized(self::EDITORIAL_DELETE, ['editorial'=>$editorial]);
    }

    private function canListEditorial()
    {
        return $this->authManager->isAuthorized(self::EDITORIAL_LIST);
    }
}
