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

use DateTime;
use Mobicoop\Bundle\MobicoopBundle\Api\Entity\Response;
use Mobicoop\Bundle\MobicoopBundle\Entity\Contact;
use Mobicoop\Bundle\MobicoopBundle\Service\ContactManager;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use phpDocumentor\Parser\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class ContactController extends AbstractController
{
    use HydraControllerTrait;

    /**
     * Show the contact page
     */
    public function showContact()
    {
        return $this->render(
            '@Mobicoop/contact/contact.html.twig'
        );
    }

    /**
     * @param Request $request
     *
     * Handle post request from contact form
     *
     * @param ContactManager $contactManager
     */
    public function sendContact(Request $request, ContactManager $contactManager)
    {
        $contact = new Contact();

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $reponseofmanager = $this->handleManagerReturnValue($data);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }

            $errors = [];

            // pass front info into contact form
            !empty($data['email']) ? $contact->setEmail($data['email']) : $errors[] = "email.errors.required";
            !empty($data['message']) ? $contact->setMessage($data['message']) : $errors[] = "message.errors.required";
            $contact->setDemand($data['demand']);
            $contact->setGivenName($data['givenName']);
            $contact->setFamilyName($data['familyName']);
            $contact->setDatetime(new DateTime());

            if (count($errors) > 0) {
                return new JsonResponse(
                    [
                    "errors" => $errors
                ],
                    \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            if ($response = $contactManager->sendContactEmail($contact)) {

//                return new JsonResponse(
//                    [
//                        "message" => "OK"
//                    ],
//                    \Symfony\Component\HttpFoundation\Response::HTTP_ACCEPTED
//                );
            }
            $reponseofmanager = $this->handleManagerReturnValue($response);
            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }
//            return new Response();
            return new JsonResponse($response);die;


        }

        // todo: custom error and ok messages
        return new JsonResponse(
            [
            "message" => "Erreur"
        ],
            \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN
        );
    }
}
