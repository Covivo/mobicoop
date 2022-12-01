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
 */

namespace Mobicoop\Bundle\MobicoopBundle\Controller;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\JsonLD\Entity\Hydra;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\CeeSubscription;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    private $searchComponentHorizontal;
    private $dataProvider;

    public function __construct(DataProvider $dataProvider, bool $searchComponentHorizontal)
    {
        $this->dataProvider = $dataProvider;
        $this->searchComponentHorizontal = $searchComponentHorizontal;
    }

    /**
     * HomePage.
     */
    public function index(Request $request)
    {
        $params = [
            'baseUri' => $_ENV['API_URI'],
            'searchComponentHorizontal' => $this->searchComponentHorizontal,
        ];

        if (!is_null($request->get('fromMobConnectSso')) && true === boolval($request->get('fromMobConnectSso'))) {
            $this->dataProvider->setClass(CeeSubscription::class, CeeSubscription::RESOURCE_NAME);
            $response = $this->dataProvider->getCollection();

            if (200 === $response->getCode()) {
                $subscriptions = $response->getValue()->getMember()[0];
                $params['mobConnectSubscriptions'] = !is_null($subscriptions->getShortDistanceSubscriptions()) && !is_null($subscriptions->getLongDistanceSubscriptions());
            }
        }

        return $this->render(
            '@Mobicoop/default/index.html.twig',
            $params
        );
    }

    /**
     * HomePage, coming from an delete account.
     */
    public function indexLogout()
    {
        return $this->render(
            '@Mobicoop/default/index.html.twig',
            [
                'baseUri' => $_ENV['API_URI'],
                'logout' => 1,
                'searchComponentHorizontal' => $this->searchComponentHorizontal,
            ]
        );
    }

    /**
     * Error Page.
     *
     * @Route("/provider/errors", name="api_hydra_errors")
     */
    public function showErrorsAction()
    {
        $session = $this->get('session');
        $hydra = $session->get('hydra');
        if ($hydra instanceof Hydra) {
            return $this->render('@Mobicoop/hydra/error.html.twig', ['hydra' => $hydra]);
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
            'searchRoute' => 'covoiturage/recherche',
        ]);
    }

    /**
     * Show the platform widget page to get the widget code.
     */
    public function getPlatformWidget()
    {
        // $this->denyAccessUnlessGranted('show', $community);
        return $this->render('@Mobicoop/platform-get-widget.html.twig');
    }

    /**
     * Show a default page when the request page no longer exists.
     */
    public function getPageNoLongerExists()
    {
        return $this->render('@Mobicoop/page-no-longer-exists.html.twig');
    }

    /**
     * Store language selected by user in session.
     */
    public function setSessionLanguage(Request $request)
    {
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
        }

        return new JsonResponse();
    }

    /**
     * Refresh the api token.
     */
    public function refreshToken(Request $request)
    {
        if ($request->isMethod('POST')) {
            return new JsonResponse(['token' => $this->dataProvider->getToken()]);
        }

        return new JsonResponse();
    }
}
