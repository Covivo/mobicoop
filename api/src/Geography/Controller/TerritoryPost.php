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

namespace App\Geography\Controller;

use App\Geography\Service\TerritoryManager;
use App\Geography\Entity\Territory;
use App\TranslatorTrait;

/**
 * Controller class for territory post.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class TerritoryPost
{
    use TranslatorTrait;
    private $territoryManager;

    public function __construct(TerritoryManager $territoryManager)
    {
        $this->territoryManager = $territoryManager;
    }

    /**
     * This method is invoked when a new territory is posted.
     * It returns the new territory created.
     *
     * @param Territory $data
     * @return Territory
     */
    public function __invoke(Territory $data): Territory
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad Territory id is provided"));
        }
        $data = $this->territoryManager->createTerritory($data);
        return $data;
    }
}
