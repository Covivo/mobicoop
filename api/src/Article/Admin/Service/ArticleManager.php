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

use App\Article\Entity\Article;
use App\Article\Entity\Paragraph;
use App\Article\Entity\Section;
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
    public function getArticle(int $id): Article
    {
        if (!$article = $this->articleRepository->find($id)) {
            return new ArticleException('Article not found');
        }
        return $article;
    }

    /**
     * Add an article.
     *
     * @param Article   $article    The article to add
     * @return Article  The article updated
     */
    public function addArticle(Article $article): Article
    {
        // treat sections
        if (!is_null($article->getAsections())) {
            foreach ($article->getAsections() as $asection) {
                $section = new Section();
                if (isset($asection["position"])) {
                    $section->setPosition($asection["position"]);
                }
                if (isset($asection["status"])) {
                    $section->setStatus($asection["status"]);
                }
                if (isset($asection["title"])) {
                    $section->setTitle($asection["title"]);
                }
                if (isset($asection["subTitle"])) {
                    $section->setSubTitle($asection["subTitle"]);
                }
                // treat paragraphs
                if (isset($asection["paragraphs"])) {
                    foreach ($asection["paragraphs"] as $aparagraph) {
                        $paragraph = new Paragraph();
                        if (isset($aparagraph["position"])) {
                            $paragraph->setPosition($aparagraph["position"]);
                        }
                        if (isset($aparagraph["status"])) {
                            $paragraph->setStatus($aparagraph["status"]);
                        }
                        if (isset($aparagraph["text"])) {
                            $paragraph->setText($aparagraph["text"]);
                        }
                        if (!is_null($paragraph->getText())) {
                            // save only non null paragraphs
                            $section->addParagraph($paragraph);
                        }
                    }
                }
                $article->addSection($section);
            }
        }

        // persist the article
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        // reorder sections and paragraphs (order may have changed during the persists)
        $sections = $article->getSections();
        $article->removeSections();
        foreach ($sections as $section) {
            $paragraphs = $section->getParagraphs();
            $section->removeParagraphs();
            usort($paragraphs, [$this,"comparePosition"]);
            foreach ($paragraphs as $paragraph) {
                $section->addParagraph($paragraph);
            }
        }
        usort($sections, [$this,"comparePosition"]);
        foreach ($sections as $section) {
            $article->addSection($section);
        }

        // return the article
        return $article;
    }

    /**
     * Patch an article.
     *
     * @param Article   $article    The article to update
     * @param array     $fields     The updated fields
     * @return Article  The article updated
     */
    public function patchArticle(Article $article, array $fields): Article
    {
        // keep original sections to track deleted ones
        $originalSections = [];
        foreach ($article->getSections() as $section) {
            $originalSections[] = $section;
        }
        // treat sections
        if (in_array('asections', array_keys($fields))) {
            $treatedSections = [];  // keep ids of patched sections
            foreach ($fields['asections'] as $asection) {
                $section = null;
                // keep original paragraphs to track deleted ones
                $originalParagraphs = [];
                if (isset($asection["id"])) {
                    $treatedSections[] = $asection["id"];
                    $section = $this->sectionRepository->find($asection['id']);
                    foreach ($section->getParagraphs() as $paragraph) {
                        $originalParagraphs[] = $paragraph;
                    }
                }
                if (is_null($section)) {
                    $section = new Section();
                }
                if (isset($asection["position"])) {
                    $section->setPosition($asection["position"]);
                }
                if (isset($asection["status"])) {
                    $section->setStatus($asection["status"]);
                }
                if (isset($asection["title"])) {
                    $section->setTitle($asection["title"]);
                }
                if (isset($asection["subTitle"])) {
                    $section->setSubTitle($asection["subTitle"]);
                }
                // treat paragraphs
                if (isset($asection["paragraphs"])) {
                    $treatedParagraphs = []; // keep ids of patched paragraphs
                    foreach ($asection["paragraphs"] as $aparagraph) {
                        $paragraph = null;
                        if (isset($aparagraph["id"])) {
                            $treatedParagraphs[] = $aparagraph["id"];
                            $paragraph = $this->paragraphRepository->find($aparagraph['id']);
                        }
                        if (is_null($paragraph)) {
                            $paragraph = new Paragraph();
                        }
                        if (isset($aparagraph["position"])) {
                            $paragraph->setPosition($aparagraph["position"]);
                        }
                        if (isset($aparagraph["status"])) {
                            $paragraph->setStatus($aparagraph["status"]);
                        }
                        if (isset($aparagraph["text"])) {
                            $paragraph->setText($aparagraph["text"]);
                        }
                        if (!is_null($paragraph->getText())) {
                            // save only non null paragraphs
                            $section->addParagraph($paragraph);
                        }
                    }
                    // removed paragraphs
                    foreach ($originalParagraphs as $paragraph) {
                        if (!in_array($paragraph->getId(), $treatedParagraphs)) {
                            $section->removeParagraph($paragraph);
                        }
                    }
                }
                $article->addSection($section);
            }
            // removed sections
            foreach ($originalSections as $section) {
                if (!in_array($section->getId(), $treatedSections)) {
                    $article->removeSection($section);
                }
            }
        }

        // persist the article
        $this->entityManager->persist($article);
        $this->entityManager->flush();

        // reorder sections and paragraphs (order may have changed during the persists)
        $sections = $article->getSections();
        $article->removeSections();
        foreach ($sections as $section) {
            $paragraphs = $section->getParagraphs();
            $section->removeParagraphs();
            usort($paragraphs, [$this,"comparePosition"]);
            foreach ($paragraphs as $paragraph) {
                $section->addParagraph($paragraph);
            }
        }
        usort($sections, [$this,"comparePosition"]);
        foreach ($sections as $section) {
            $article->addSection($section);
        }

        // return the article
        return $article;
    }

    /**
     * Delete an article
     *
     * @param Article   $article    The article to delete
     * @return void
     */
    public function deleteArticle(Article $article): void
    {
        $this->entityManager->remove($article);
        $this->entityManager->flush();
    }

    private function comparePosition($a, $b)
    {
        return strcmp($a->getPosition(), $b->getPosition());
    }
}
