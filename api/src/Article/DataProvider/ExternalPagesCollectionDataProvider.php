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

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Article\Entity\Article;
use App\Article\Service\ArticleManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Get the External Articles.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class ExternalPagesCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    public function __construct(ArticleManager $articleManager, RequestStack $request)
    {
        $this->articleManager = $articleManager;
        $this->request = $request->getCurrentRequest();
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Article::class === $resourceClass && 'externalArticles' == $operationName;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        $nbArticles = Article::NB_EXTERNAL_ARTICLES_DEFAULT;
        if ('' !== $this->request->get('nbArticles') && is_numeric($this->request->get('nbArticles'))) {
            $nbArticles = $this->request->get('nbArticles');
        } else {
            $nbArticles = Article::NB_EXTERNAL_ARTICLES_DEFAULT;
        }

        return $this->articleManager->getLastExternalArticles($nbArticles);
    }
}
