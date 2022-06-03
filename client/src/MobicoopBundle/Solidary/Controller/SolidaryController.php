<?php
/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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

namespace Mobicoop\Bundle\MobicoopBundle\Solidary\Controller;

use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\ProposalManager;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Mobicoop\Bundle\MobicoopBundle\Solidary\Entity\Solidary;
use Mobicoop\Bundle\MobicoopBundle\Solidary\Service\SolidaryManager;
use Mobicoop\Bundle\MobicoopBundle\Solidary\Service\StructureManager;
use Mobicoop\Bundle\MobicoopBundle\Solidary\Service\SubjectManager;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Mobicoop\Bundle\MobicoopBundle\User\Entity\User;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface as TranslationTranslatorInterface;

class SolidaryController extends AbstractController
{
    use HydraControllerTrait;

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function index(StructureManager $structureManager, SubjectManager $subjectManager)
    {
        $structures = $structureManager->getStructures();
        $subjects = $subjectManager->getSubjects();

        return $this->render(
            '@Mobicoop/solidary/solidary.html.twig',
            [
                'subjects' => $subjects,
                'structures' => $structures,
            ]
        );
    }

    /**
     * Handle post request from solidary form.
     *
     * @param TranslatorInterface $translator
     *
     * @return JsonResponse
     */
    public function solidaryCreate(
        Request $request,
        SolidaryManager $solidaryManager,
        UserManager $userManager,
        TranslationTranslatorInterface $translator,
        StructureManager $structureManager,
        SubjectManager $subjectManager,
        ProposalManager $proposalManager
    ) {
        $solidary = new Solidary();

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            $datetime = new \DateTime();
            // todo: move in manager ?
            // get or create the user
            if (!empty($userManager->getLoggedUser())) {
                $user = new User($userManager->getLoggedUser()->getId());
            } else {
                $user = new User();
                $address = new Address();

                // add home address to user if it exists
                if (isset($data['address'])) {
                    $address->setAddressCountry($data['address']['addressCountry']);
                    $address->setAddressLocality($data['address']['addressLocality']);
                    $address->setCountryCode($data['address']['countryCode']);
                    $address->setCounty($data['address']['county']);
                    $address->setLatitude($data['address']['latitude']);
                    $address->setLocalAdmin($data['address']['localAdmin']);
                    $address->setLongitude($data['address']['longitude']);
                    $address->setMacroCounty($data['address']['macroCounty']);
                    $address->setMacroRegion($data['address']['macroRegion']);
                    $address->setName($translator->trans('homeAddress', [], 'signup'));
                    $address->setPostalCode($data['address']['postalCode']);
                    $address->setRegion($data['address']['region']);
                    $address->setStreet($data['address']['street']);
                    $address->setStreetAddress($data['address']['streetAddress']);
                    $address->setSubLocality($data['address']['subLocality']);
                    $address->setHome(true);
                }
                $user->addAddress($address);

                // todo: doesn't work because we need password
                $year = new \DateTime($data['yearOfBirth']);
                $user->setEmail($data['email']);
                $user->setTelephone($data['phoneNumber']);
                $user->setPassword('password'); //todo: update
                $user->setGivenName($data['givenName']);
                $user->setFamilyName($data['familyName']);
                $user->setGender($data['gender']);
                $user->setBirthYear((int) $year->format('Y'));
            }

            if (is_null($user)) {
                return new JsonResponse(
                    ['errors' => 'user.errors.required'],
                    \Symfony\Component\HttpFoundation\Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }

            $solidary->setProposal($proposalManager->createSolidaryProposalFromData($data['search'], $user));

            $solidary->setUser($user);
            $solidary->setCreatedDate($datetime);
            $solidary->setUpdatedDate($datetime);
            $solidary->setStatus(Solidary::ASKED);
            $solidary->setAssisted(!empty($data['structure']));
            if (!empty($data['structure'])) {
                $solidary->setStructure($structureManager->getStructure($data['structure'])->getName());
            }
            if (!empty($data['subject'])) {
                $solidary->setSubject($subjectManager->getSubject($data['subject'])->getLabel());
            }

            // todo: activate
//            if ($response = $solidaryManager->createSolidary($solidary)) {
            return new JsonResponse(
                ['message' => 'success'],
                \Symfony\Component\HttpFoundation\Response::HTTP_ACCEPTED
            );
//            }
            return new JsonResponse(
                ['message' => 'error create'],
                \Symfony\Component\HttpFoundation\Response::HTTP_BAD_REQUEST
            );
        }

        // todo: custom error and ok messages
        return new JsonResponse(
            ['message' => 'error'],
            \Symfony\Component\HttpFoundation\Response::HTTP_FORBIDDEN
        );
    }
}
