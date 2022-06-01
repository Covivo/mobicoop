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

namespace App\Article\Repository;

use App\Article\Entity\Article;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

class ArticleRepository
{
    /**
     * @var EntityRepository
     */
    private $repository;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->repository = $entityManager->getRepository(Article::class);
    }

    /**
     * Find All the articles.
     *
     * @return null|Article[]
     */
    public function findAll(): ?array
    {
        return $this->repository->findAll();
    }

    public function find(int $id): ?Article
    {
        return $this->repository->find($id);
    }

    /**
     * Find the external articles.
     */
    public function findLastExternal(int $nbArticles = Article::NB_EXTERNAL_ARTICLES_DEFAULT)
    {
        $query = $this->repository->createQueryBuilder('a')
            ->where('a.iFrame is not null')
            ->orderBy('a.createdDate', 'DESC')
            ->setMaxResults($nbArticles)
            ->getQuery()
        ;

        return $query->getResult();
    }
}
