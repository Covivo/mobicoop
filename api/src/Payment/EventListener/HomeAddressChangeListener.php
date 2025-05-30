<?php

/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace App\Payment\EventListener;

use App\Payment\Service\PaymentDataProvider;
use App\Payment\Service\PaymentManager;
use App\User\Event\UserHomeAddressUpdateEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class HomeAddressChangeListener implements EventSubscriberInterface
{
    private $_paymentManager;
    private $_paymentDataProvider;

    public function __construct(PaymentManager $paymentManager, PaymentDataProvider $paymentDataProvider)
    {
        $this->_paymentManager = $paymentManager;
        $this->_paymentDataProvider = $paymentDataProvider;
    }

    public static function getSubscribedEvents()
    {
        return [
            UserHomeAddressUpdateEvent::NAME => 'onUserHomeAddressUpdated',
        ];
    }

    public function onUserHomeAddressUpdated(UserHomeAddressUpdateEvent $event)
    {
        $user = $event->getUser();

        if ($this->_paymentManager->hasPaymentProfileForCurrentProvider($user)) {
            $this->_paymentDataProvider->updateUser($user);
        }
    }
}
