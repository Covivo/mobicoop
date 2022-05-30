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

namespace App\DataProvider\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use ApiPlatform\Core\Exception\ResourceClassNotSupportedException;
use App\DataProvider\Ressource\MangoPayHook;
use App\Payment\Exception\PaymentException;
use App\Payment\Service\PaymentManager;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class MangoPayHookKYCCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $request;
    private $paymentManager;

    public function __construct(RequestStack $requestStack, PaymentManager $paymentManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->paymentManager = $paymentManager;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return MangoPayHook::class === $resourceClass && $operationName == "mangoPayKYC";
    }

    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        $mangoPayHook = new MangoPayHook();
        if (is_null($this->request->get('EventType')) ||
            is_null($this->request->get('RessourceId')) ||
            is_null($this->request->get('Date'))
        ) {
            throw new PaymentException(PaymentException::MISSING_PARAMETER);
        }
        
        if (
            $this->request->get('EventType')!==MangoPayHook::VALIDATION_SUCCEEDED &&
            $this->request->get('EventType')!==MangoPayHook::VALIDATION_FAILED &&
            $this->request->get('EventType')!==MangoPayHook::VALIDATION_OUTDATED
        ) {
            throw new \LogicException("Unknown MangoPay KYC EventType");
        }

        $mangoPayHook->setEventType($this->request->get('EventType'));
        $mangoPayHook->setRessourceId($this->request->get('RessourceId'));
        $mangoPayHook->setDate($this->request->get('Date'));
        if (null !== $this->request->get('token') && $this->request->get('token')!=="") {
            $mangoPayHook->setSecurityToken($this->request->get('token'));
        }
        return $this->paymentManager->handleHookValidation($mangoPayHook);
    }
}
