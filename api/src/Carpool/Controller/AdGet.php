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

use App\Carpool\Entity\Ad;
use App\Carpool\Service\AdManager;
use App\TranslatorTrait;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Controller class for ad get.
 * We return the Ad with its results.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class AdGet
{
    use TranslatorTrait;
    
    private $adManager;
    
    public function __construct(RequestStack $requestStack, AdManager $adManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->adManager = $adManager;
    }

    /**
     * This method is invoked when a new ad is asked.
     *
     * @param Ad $data
     * @return Ad
     */
    public function __invoke(Ad $data): Ad
    {
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad Ad id is provided"));
        }
        $data = $this->adManager->getAd($this->request->get("id"), $this->request->query->get('filters'));
        return $data;
    }
}
