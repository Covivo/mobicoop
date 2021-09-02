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

namespace Mobicoop\Bundle\MobicoopBundle\Editorial\Controller;

use Mobicoop\Bundle\MobicoopBundle\Editorial\Entity\Editorial;
use Mobicoop\Bundle\MobicoopBundle\Editorial\Service\EditorialManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class EditorialController extends AbstractController
{
    private $editorialManager;

    public function __construct(EditorialManager $editorialManager)
    {
        $this->editorialManager = $editorialManager;
    }

    /**
     * Get the current Editorial
     */
    public function getEditorial()
    {
        return new JsonResponse($this->editorialManager->getEditorial());
    }
}
