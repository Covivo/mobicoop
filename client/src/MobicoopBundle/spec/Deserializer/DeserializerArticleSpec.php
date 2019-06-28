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

namespace Mobicoop\Bundle\MobicoopBundle\Spec\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\Deserializer;
use Mobicoop\Bundle\MobicoopBundle\Article\Entity\Article;

/**
 * DeserializerArticleSpec.php
 * Tests for Deserializer - Article
 * @author Sylvain briat <sylvain.briat@mobicoop.org>
 * Date: 24/06/2019
 * Time: 14:00
 *
 */

describe('deserializeArticle', function () {
    it('deserialize Article should return an Article object', function () {
        $jsonArticle = <<<JSON
{
  "id": 0,
  "title": "string",
  "status": 0,
  "sections": [
    {
      "id": 0,
      "title": "string",
      "subTitle": "string",
      "position": 0,
      "status": 0,
      "paragraphs": [
        {
          "id": 0,
          "text": "string",
          "position": 0,
          "status": 0
        }
      ]
    }
  ]
}
JSON;

        $deserializer = new Deserializer();
        $article = $deserializer->deserialize(Article::class, json_decode($jsonArticle, true));
        expect($article)->toBeAnInstanceOf(Article::class);
    });
});
