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

namespace Mobicoop\Bundle\MobicoopBundle\Price\Controller;

use Mobicoop\Bundle\MobicoopBundle\Price\Entity\Price;
use Mobicoop\Bundle\MobicoopBundle\Price\Service\PriceManager;
use Mobicoop\Bundle\MobicoopBundle\Traits\HydraControllerTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PriceController extends AbstractController
{
    use HydraControllerTrait;

    /**
     * @param Request $request
     *
     * Handle post request for round prices
     *
     * @param PriceManager $priceManager
     * @return JsonResponse
     */
    public function roundPrice(Request $request, PriceManager $priceManager)
    {
        $price = new Price();

        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            $reponseofmanager = $this->handleManagerReturnValue($data);

            if (!empty($reponseofmanager)) {
                return $reponseofmanager;
            }

            $price->setValue($data["value"]);
            $price->setFrequency($data["frequency"]);

            /** @var Price $roundedPrice */
            $roundedPrice = $priceManager->roundPrice($price);

            return new JsonResponse(
                ["value" => $roundedPrice->getValue()],
                Response::HTTP_OK
            );
        }

        return new JsonResponse(
            ["message" => "error"],
            Response::HTTP_FORBIDDEN
        );
    }
}
