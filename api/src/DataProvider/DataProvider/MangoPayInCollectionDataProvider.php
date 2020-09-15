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
use App\DataProvider\Ressource\MangoPayIn;
use App\Payment\Service\PaymentDataProvider;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class MangoPayInCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $request;
    private $paymentDataProvider;

    public function __construct(RequestStack $requestStack, PaymentDataProvider $paymentDataProvider)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->paymentDataProvider = $paymentDataProvider;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return MangoPayIn::class === $resourceClass && $operationName == "mangoPayins";
    }

    public function getCollection(string $resourceClass, string $operationName = null)
    {
        $mangoPayIn = new MangoPayIn();
        if (is_null($this->request->get('EventType')) ||
            is_null($this->request->get('RessourceId')) ||
            is_null($this->request->get('Date'))
        ) {
            throw new \LogicException("Missing parameter");
        }
        
        if ($this->request->get('EventType')!==MangoPayIn::PAYIN_SUCCEEDED &&
            $this->request->get('EventType')!==MangoPayIn::PAYIN_FAILED
        ) {
            throw new \LogicException("Unknown MangoPay EventType");
        }

        $mangoPayIn->setEventType($this->request->get('EventType'));
        $mangoPayIn->setRessourceId($this->request->get('RessourceId'));
        $mangoPayIn->setDate($this->request->get('Date'));
        $mangoPayIn->setSecurityToken($this->request->get('token'));
        return $this->paymentDataProvider->handleHook($mangoPayIn);
    }
}
