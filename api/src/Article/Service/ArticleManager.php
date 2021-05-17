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
use App\Article\Entity\Article as ArticleEntity;
use App\Article\Entity\Iframe;
use App\Article\Entity\RssElement;
use App\Article\Repository\SectionRepository;
use App\Article\Repository\ParagraphRepository;
use App\Article\Repository\ArticleRepository;
use App\Article\Ressource\Article;
use DOMDocument;

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

    private $articleFeed;
    private $articleFeedNumber;
    private $articleIframeMaxWidth;
    private $articleIframeMaxHeight;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        SectionRepository $sectionRepository,
        ParagraphRepository $paragraphRepository,
        ArticleRepository $articleRepository,
        string $articleFeed,
        int $articleFeedNumber,
        int $articleIframeMaxWidth,
        int $articleIframeMaxHeight
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->sectionRepository = $sectionRepository;
        $this->paragraphRepository = $paragraphRepository;
        $this->articleRepository = $articleRepository;
        $this->articleFeed = $articleFeed;
        $this->articleFeedNumber = $articleFeedNumber;
        $this->articleIframeMaxWidth = $articleIframeMaxWidth;
        $this->articleIframeMaxHeight = $articleIframeMaxHeight;
    }

    /**
     * Change the position of a section, and the position of the associated section.
     *
     * @param Section $section  The section
     * @param string $direction The direction (up/down)
     * @return Section  The section
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
     * @param Paragraph $paragraph  The paragraph
     * @param string $direction     The direction (up/down)
     * @return Paragraph    The paragraph
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
    public function getLastExternalArticles(int $nbArticles=ArticleEntity::NB_EXTERNAL_ARTICLES_DEFAULT)
    {
        return $this->articleRepository->findLastExternal($nbArticles);
    }

    /**
     * Make an Article ressource from Article (Page) entity
     *
     * @param ArticleEntity $articleEntity
     * @return Article
     */
    private function makeArticleFromEntity(ArticleEntity $articleEntity): Article
    {
        $article = new Article($articleEntity->getId());

        $article->setTitle($articleEntity->getTitle());

        return $article;
    }


    /**
     * Make an article ressource from an RSS element
     * @param RssElement $rssElement   Rss element to convert
     *
     * @return Article
     */
    private function makeArticleFromRss(RssElement $rssElement): Article
    {
        $article = new Article();

        $article->setTitle($rssElement->getTitle());
        $article->setDescription($rssElement->getDescription());
        $article->setImage($rssElement->getImage());
        $article->setPubDate($rssElement->getPubDate());
        
        if (!is_null($rssElement->getIframe())) {
            $rssIframe = $rssElement->getIframe();
            $article->setIFrame('<iframe src="'.$rssIframe->getSrc().'" allowfullscreen title="'.$rssIframe->getTitle().'" width="'.$rssIframe->getWidth().'" height="'.$rssIframe->getHeight().'" allow="'.$rssIframe->getAllow().'" frameborder="0"></iframe>');
        }

        return $article;
    }

    /**
     * Get Rss elements from all feeds (in .env ARTICLE_FEEDS)
     *
     * @return RssElement[]
     */
    private function getRssFeeds(): array
    {
        $rssElements = [];

        $articleFeed = $this->articleFeed;
        $articleFeedNumber = $this->articleFeedNumber;

        // transform xml to object
        $feedResult = simplexml_load_file($articleFeed, 'SimpleXMLElement', LIBXML_NOCDATA);


        $counter=-1;

        foreach ($feedResult->channel->item as $item) {
            $rssElement = new RssElement();

            $rssElement->setTitle((string) $item->title);
            $rssElement->setPubDate(date('d M Y', strtotime($item->pubDate)));

            $description = (string) $item->description;

            $start = strpos($description, '<p>');
            $end = strpos($description, '</p>', $start);

            if (strlen($description)>=255) {
                $description = substr($description, $start, $end-$start+234)." ...";
            } else {
                $description = substr($description, $start, $end-$start+255)." ...";
            }

            $description = strip_tags($description);


            $rssElement->setDescription(html_entity_decode($description));

            $dom = new DOMDocument();
            libxml_use_internal_errors(true);

            $content = $item->children('content', true);

            $html_string = $content->encoded;
            $dom->loadHTML($html_string);
            libxml_clear_errors();

            if ($dom->getElementsByTagName('img')->length > 0) {
                $image = $dom->getElementsByTagName('img')->item(0)->getAttribute('src');
                $rssElement->setImage($image);
            }

            // If there is an iframe (usually a video) we parse it. We only take the first one.
            if ($dom->getElementsByTagName('iframe')->length > 0) {
                $iframe = new Iframe();
                $iframe->setSrc($dom->getElementsByTagName('iframe')->item(0)->getAttribute('src'));
                $iframe->setTitle($dom->getElementsByTagName('iframe')->item(0)->getAttribute('title'));
                
                if ((int)$dom->getElementsByTagName('iframe')->item(0)->getAttribute('width') > $this->articleIframeMaxWidth) {
                    $iframe->setWidth($this->articleIframeMaxWidth);
                } else {
                    $iframe->setWidth($dom->getElementsByTagName('iframe')->item(0)->getAttribute('width'));
                }
                
                if ((int)$dom->getElementsByTagName('iframe')->item(0)->getAttribute('height') > $this->articleIframeMaxHeight) {
                    $iframe->setHeight($this->articleIframeMaxHeight);
                } else {
                    $iframe->setHeight($dom->getElementsByTagName('iframe')->item(0)->getAttribute('height'));
                }

                $iframe->setAllow($dom->getElementsByTagName('iframe')->item(0)->getAttribute('allow'));
                
                $rssElement->setIframe($iframe);
            }

            $counter++;
            if ($counter == $articleFeedNumber) {
                break;
            }
            
            $rssElements[]=$rssElement;
        }
        return $rssElements;
    }
    
    /**
     * Get a collection of Article
     *
     * @param string $context   (optionnal) : Context to select specific articles
     * @return Article[]
     */
    public function getArticles(string $context=null): array
    {
        $articles = [];
        
        // Get the articles in database
        if (is_null($context) || $context==Article::CONTEXT_INTERNAL) {
            $pages = $this->articleRepository->findAll();

            foreach ($pages as $page) {
                $articles[] = $this->makeArticleFromEntity($page);
            }
        }

        // Get the RSS articles
        if (is_null($context) || $context==Article::CONTEXT_HOME) {
            $rssItems = $this->getRssFeeds();

            foreach ($rssItems as $rssItem) {
                $articles[] = $this->makeArticleFromRss($rssItem);
            }
        }

        return $articles;
    }
}
