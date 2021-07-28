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

namespace Mobicoop\Bundle\MobicoopBundle\Stats\Controller;

use Mobicoop\Bundle\MobicoopBundle\Stats\Service\StatsManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller class for stats actions.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 *
 */
class StatsController extends AbstractController
{
    /**
    * Get home stats indicators
     * AJAX
     * @param Request $request
     * @param StatsManager $statsManager
     */
    public function getHomeStatsIndicators(Request $request, StatsManager $statsManager)
    {
        $homeStats = null;
        if ($request->isMethod('POST')) {
            if ($homeStats = $statsManager->getHomeStatsIndicators()) {
                return new JsonResponse($homeStats);
            }
        }
        return new JsonResponse($homeStats);
    }
}
