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
use App\Payment\Exception\PaymentException;
use App\Payment\Ressource\BankAccount;
use App\Payment\Service\PaymentManager;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Disable a bank account
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class BankAccountDisableCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $paymentManager;
    private $security;
    private $request;

    public function __construct(PaymentManager $paymentManager, Security $security, RequestStack $request)
    {
        $this->paymentManager = $paymentManager;
        $this->security = $security;
        $this->request = $request->getCurrentRequest();
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return BankAccount::class === $resourceClass && isset($context['collection_operation_name']) && $context['collection_operation_name'] == 'disable';
    }

    public function getCollection(string $resourceClass, string $operationName = null): iterable
    {
        if ($this->request->get("idBankAccount") == "") {
            throw new PaymentException(PaymentException::NO_BANKACCOUNT_ID_IN_UPDATE_REQUEST);
        }
        $bankAccount = new BankAccount();
        $bankAccount->setId($this->request->get("idBankAccount"));
        $bankAccount->setStatus(BankAccount::STATUS_INACTIVE);
        return $this->paymentManager->disableBankAccount($this->security->getUser(), $bankAccount);
    }
}
