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

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Controller class for payments actions.
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
            "payment_electronic_active"=>($this->payment_electronic_active==="true") ? true : false,
        ]);
    }
}
