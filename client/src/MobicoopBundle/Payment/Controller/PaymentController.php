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
    private $paymentManager;

    /**
    * Constructor
    */
    public function __construct(PaymentManager $paymentManager, bool $payment_electronic_active)
    {
        $this->payment_electronic_active = $payment_electronic_active;
        $this->paymentManager = $paymentManager;
    }

    /**
     * Display of the payment page
     *
     */
    public function payment($id, $frequency, $type)
    {
        if ($id == '' || $frequency == '' || $type == '') {
            throw new \LogicException("Missing parameters");
        }
        return $this->render('@Mobicoop/payment/payment.html.twig', [
            "paymentElectronicActive" => $this->payment_electronic_active === "true" ? true : false,
            "selectedId" => $id,
            "frequency" => $frequency,
            "type" => $type,
        ]);
    }

  
    /**
    * Get all payment itmes of a user
     * AJAX
     * @param Request $request
     * @param PaymentManager $paymentManager
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

    /**
     * Post payments
     *
     * @param Request $request
     * @param PaymentManager $paymentManager
     */
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

    /**
     * Get weeks with a pending payment
     *
     * @param Request $request
     * @param PaymentManager $paymentManager
     */
    public function getWeeks(Request $request, PaymentManager $paymentManager)
    {
        $weeks = null;
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if ($weeks = $paymentManager->getWeeks($data['askId'])) {
                return new JsonResponse($weeks);
            }
        }
        return new JsonResponse($weeks);
    }

    /**
     * Get the first non validated week for a regular Ask
     *
     * @param Request $request
     * @param PaymentManager $paymentManager
     */
    public function getFirstWeek(Request $request, PaymentManager $paymentManager)
    {
        $week = null;
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if ($week = $paymentManager->getFirstWeek($data['id'])) {
                return new JsonResponse($week);
            }
        }
        return new JsonResponse($week);
    }

    /**
     * Get the calendar of payments for a regular Ad
     *
     * @param Request $request
     * @param PaymentManager $paymentManager
     */
    public function getCalendar(Request $request, PaymentManager $paymentManager)
    {
        $periods = null;
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if ($periods = $paymentManager->getCalendar($data['type'])) {
                return new JsonResponse($periods);
            }
        }
        return new JsonResponse($periods);
    }

    /**
     * Landing page after an online payment
     *
     * @param Request $request
     */
    public function paymentPaid(Request $request)
    {
        $paymentPaymentId = $request->get("paymentPaymentId");
        if (is_null($paymentPaymentId) || $paymentPaymentId=="") {
            $paymentPaymentId = -1;
        }
        return $this->render(
            '@Mobicoop/payment/payment-paid.html.twig',
            [
                "paymentPaymentId"=>$paymentPaymentId
            ]
        );
    }

    /**
     * Get the status of a carpoolPaypement
     *
     * @param Request $request
     */
    public function getCarpoolPaymentStatus(Request $request)
    {
        $status = null;
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);
            if (!isset($data['paymentPaymentId']) || $data['paymentPaymentId']==="") {
                $status['error'] = true;
                $status['message'] = "No paymentPaymentId id";
                return new JsonResponse($status);
            }

            $paymentpayment = $this->paymentManager->getPaymentPayment($data['paymentPaymentId']);
            $status['error'] = false;
            $status['status'] = $paymentpayment->getStatus();
            return new JsonResponse($status);
        }
        return new JsonResponse($status);
    }
}
