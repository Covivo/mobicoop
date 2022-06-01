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

namespace App\Payment\DataProvider;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Payment\Ressource\PaymentWeek;
use App\Payment\Service\PaymentManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Get the first non validated week of an Ask.
 */
final class PaymentWeekFirstNonValidatedItemDataProvider implements RestrictedDataProviderInterface, ItemDataProviderInterface
{
    protected $paymentManager;
    protected $request;
    protected $security;

    public function __construct(PaymentManager $paymentManager, RequestStack $requestStack, Security $security)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->paymentManager = $paymentManager;
        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return PaymentWeek::class === $resourceClass && $operationName === "get";
    }

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?object
    {
        return $this->paymentManager->getFirstNonValidatedWeek($this->security->getUser(), $this->request->get("id"));
    }
}
