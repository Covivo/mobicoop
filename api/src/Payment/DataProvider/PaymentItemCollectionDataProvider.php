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
 */

namespace App\Payment\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Carpool\Entity\Criteria;
use App\Payment\Ressource\PaymentItem;
use App\Payment\Service\PaymentManager;
use Symfony\Component\Security\Core\Security;

/**
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
final class PaymentItemCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
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

        return PaymentItem::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        // we initialize the frequency
        $frequency = Criteria::FREQUENCY_PUNCTUAL;
        if (!empty($this->filters['frequency'])) {
            $frequency = $this->filters['frequency'];
        }

        // we initialize the type
        $type = PaymentItem::TYPE_PAY;
        if (!empty($this->filters['type'])) {
            $type = $this->filters['type'];
        }

        if (!empty($this->filters['day'])) {
            return $this->paymentManager->getPaymentItems($this->security->getUser(), $frequency, $type, $this->filters['day']);
        }

        if (!empty($this->filters['week'])) {
            return $this->paymentManager->getPaymentItems($this->security->getUser(), $frequency, $type, null, $this->filters['week']);
        }

        return $this->paymentManager->getPaymentItems($this->security->getUser(), $frequency, $type);
    }
}
