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

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\Payment\Ressource\PaymentItem;
use App\Payment\Ressource\PaymentPeriod;
use App\Payment\Service\PaymentManager;
use Symfony\Component\Security\Core\Security;

/**
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
final class PaymentPeriodCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $filters;
    private $paymentManager;
    private $security;

    public function __construct(PaymentManager $paymentManager, Security $security)
    {
        $this->paymentManager = $paymentManager;
        $this->security = $security;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        if (isset($context['filters'])) {
            $this->filters = $context['filters'];
        }

        return PaymentPeriod::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        // we initialize the type
        $type = PaymentItem::TYPE_PAY;
        if (!empty($this->filters['type'])) {
            $type = $this->filters['type'];
        }

        return $this->paymentManager->getPaymentPeriods($this->security->getUser(), $type);
    }
}
