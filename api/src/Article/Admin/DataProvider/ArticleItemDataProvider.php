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

namespace App\Article\Admin\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Article\Admin\Service\ArticleManager;
use App\Article\Entity\Article;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Item data provider for Article in administration context
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 *
 */
final class ArticleItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
{
    protected $request;
    private $articleManager;
    private $security;

    public function __construct(RequestStack $requestStack, ArticleManager $articleManager, Security $security)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->articleManager = $articleManager;

        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Article::class === $resourceClass && $operationName === "ADMIN_get";
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?Article
    {
        return $this->articleManager->getArticle($id);
    }
}
