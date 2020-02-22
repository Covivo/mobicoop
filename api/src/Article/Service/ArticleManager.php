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

namespace App\Article\Service;

use App\Article\Entity\Section;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use App\Article\Entity\Paragraph;
use App\Article\Entity\Article;
use App\Article\Repository\SectionRepository;
use App\Article\Repository\ParagraphRepository;
use App\Article\Repository\ArticleRepository;

/**
 * Article manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ArticleManager
{
    const DIRECTION_UP = "up";
    const DIRECTION_DOWN = "down";
    
    /**
     * @var EntityManagerInterface  $entityManager
     */
    private $entityManager;
    /**
     * @var LoggerInterface $logger
     */
    private $logger;
    /**
     * @var SectionRepository  $sectionRepository
     */
    private $sectionRepository;
    /**
     * @var ParagraphRepository $paragraphRepository
     */
    private $paragraphRepository;
    /**
     * @var ArticleRepository $articleRepository
     */
    private $articleRepository;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, SectionRepository $sectionRepository, ParagraphRepository $paragraphRepository, ArticleRepository $articleRepository)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->sectionRepository = $sectionRepository;
        $this->paragraphRepository = $paragraphRepository;
        $this->articleRepository = $articleRepository;
    }

    /**
     * Change the position of a section, and the position of the associated section.
     *
     * @param Section $section
     * @param string $direction
     * @return Section
     */
    public function changeSectionPosition(Section $section, string $direction): Section
    {
        switch ($direction) {
            case self::DIRECTION_UP:
                if ($previousSection = $this->sectionRepository->findPrevious($section)) {
                    $section->setPosition($section->getPosition()-1);
                    $previousSection->setPosition($previousSection->getPosition()+1);
                    $this->entityManager->persist($section);
                    $this->entityManager->persist($previousSection);
                    $this->entityManager->flush();
                }
                break;
            case self::DIRECTION_DOWN:
            default:
                if ($nextSection = $this->sectionRepository->findNext($section)) {
                    $section->setPosition($section->getPosition()+1);
                    $nextSection->setPosition($nextSection->getPosition()-1);
                    $this->entityManager->persist($section);
                    $this->entityManager->persist($nextSection);
                    $this->entityManager->flush();
                }
                break;
        }
        return $section;
    }

    /**
     * Change the position of a paragraph, and the position of the associated paragraph.
     *
     * @param Paragraph $paragraph
     * @param string $direction
     * @return Paragraph
     */
    public function changeParagraphPosition(Paragraph $paragraph, string $direction): Paragraph
    {
        switch ($direction) {
            case self::DIRECTION_UP:
                if ($previousParagraph = $this->paragraphRepository->findPrevious($paragraph)) {
                    $paragraph->setPosition($paragraph->getPosition()-1);
                    $previousParagraph->setPosition($previousParagraph->getPosition()+1);
                    $this->entityManager->persist($paragraph);
                    $this->entityManager->persist($previousParagraph);
                    $this->entityManager->flush();
                }
                break;
            case self::DIRECTION_DOWN:
            default:
                if ($nextParagraph = $this->paragraphRepository->findNext($paragraph)) {
                    $paragraph->setPosition($paragraph->getPosition()+1);
                    $nextParagraph->setPosition($nextParagraph->getPosition()-1);
                    $this->entityManager->persist($paragraph);
                    $this->entityManager->persist($nextParagraph);
                    $this->entityManager->flush();
                }
                break;
        }
        return $paragraph;
    }

    /**
     * Get the external articles
     */
    public function getLastExternalArticles(int $nbArticles=Article::NB_EXTERNAL_ARTICLES_DEFAULT)
    {
        return $this->articleRepository->findLastExternal($nbArticles);
    }
}
