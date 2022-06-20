<?php

declare(strict_types=1);

namespace App\Validator\Phone\Validators;

use App\Validator\Phone\Validator;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PhoneValidatorFR extends Validator
{
    protected function getRegion(): string
    {
        return 'FR';
    }
}
