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

namespace App\Article\Admin\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Article\Entity\Article;
use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArticleVoter extends Voter
{
    public const ADMIN_ARTICLE_CREATE = 'admin_article_create';
    public const ADMIN_ARTICLE_READ = 'admin_article_read';
    public const ADMIN_ARTICLE_UPDATE = 'admin_article_update';
    public const ADMIN_ARTICLE_DELETE = 'admin_article_delete';
    public const ADMIN_ARTICLE_LIST = 'admin_article_list';
    public const ARTICLE_CREATE = 'article_create';
    public const ARTICLE_READ = 'article_read';
    public const ARTICLE_UPDATE = 'article_update';
    public const ARTICLE_DELETE = 'article_delete';
    public const ARTICLE_LIST = 'article_list';

    private $authManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject): bool
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ADMIN_ARTICLE_CREATE,
            self::ADMIN_ARTICLE_READ,
            self::ADMIN_ARTICLE_UPDATE,
            self::ADMIN_ARTICLE_DELETE,
            self::ADMIN_ARTICLE_LIST,
        ])) {
            return false;
        }
        // only vote on Article objects inside this voter
        if (!in_array($attribute, [
            self::ADMIN_ARTICLE_CREATE,
            self::ADMIN_ARTICLE_READ,
            self::ADMIN_ARTICLE_UPDATE,
            self::ADMIN_ARTICLE_DELETE,
            self::ADMIN_ARTICLE_LIST,
        ]) && !($subject instanceof Paginator) && !($subject instanceof Article)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        switch ($attribute) {
            case self::ADMIN_ARTICLE_CREATE:
                return $this->canCreateArticle();

            case self::ADMIN_ARTICLE_READ:
                return $this->canReadArticle($subject);

            case self::ADMIN_ARTICLE_UPDATE:
                return $this->canUpdateArticle($subject);

            case self::ADMIN_ARTICLE_DELETE:
                return $this->canDeleteArticle($subject);

            case self::ADMIN_ARTICLE_LIST:
                return $this->canListArticle();
            }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateArticle()
    {
        return $this->authManager->isAuthorized(self::ARTICLE_CREATE);
    }

    private function canReadArticle(Article $article)
    {
        return $this->authManager->isAuthorized(self::ARTICLE_READ, ['article' => $article]);
    }

    private function canUpdateArticle(Article $article)
    {
        return $this->authManager->isAuthorized(self::ARTICLE_UPDATE, ['article' => $article]);
    }

    private function canDeleteArticle(Article $article)
    {
        return $this->authManager->isAuthorized(self::ARTICLE_DELETE, ['article' => $article]);
    }

    private function canListArticle()
    {
        return $this->authManager->isAuthorized(self::ARTICLE_LIST);
    }
}
