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

namespace Mobicoop\Bundle\MobicoopBundle\Gamification\Controller;

use Mobicoop\Bundle\MobicoopBundle\Gamification\Service\GamificationManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class GamificationController extends AbstractController
{
    private $gamificationManager;

    public function __construct(GamificationManager $gamificationManager)
    {
        $this->gamificationManager = $gamificationManager;
    }
    
    /**
    * Tags all given rewardSteps as notified
    */
    public function tagRewardStepsAsNotified(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $this->gamificationManager->tagRewardStepsAsNotified($data);
            return new JsonResponse($data);
        }
        return new JsonResponse();
    }
}
