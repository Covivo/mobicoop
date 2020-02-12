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

namespace App\Article\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Action\Entity\Action;
use App\Article\Entity\Article;
use App\Article\Entity\Paragraph;
use App\Article\Entity\Section;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use App\Right\Service\PermissionManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class ArticleVoter extends Voter
{
    const ARTICLE_READ = 'article_read';
    const ARTICLES_READ = 'articles_read';
    const ARTICLE_CREATE = 'article_create';
    const ARTICLE_UPDATE = 'article_update';
    const ARTICLE_DELETE = 'article_delete';
    
    private $permissionManager;

    public function __construct(PermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ARTICLE_READ,
            self::ARTICLES_READ,
            self::ARTICLE_CREATE,
            self::ARTICLE_UPDATE,
            self::ARTICLE_DELETE
            ])) {
            return false;
        }

        // only vote on Article objects inside this voter
        // only for items actions
        if (!in_array($attribute, [
            self::ARTICLE_READ,
            self::ARTICLE_CREATE,
            self::ARTICLE_UPDATE,
            self::ARTICLE_DELETE
            ]) && !($subject instanceof Paginator) && !($subject instanceof Article || $subject instanceof Section || $subject instanceof Paragraph)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $requester = $token->getUser();

        switch ($attribute) {
            case self::ARTICLE_READ:
                return $this->canReadArticle($requester, $subject);
            case self::ARTICLES_READ:
                return $this->canReadArticles($requester);
            case self::ARTICLE_CREATE:
                return $this->canCreateArticle($requester);
            case self::ARTICLE_UPDATE:
                return $this->canUpdateArticle($requester, $subject);
            case self::ARTICLE_DELETE:
                return $this->canDeleteArticle($requester, $subject);
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canReadArticle(UserInterface $requester, Article $article)
    {
        // only registered users/apps can read articles
        if (!$requester instanceof UserInterface) {
            return false;
        }
        return $this->permissionManager->checkPermission('article_read', $requester, null, $article->getId());
    }

    private function canReadArticles(UserInterface $requester)
    {
        // only registered users/apps can read articles
        if (!$requester instanceof UserInterface) {
            return false;
        }
        return $this->permissionManager->checkPermission('article_read', $requester);
    }

    private function canCreateArticle(UserInterface $requester)
    {
        // only registered users/apps can create articles
        if (!$requester instanceof UserInterface) {
            return false;
        }
        return $this->permissionManager->checkPermission('article_create', $requester);
    }

    private function canUpdateArticle(UserInterface $requester, Article $article)
    {
        // only registered users/apps can update articles
        if (!$requester instanceof UserInterface) {
            return false;
        }
        return $this->permissionManager->checkPermission('article_update', $requester, null, $article->getId());
    }

    private function canDeleteArticle(UserInterface $requester, Article $article)
    {
        // only registered users/apps can delete articles
        if (!$requester instanceof UserInterface) {
            return false;
        }
        return $this->permissionManager->checkPermission('article_delete', $requester, null, $article->getId());
    }
}
