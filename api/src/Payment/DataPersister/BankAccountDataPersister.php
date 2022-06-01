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

namespace App\Payment\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Payment\Exception\PaymentException;
use App\Payment\Ressource\BankAccount;
use App\Payment\Service\PaymentManager;
use Symfony\Component\Security\Core\Security;

/**
 * Bank Account Data Persister.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
final class BankAccountDataPersister implements ContextAwareDataPersisterInterface
{
    private $paymentManager;
    private $security;

    public function __construct(PaymentManager $paymentManager, Security $security)
    {
        $this->paymentManager = $paymentManager;
        $this->security = $security;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof BankAccount;
    }

    public function persist($data, array $context = [])
    {
        if (isset($context['collection_operation_name']) && 'post' == $context['collection_operation_name']) {
            if (!$this->paymentManager->checkValidForRegistrationToTheProvider($this->security->getUser(), $data->getAddress())) {
                throw new PaymentException(PaymentException::USER_INVALID);
            }
            $data = $this->paymentManager->createBankAccount($this->security->getUser(), $data);
        }

        return $data;
    }

    public function remove($data, array $context = [])
    {
        // call your persistence layer to delete $data
    }
}
