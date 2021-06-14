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

namespace App\Article\EventListener;

use App\Article\Entity\Article;
use App\Event\Service\EventManager;
use App\I18n\Service\LanguageManager;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Article Event listener
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ArticleLoadListener
{
    private $eventManager;
    private $defaultLanguage;
    private $languageManager;

    public function __construct(EventManager $eventManager, LanguageManager $languageManager, int $defaultLanguage)
    {
        $this->eventManager = $eventManager;
        $this->defaultLanguage = $defaultLanguage;
        $this->languageManager = $languageManager;
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $article = $args->getEntity();

        if ($article instanceof Article) {
            $this->languageManager->getTranslation($this->defaultLanguage, "Article", $article->getId(), $article);
        }
    }
}
