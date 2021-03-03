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

namespace App\Article\Admin\Service;

use App\Article\Exception\ArticleException;
use Doctrine\ORM\EntityManagerInterface;
use App\Article\Repository\SectionRepository;
use App\Article\Repository\ParagraphRepository;
use App\Article\Repository\ArticleRepository;

/**
 * Article manager service in administration context.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class ArticleManager
{
    
    private $entityManager;
    private $sectionRepository;
    private $paragraphRepository;
    private $articleRepository;

    public function __construct(EntityManagerInterface $entityManager, SectionRepository $sectionRepository, ParagraphRepository $paragraphRepository, ArticleRepository $articleRepository)
    {
        $this->entityManager = $entityManager;
        $this->sectionRepository = $sectionRepository;
        $this->paragraphRepository = $paragraphRepository;
        $this->articleRepository = $articleRepository;
    }

    /**
     * Get an article with its sections and paragraphes
     *
     * @param integer $id   The article id
     * @return Article
     */
    public function getArticle(int $id)
    {
        if (!$article = $this->articleRepository->find($id)) {
            return new ArticleException('Article not found');
        }
        return $article;
    }
}