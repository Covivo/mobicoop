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
 */

namespace App\Article\Security;

use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Article\Entity\Article;
use App\Article\Entity\Paragraph;
use App\Article\Entity\Section;
use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ArticleVoter extends Voter
{
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
            self::ARTICLE_CREATE,
            self::ARTICLE_READ,
            self::ARTICLE_UPDATE,
            self::ARTICLE_DELETE,
            self::ARTICLE_LIST,
        ])) {
            return false;
        }

        // only vote on Article objects inside this voter
        if (!in_array($attribute, [
            self::ARTICLE_CREATE,
            self::ARTICLE_READ,
            self::ARTICLE_UPDATE,
            self::ARTICLE_DELETE,
            self::ARTICLE_LIST,
        ]) && !($subject instanceof Paginator) && !($subject instanceof Article || $subject instanceof Section || $subject instanceof Paragraph)) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token): bool
    {
        if ($subject instanceof Article) {
            $article = $subject;
        } elseif ($subject instanceof Section) {
            $article = $subject->getArticle();
        } elseif ($subject instanceof Paragraph) {
            $article = $subject->getSection()->getArticle();
        }

        switch ($attribute) {
            case self::ARTICLE_CREATE:
                return $this->canCreateArticle();

            case self::ARTICLE_READ:
                return $this->canReadArticle($article);

            case self::ARTICLE_UPDATE:
                return $this->canUpdateArticle($article);

            case self::ARTICLE_DELETE:
                return $this->canDeleteArticle($article);

            case self::ARTICLE_LIST:
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
