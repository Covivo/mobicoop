<?php

namespace App\Tests\DataProvider\Entity\Stripe\Mock;

use App\Payment\Entity\PaymentProfile;

class MockPaymentProfile
{
    public static function getPaymentProfile(): PaymentProfile
    {
        $paymentProfile = new PaymentProfile(1);
        $paymentProfile->setIdentifier('paymentProfileId');
        $paymentProfile->setProvider('Stripe');
        $paymentProfile->setStatus(1);
        $paymentProfile->setElectronicallyPayable(1);
        $paymentProfile->setValidationId('validationId');
        $paymentProfile->setValidatedDate(new \DateTime());
        $paymentProfile->setValidationStatus(1);
        $paymentProfile->setUser(MockUser::getSimpleUser());

        return $paymentProfile;
    }
}
