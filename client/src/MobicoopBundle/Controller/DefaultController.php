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
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends AbstractController
{

    /**
     * HomePage
     */
    public function index()
    {
        return $this->render(
            '@Mobicoop/default/index.html.twig',
            [
                'baseUri' => $_ENV['API_URI']
            ]
        );
    }

    /**
     * HomePage, coming from an delete account
     */
    public function indexLogout()
    {
        return $this->render(
            '@Mobicoop/default/index.html.twig',
            [
                'baseUri' => $_ENV['API_URI'],
                'logout' => 1,
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

    /**
     * Show the platform widget.
     */
    public function platformWidget(UserManager $userManager)
    {
        // retreive logged user
        $user = $userManager->getLoggedUser();

        return $this->render('@Mobicoop/platform-widget.html.twig', [
            'user' => $user,
            'searchRoute' => 'covoiturage/recherche'
        ]);
    }

    /**
     * Show the platform widget page to get the widget code.
     */
    public function getPlatformWidget()
    {
        //$this->denyAccessUnlessGranted('show', $community);
        return $this->render('@Mobicoop/platform-get-widget.html.twig');
    }

    /**
     * Show a default page when the request page no longer exists
     */
    public function getPageNoLongerExists()
    {
        return $this->render('@Mobicoop/page-no-longer-exists.html.twig');
    }

    /**
     * Store language selected by user in session
     *
     * @param Request $request
     * @return void
     */
    public function setSessionLanguage(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
        }
        return new JsonResponse();
    }
}
