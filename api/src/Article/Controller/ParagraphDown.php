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

namespace App\Article\Controller;

use App\Article\Service\ArticleManager;
use App\Article\Entity\Paragraph;
use App\TranslatorTrait;

/**
 * Controller class for paragraph down position change.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class ParagraphDown
{
    use TranslatorTrait;

    private $articleManager;

    public function __construct(ArticleManager $articleManager)
    {
        $this->articleManager = $articleManager;
    }

    /**
     * This method is invoked when a paragraph down position change is asked.
     * It returns the edited paragraph.
     *
     * @param Paragraph $data
     * @return Paragraph
     */
    public function __invoke(Paragraph $data): Paragraph
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad Paragraph id is provided"));
        }
        return $this->articleManager->changeParagraphPosition($data, $this->articleManager::DIRECTION_DOWN);
    }
}
