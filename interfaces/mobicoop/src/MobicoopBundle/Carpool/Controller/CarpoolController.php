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
use Symfony\Component\HttpFoundation\Request;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Entity\Ad;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Form\AdForm;
use Mobicoop\Bundle\MobicoopBundle\Carpool\Service\AdManager;
use Mobicoop\Bundle\MobicoopBundle\User\Service\UserManager;

/**
 * Controller class for carpooling related actions.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class CarpoolController extends AbstractController
{
    /**
     * Create a carpooling ad.
     */
    public function ad(AdManager $adManager, UserManager $userManager, Request $request)
    {
        $date = new \DateTime();
        $ad = new Ad();
        $ad->setRole(Ad::ROLE_BOTH);
        $ad->setType(Ad::TYPE_ONE_WAY);
        $ad->setFrequency(Ad::FREQUENCY_PUNCTUAL);
        $ad->setPrice(Ad::PRICE);
        $ad->setOutwardDate($date->format('Y-m-d H:i'));
        $ad->setUser($userManager->getLoggedUser());

        $form = $this->createForm(AdForm::class, $ad);
        $form->handleRequest($request);
        $error = false;

        if ($form->isSubmitted()) {
            $ad = $adManager->prepareAd($ad, $request);
            if ($form->isValid()) {
                if ($ad = $adManager->createProposalFromAd($ad)) {
                    return $this->redirectToRoute('home');
                }
                $error = true;
            }
        }

        return $this->render('@Mobicoop/ad/create.html.twig', [
            'form' => $form->createView(),
            'error' => $error
        ]);
    }
}
