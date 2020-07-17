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

namespace App\Payment\Service;

use App\Geography\Entity\Address;
use App\Payment\Entity\PaymentProfile;
use App\Payment\Exception\PaymentException;
use App\Payment\Repository\PaymentProfileRepository;
use App\Payment\Ressource\BankAccount;
use App\Payment\Ressource\PaymentItem;
use App\Payment\Ressource\PaymentPayment;
use App\User\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Payment manager service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class PaymentManager
{
    private $entityManager;
    private $provider;
    private $paymentProvider;
    private $paymentProfileRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        PaymentDataProvider $paymentProvider,
        PaymentProfileRepository $paymentProfileRepository,
        String $paymentProviderService
    ) {
        $this->provider = $paymentProviderService;
        $this->entityManager = $entityManager;
        $this->paymentProvider = $paymentProvider;
        $this->paymentProfileRepository = $paymentProfileRepository;
    }

    /**
     * Get the payment items
     *
     * @param User $user            The user concerned
     * @param integer $frequency    The frequency for the items (1 = punctual, 2 = regular)
     * @param integer $type         The type of items (1 = to pay, 2 = to collect)
     * @param integer|null $week    The week and year for regular items, under the form WWYYYY (ex : 052020 pour for the week 05 of year 2020)
     * @return array The payment items found
     */
    public function getPaymentItems(User $user, int $frequency = 1, int $type = 1, ?int $week = null)
    {
        $items = [];
        
        // TODO : get the real payments for the logged user !

        // first we get the items for the given user

        // then we create each PaymentItem from the items
        for ($i=1;$i<50;$i++) {
            $paymentItem = new PaymentItem($i);
            $paymentItem->setFrequency(PaymentItem::FREQUENCY_REGULAR);
            $paymentItem->setType(PaymentItem::TYPE_PAY);
            $paymentItem->setGivenName(chr(rand(65, 90)).chr(rand(97, 122)).chr(rand(97, 122)).chr(rand(97, 122)).chr(rand(97, 122)).chr(rand(97, 122)).chr(rand(97, 122)));
            $paymentItem->setShortFamilyName(chr(rand(65, 90)));
            $paymentItem->setOutwardAmount($i*2+$i/10);
            $paymentItem->setReturnAmount($paymentItem->getOutwardAmount()+1);
            $paymentItem->setOutwardDays([0,1,1,2,1,1,0]);
            $paymentItem->setReturnDays([0,1,2,2,1,1,0]);
            $origin = new Address();
            $origin->setAddressLocality("Nancy ".$i);
            $destination = new Address();
            $destination->setAddressLocality("Metz ".$i);
            $paymentItem->setOrigin($origin);
            $paymentItem->setDestination($destination);
            $items[] = $paymentItem;
        }

        // finally we return the array of PaymentItem
        return $items;
    }

    public function createPayment(PaymentPayment $payment)
    {
        // TODO : create the real payment !
        $payment->setStatus(rand(PaymentPayment::STATUS_SUCCESS, PaymentPayment::STATUS_FAILURE));
        return $payment;
    }

    
    /**
     * Create a bank account for a User
     *
     * @param User $user
     * @param BankAccount $bankAccount
     * @return BankAccount
     */
    public function createBankAccount(User $user, BankAccount $bankAccount)
    {
        // Check if there is a paymentProfile
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user'=>$user]);
        if (is_null($paymentProfiles) || count($paymentProfiles)==0) {
            // No Payment Profile, we create one
            $identifier = null;

            // First we register the User on the payment provider to get an identifier
            $identifier = $this->paymentProvider->registerUser($user);

            if ($identifier==null || $identifier=="") {
                throw new PaymentException(PaymentException::REGISTER_USER_FAILED);
            }

            // Now, we create a Wallet for this User
            $wallet = null;
            $wallet = $this->paymentProvider->createWallet($identifier);
            if ($wallet==null || $wallet=="") {
                throw new PaymentException(PaymentException::REGISTER_USER_FAILED);
            }


            $this->createPaymentProfile($user, $identifier, true);
        }

        return $this->paymentProvider->addBankAccount($bankAccount);
    }

    /**
     * Disable a bank account
     *
     * @param User $user
     * @param BankAccount $bankAccount
     * @return BankAccount
     */
    public function disableBankAccount(User $user, BankAccount $bankAccount)
    {
        // Check if there is a paymentProfile
        $paymentProfiles = $this->paymentProfileRepository->findBy(['user'=>$user]);

        if (is_null($paymentProfiles) || count($paymentProfiles)==0) {
            throw new PaymentException(PaymentException::NO_PAYMENT_PROFILE);
        }

        return $this->paymentProvider->disableBankAccount($bankAccount);
    }
    
    /**
     * Create a paymentProfile
     *
     * @param User $user                     The User we want to create a profile
     * @param string $identifier             The User identifier on the payment provider service
     * @param bool $electronicallyPayable    If the User can be payed electronically
     * @return PaymentProfile
     */
    public function createPaymentProfile(User $user, string $identifier, bool $electronicallyPayable = false)
    {
        $paymentProfile = new PaymentProfile();
        $paymentProfile->setUser($user);
        $paymentProfile->setProvider($this->provider);
        $paymentProfile->setIdentifier($identifier);
        $paymentProfile->setStatus(1);
        $paymentProfile->setElectronicallyPayable($electronicallyPayable);
        $this->entityManager->persist($paymentProfile);
        $this->entityManager->flush();

        return $paymentProfile;
    }
}
