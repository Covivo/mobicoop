<?php

declare(strict_types=1);

namespace App\Validator\Phone;

use libphonenumber\PhoneNumberUtil;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PhoneValidatorRegion extends Validator
{
    private $region;

    public function __construct(PhoneNumberUtil $phoneNumberUtil, string $region)
    {
        $this->region = $region;
        parent::__construct($phoneNumberUtil);
    }

    protected function getRegion(): string
    {
        return $this->region;
    }
}
