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

namespace App\Rdex\Controller;

use App\Rdex\Entity\RdexError;
use App\Rdex\Entity\RdexJourney;
use App\Rdex\Service\RdexManager;
use App\TranslatorTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller class for Rdex Journey collection.
 * We use a controller instead of a data provider because we need to send a custom http status code if an error occurs.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class JourneyCollectionController
{
    use TranslatorTrait;
    protected $request;
    private $rdexManager;
    private $active;
    private $alternativeMatching;

    public function __construct(RequestStack $requestStack, RdexManager $rdexManager, bool $active, bool $alternativeMatching)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->rdexManager = $rdexManager;
        $this->alternativeMatching = $alternativeMatching;
        $this->active = $active;
    }

    /**
     * This method is invoked when a Journey collection is requested.
     *
     * @param RdexJourney $data
     *
     * @return Response
     */
    public function __invoke(array $data): JsonResponse
    {
        if (!$this->active) {
            return new JsonResponse(null, Response::HTTP_NO_CONTENT);
        }
        if (is_null($data)) {
            throw new \InvalidArgumentException($this->translator->trans('bad RdexJourney id is provided'));
        }
        $response = new JsonResponse();
        // if there are no parameters we stop without errors, in could be an api check, it shouldn't throw an error
        if ($this->rdexManager->isEmptyRequest($this->request)) {
            return $response;
        }
        $validation = $this->rdexManager->validate($this->request);
        // if validation is an RdexError, we send an error array
        if (is_a($validation, RdexError::class)) {
            $error = $this->rdexManager->createError($validation);
            $response->setContent($error['error']);
            $response->setStatusCode($error['code']);
        } elseif ($this->alternativeMatching) {
            $response->setContent(json_encode($this->rdexManager->getJourneysAlt($this->request->get('p'))));
        } else {
            $response->setContent(json_encode($this->rdexManager->getJourneys($this->request->get('p'))));
        }

        return $response;
    }
}
