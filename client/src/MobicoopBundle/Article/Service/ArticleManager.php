<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\Article\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Article\Entity\Article;

/**
 * Article management service.
 */
class ArticleManager
{
    private $dataProvider;

    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Article::class);
    }

    /**
     * Get one article
     *
     * @return Article|null
     */
    public function getArticle($id)
    {
        $response = $this->dataProvider->getItem($id);
        return $response->getValue();
    }

    /**
     * Get the last external articles
     * @param int $nbArticles   Number of articles to return
     * @return array
     */
    public function getLastExternalArticles($nbArticles)
    {
        $this->dataProvider->setFormat(DataProvider::RETURN_JSON);
        $response = $this->dataProvider->getSpecialCollection("external", ['nbArticles'=>$nbArticles]);
        return $response->getValue();
    }
}
