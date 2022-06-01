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

namespace App\Article\Admin\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Article\Admin\Service\ArticleManager;
use App\Article\Entity\Article;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Data persister for Article in administration context.
 */
final class ArticleDataPersister implements ContextAwareDataPersisterInterface
{
    private $request;
    private $articleManager;

    public function __construct(RequestStack $requestStack, ArticleManager $articleManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->articleManager = $articleManager;
    }

    public function supports($data, array $context = []): bool
    {
        if ($data instanceof Article) {
            switch ($context) {
                case isset($context['collection_operation_name']):
                    return 'ADMIN_post' == $context['collection_operation_name'];

                    break;

                case isset($context['item_operation_name']):
                    return 'ADMIN_patch' == $context['item_operation_name'] || 'ADMIN_delete' == $context['item_operation_name'];

                    break;

                default:
                    return false;

                    break;
            }
        } else {
            return false;
        }
    }

    public function persist($data, array $context = [])
    {
        if (isset($context['collection_operation_name']) && 'ADMIN_post' == $context['collection_operation_name']) {
            $data = $this->articleManager->addArticle($data);
        } elseif (isset($context['item_operation_name']) && 'ADMIN_patch' == $context['item_operation_name']) {
            // for a patch operation, we update only some fields, we pass them to the method for further checkings
            $data = $this->articleManager->patchArticle($data, json_decode($this->request->getContent(), true));
        }

        return $data;
    }

    public function remove($data, array $context = [])
    {
        if (isset($context['item_operation_name']) && 'ADMIN_delete' == $context['item_operation_name']) {
            return $this->articleManager->deleteArticle($data);
        }
    }
}
