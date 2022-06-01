<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace App\Carpool\Controller;

use App\Carpool\Service\AskManager;
use App\Carpool\Entity\Ask;
use App\TranslatorTrait;

/**
 * Controller class for ask post.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class AskPut
{
    use TranslatorTrait;

    private $askManager;

    public function __construct(AskManager $askManager)
    {
        $this->askManager = $askManager;
    }

    /**
     * This method is invoked when a new ask is posted.
     *
     * @param Ask $data
     * @return Ask
     */
    public function __invoke(Ask $data): Ask
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad Ask id is provided"));
        }
        $data = $this->askManager->updateAsk($data);
        return $data;
    }
}
