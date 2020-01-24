<?php

/**
 * Copyright (c) 2020, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\EventListener;

use Mobicoop\Bundle\MobicoopBundle\Import\Entity\Redirect;
use Mobicoop\Bundle\MobicoopBundle\Import\Service\RedirectManager;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Exception listener, used to redirect routes in case of data import : redirect an old route (from an old platform) to a new one.
 */
class ExceptionListener
{
    private $requestStack;
    private $redirectManager;
    private $router;

    public function __construct($router, RequestStack $requestStack, RedirectManager $redirectManager)
    {
        $this->requestStack = $requestStack;
        $this->redirectManager = $redirectManager;
        $this->router = $router;
    }

    public function onKernelException(ExceptionEvent $event)
    {
        // get the exception from the event
        $exception = $event->getException();

        // check if the exception is a NotFoundHttpException
        if ($exception instanceof NotFoundHttpException) {
            // route not found, we check if the route is a redirection for a data import
            $redirect = $this->redirectManager->getRedirect(substr($this->requestStack->getCurrentRequest()->getRequestUri(), 1)); // note : we remove the leading '/' with a substr
            if (!is_null($redirect)) {
                $url = null;
                // the route is found, we redirect
                switch ($redirect->getType()) {
                    case Redirect::TYPE_COMMUNITY:
                        $url = $this->router->generate('community_show.' . $redirect->getLanguage(), ['id' => $redirect->getDestinationId()]);
                    break;
                    case Redirect::TYPE_EVENT:
                        $url = $this->router->generate('event_show.' . $redirect->getLanguage(), ['id' => $redirect->getDestinationId()]);
                    break;
                }
                if (!is_null($url)) {
                    $response = new RedirectResponse($url);
                    $event->setResponse($response);
                }
            }
        }
    }
}
