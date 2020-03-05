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

namespace App\Rdex\Controller;

use App\TranslatorTrait;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use App\Rdex\Service\RdexManager;
use App\Rdex\Entity\RdexError;
use App\Rdex\Entity\RdexConnection;

/**
 * Controller class for Rdex Connections
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ConnectionController
{
    use TranslatorTrait;
    private $rdexManager;
    protected $request;
    
    public function __construct(RequestStack $requestStack, RdexManager $rdexManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->rdexManager = $rdexManager;
    }
    
    /**
     * This method is invoked when a Journey collection is requested.
     *
     * @param RdexConnection $data
     * @return Response
     */
    public function __invoke(RdexConnection $data)
    {
        echo "ok";
        die;
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans("bad RdexJourney id is provided"));
        }
        $response = new Response();

//        $this->rdexManager->validate($this->request);


        return $response;
    }
}
