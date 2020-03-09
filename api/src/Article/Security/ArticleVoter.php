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
use App\Article\Entity\Article;
use App\Article\Entity\Paragraph;
use App\Article\Entity\Section;
use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class ArticleVoter extends Voter
{
    const ARTICLE_CREATE = 'article_create';
    const ARTICLE_READ = 'article_read';
    const ARTICLE_UPDATE = 'article_update';
    const ARTICLE_DELETE = 'article_delete';
    const ARTICLE_LIST = 'article_list';
    
    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ARTICLE_CREATE,
            self::ARTICLE_READ,
            self::ARTICLE_UPDATE,
            self::ARTICLE_DELETE,
            self::ARTICLE_LIST
            ])) {
            return false;
        }

        // only vote on Article objects inside this voter
        if (!in_array($attribute, [
            self::ARTICLE_CREATE,
            self::ARTICLE_READ,
            self::ARTICLE_UPDATE,
            self::ARTICLE_DELETE,
            self::ARTICLE_LIST
            ]) && !($subject instanceof Paginator) && !($subject instanceof Article || $subject instanceof Section || $subject instanceof Paragraph)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $requester = $token->getUser();

        switch ($attribute) {
            case self::ARTICLE_CREATE:
                return $this->canCreateArticle($requester);
            case self::ARTICLE_READ:
                return $this->canReadArticle($requester, $subject);
            case self::ARTICLE_UPDATE:
                return $this->canUpdateArticle($requester, $subject);
            case self::ARTICLE_DELETE:
                return $this->canDeleteArticle($requester, $subject);
            case self::ARTICLE_LIST:
                return $this->canListArticle($requester);
            }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateArticle(UserInterface $requester)
    {
        // only registered users/apps can create articles
        if (!$requester instanceof UserInterface) {
            return false;
        }
        return $this->authManager->isAuthorized('article_create');
    }

    private function canReadArticle(UserInterface $requester, Article $article)
    {
        // only registered users/apps can read articles
        if (!$requester instanceof UserInterface) {
            return false;
        }
        return $this->authManager->isAuthorized('article_read', ['id'=>$article->getId()]);
    }

    private function canUpdateArticle(UserInterface $requester, Article $article)
    {
        // only registered users/apps can update articles
        if (!$requester instanceof UserInterface) {
            return false;
        }
        return $this->authManager->isAuthorized('article_update', ['id'=>$article->getId()]);
    }

    private function canDeleteArticle(UserInterface $requester, Article $article)
    {
        // only registered users/apps can delete articles
        if (!$requester instanceof UserInterface) {
            return false;
        }
        return $this->authManager->isAuthorized('article_delete', ['id'=>$article->getId()]);
    }

    private function canListArticle(UserInterface $requester)
    {
        // only registered users/apps can list articles
        if (!$requester instanceof UserInterface) {
            return false;
        }
        return $this->authManager->isAuthorized('article_list');
    }
}
