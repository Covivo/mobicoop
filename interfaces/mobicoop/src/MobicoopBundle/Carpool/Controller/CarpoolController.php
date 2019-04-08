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
 *    along with this program.  If not, see <gnu.oruse Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;g/licenses>.
 ***************************
 *    Licence MOBICOOP described in the file
 *    LICENSE
 **************************/

namespace Mobicoop\Bundle\MobicoopBundle\Carpool\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Form\AdForm;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\AdManager;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;
use Symfony\Component\HttpFoundation\Response;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\ProposalManager;

/**
 * Controller class for carpooling related actions.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class CarpoolController extends AbstractController
{
    /**
     * Create a carpooling ad.
     * @IsGranted("ROLE_USER")
     */
    public function ad(AdManager $adManager, UserManager $userManager, Request $request)
    {
        $date = new \DateTime();
        $ad = new Ad();
        $ad->setRole(Ad::ROLE_BOTH);
        $ad->setType(Ad::TYPE_ONE_WAY);
        $ad->setFrequency(Ad::FREQUENCY_PUNCTUAL);
        $ad->setPrice(Ad::PRICE);
        // $ad->setOutwardDate($date->format('Y/m/d'));
        $ad->setUser($userManager->getLoggedUser());

        $form = $this->createForm(AdForm::class, $ad, ['csrf_protection' => false]);
        $error = false;
        $sucess = false;

        
        
        if ($request->isMethod('POST')) {
            $createToken = $request->request->get('createToken');
            if (!$this->isCsrfTokenValid('ad-create', $createToken)) {
                return  new Response('Broken Token CSRF ', 403);
            }
            $form->submit($request->request->get($form->getName()));
            // $form->submit($request->request->all());
        }

        // If it's a get, just render the form !
        if (!$form->isSubmitted()) {
            return $this->render('@Mobicoop/ad/create.html.twig', [
                'form' => $form->createView(),
                'error' => $error
            ]);
        }
        
        // Not Valid populate error
        if (!$form->isValid()) {
            $error = [];
            // Fields
            foreach ($form as $child) {
                if (!$child->isValid()) {
                    foreach ($child->getErrors(true) as $err) {
                        $error[$child->getName()][] = $err->getMessage();
                    }
                }
            }
            return $this->json(['error' => $error, 'sucess'=> $sucess]);
        }

        // Error happen durring proposol creation
        try {
            $ad = $adManager->createProposalFromAd($ad);
            $sucess = true;
        } catch (Error $err) {
            $error = $err;
        }

        return $this->json(['error' => $error, 'sucess'=> $sucess, 'ad' => print_r($ad, true)]);
    }

    /**
     * Simple search results.
     */
    public function simpleSearchResults($origin_latitude, $origin_longitude, $destination_latitude, $destination_longitude, $date, ProposalManager $proposalManager)
    {
        echo "<pre>" . print_r($proposalManager->getMatchingsForSearch($origin_latitude, $origin_longitude, $destination_latitude, $destination_longitude, \Datetime::createFromFormat("YmdHis", $date)), true) . "</pre>";
        exit;
    }
}
