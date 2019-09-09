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

namespace Mobicoop\Bundle\MobicoopBundle\Controller;

use Mobicoop\Bundle\MobicoopBundle\JsonLD\Entity\Hydra;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use App\User\Service\UserManager;

class DefaultController extends AbstractController
{
    /**
     * HomePage
     * @Route("/", name="home")
     *
     */
    public function index()
    {
        return $this->render(
            '@Mobicoop/default/index.html.twig',
            [
                'baseUri' => $_ENV['API_URI'],
                'searchRoute' => 'covoiturage/recherche',
                'metaDescription' => 'Homepage of Mobicoop'
            ]
        );
    }

    /**
     * HomePage
     * @Route("/testMap", name="testMap")
     *
     */
    public function testMap()
    {
        return $this->render(
            '@Mobicoop/default/testmap.html.twig',
            [
                'baseUri' => $_ENV['API_URI'],
                'searchRoute' => 'covoiturage/recherche',
                'metaDescription' => 'Homepage of Mobicoop'
            ]
        );
    }

    /**
     * Error Page.
     * @Route("/provider/errors", name="api_hydra_errors")
     *
     */
    public function showErrorsAction()
    {
        $session= $this->get('session');
        $hydra = $session->get('hydra');
        if ($hydra instanceof Hydra) {
            return $this->render('@Mobicoop/hydra/error.html.twig', ['hydra'=> $hydra]);
        }
        return null;
    }
}
