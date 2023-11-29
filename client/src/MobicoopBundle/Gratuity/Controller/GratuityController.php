<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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
 */

namespace Mobicoop\Bundle\MobicoopBundle\Gratuity\Controller;

use Mobicoop\Bundle\MobicoopBundle\Gratuity\Service\GratuityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class GratuityController extends AbstractController
{
    private $_gratuityManager;

    public function __construct(GratuityManager $gratuityManager)
    {
        $this->_gratuityManager = $gratuityManager;
    }

    public function tagGratuityCampaignAsNotified(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            return new JsonResponse($this->_gratuityManager->tagGratuityCampaignAsNotified($data['id']));
        }

        return new JsonResponse();
    }
}
