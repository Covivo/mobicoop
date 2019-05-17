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

namespace App\Geography\Controller;

use Symfony\Component\HttpFoundation\RequestStack;
use App\Geography\Service\GeoSearcher;

/**
 * GeoSearchController.php
 * Controller that requests a provider list
 * @author Sofiane Belaribi <sofiane.belaribi@mobicoop.org>
 * Date: 16/11/2018
 * Time: 9:25
 *
 */
class GeoSearchController
{
    private $geoSearcher;
    protected $request;

    /**
     * GeoSearchController constructor.
     * @param RequestStack $requestStack
     * @param GeoSearcher $geoSearcher
     */
    public function __construct(RequestStack $requestStack, GeoSearcher $geoSearcher)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->geoSearcher = $geoSearcher;
    }

    /**
     * This method is invoked when autocomplete function is called.
     * @param array $data
     * @return array
     */
    public function __invoke(array $data): array
    {
        return $this->geoSearcher->geoCode($this->request->get("input"));
    }
}
