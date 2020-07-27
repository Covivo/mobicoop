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

namespace Mobicoop\Bundle\MobicoopBundle\Payment\Controller;

use Mobicoop\Bundle\MobicoopBundle\Payment\Entity\PaymentPayment;
use Mobicoop\Bundle\MobicoopBundle\Payment\Service\PaymentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller class for payments actions.
 *
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 *
 */
class PaymentController extends AbstractController
{
    private $payment_electronic_active;

    /**
    * Constructor
    */
    public function __construct($payment_electronic_active)
    {
        $this->payment_electronic_active = $payment_electronic_active;
    }

    /**
     * Display of the payment page
     *
     */
    public function payment()
    {
        return $this->render('@Mobicoop/payment/payment.html.twig', [
            "paymentElectronicActive" => $this->payment_electronic_active === "true" ? true : false
        ]);
    }

  
    /**
    * Get all payment itmes of a user
     * AJAX
     * @param Request $request
     * @param PaymentManager $paymentManager
     * @return void
     */
    public function getPaymentItems(Request $request, PaymentManager $paymentManager)
    {
        $paymentItems = null;
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if ($paymentItems = $paymentManager->getPaymentItems($data['frequency'], $data['type'], $data['week'])) {
                return new JsonResponse($paymentItems);
            }
        }
        return new JsonResponse($paymentItems);
    }

    public function postPayments(Request $request, PaymentManager $paymentManager)
    {
        $paymentPayment = null;
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
           
            if ($paymentPayment = $paymentManager->postPaymentPayment($data['type'], $data['items'])) {
                return new JsonResponse($paymentPayment);
            }
        }
        return new JsonResponse($paymentPayment);
    }
}
