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

namespace App\Article\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Article\Ressource\Article;
use App\Article\Service\ArticleManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Collection data provider for Articles.
 *
 * @author Céline Jacquet <celine.jacquet@mobicoop.org>
 */
final class ArticlesCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $request;
    private $articleManager;

    public function __construct(RequestStack $requestStack, ArticleManager $articleManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->articleManager = $articleManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Article::class === $resourceClass && 'get' === $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        $context = null;

        if (null !== $this->request->get('context')) {
            $context = $this->request->get('context');
        }

        return $this->articleManager->getArticles($context);
    }
}
