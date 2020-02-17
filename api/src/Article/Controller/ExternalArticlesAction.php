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

namespace App\Article\Controller;

use App\Article\Entity\Article;
use App\Article\Service\ArticleManager;
use App\TranslatorTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller class for getting the external articles
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ExternalArticlesAction
{
    use TranslatorTrait;
    
    private $articleManager;
    private $request;

    public function __construct(ArticleManager $articleManager, RequestStack $request)
    {
        $this->articleManager = $articleManager;
        $this->request = $request->getCurrentRequest();
    }

    /**
     * @return Array
     */
    public function __invoke(): array
    {
        $nbArticles = Article::NB_EXTERNAL_ARTICLES_DEFAULT;
        if ($this->request->get("nbArticles")!=="" && is_numeric($this->request->get("nbArticles"))) {
            $nbArticles = $this->request->get("nbArticles");
        } else {
            $nbArticles = Article::NB_EXTERNAL_ARTICLES_DEFAULT;
        }
        
        return $this->articleManager->getLastExternalArticles($nbArticles);
    }
}
