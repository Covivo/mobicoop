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

use App\Carpool\Service\AdManager;
use App\Carpool\Entity\Ad;
use App\TranslatorTrait;

/**
 * Controller class for ask post.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class AdPut
{
    use TranslatorTrait;
    
    private $adManager;
    
    public function __construct(AdManager $adManager)
    {
        $this->adManager = $adManager;
    }

    /**
     * This method is invoked when a new ask is posted.
     *
     * @param Ad $data
     * @return Ad
     */
    public function __invoke(Ad $data): Ad
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad Ad id is provided"));
        }
        $data = $this->adManager->updateAd($data);
        return $data;
    }
}
