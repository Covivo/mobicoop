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

namespace App\Payment\Security;

use App\Auth\Service\AuthManager;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use App\Payment\Ressource\ElectronicPayment;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class ElectronicPaymentVoter extends Voter
{
    const ELECTRONIC_PAYMENT_CREATE = 'electronic_payment_create';
    const ELECTRONIC_PAYMENT_READ = 'electronic_payment_read';
    const ELECTRONIC_PAYMENT_LIST = 'electronic_payment_list';

    private $security;
    private $permissionManager;

    public function __construct(AuthManager $authManager)
    {
        $this->authManager = $authManager;
    }

    protected function supports($attribute, $subject)
    {
        // if the attribute isn't one we support, return false
        if (!in_array($attribute, [
            self::ELECTRONIC_PAYMENT_CREATE,
            self::ELECTRONIC_PAYMENT_READ,
            self::ELECTRONIC_PAYMENT_LIST,
            ])) {
            return false;
        }
      
        // only vote on User objects inside this voter
        if (!in_array($attribute, [
            self::ELECTRONIC_PAYMENT_CREATE,
            self::ELECTRONIC_PAYMENT_READ,
            self::ELECTRONIC_PAYMENT_LIST,
            ]) && !($subject instanceof Paginator) && !$subject instanceof ElectronicPayment) {
            return false;
        }
        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        switch ($attribute) {
            case self::ELECTRONIC_PAYMENT_CREATE:
                return $this->canCreateElectronicPayment($subject);
            case self::ELECTRONIC_PAYMENT_READ:
                return $this->canReadElectronicPayment($subject);
            case self::ELECTRONIC_PAYMENT_LIST:
                return $this->canListElectronicPayment();
        }

        throw new \LogicException('This code should not be reached!');
    }

    private function canCreateElectronicPayment(ElectronicPayment $electronicPayment)
    {
        return $this->authManager->isAuthorized(self::ELECTRONIC_PAYMENT_CREATE, ['electronicPayment'=>$electronicPayment]);
    }

    private function canReadElectronicPayment(ElectronicPayment $electronicPayment)
    {
        return $this->authManager->isAuthorized(self::ELECTRONIC_PAYMENT_READ, ['electronicPayment'=>$electronicPayment]);
    }

    private function canListElectronicPayment()
    {
        return $this->authManager->isAuthorized(self::ELECTRONIC_PAYMENT_CREATE);
    }
}
