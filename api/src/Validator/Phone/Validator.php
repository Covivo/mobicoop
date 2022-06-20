<?php

declare(strict_types=1);

namespace App\Validator\Phone;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
abstract class Validator
{
    protected $next;
    protected $phoneNumberUtil;

    public function __construct(PhoneNumberUtil $phoneNumberUtil)
    {
        $this->phoneNumberUtil = $phoneNumberUtil;
    }

    public function validate(string $phone): bool
    {
        if ($this->isValid($phone)) {
            return true;
        }

        if (isset($this->next)) {
            return $this->next->validate($phone);
        }

        return false;
    }

    public function setNext(self $next): void
    {
        $this->next = $next;
    }

    protected function isValid(string $phone): bool
    {
        var_dump($this->getRegion());
        $phoneNumber = $this->parse($phone, $this->getRegion());
        if (!is_null($phoneNumber->getNationalNumber())) {
            return $this->phoneNumberUtil->isValidNumberForRegion($phoneNumber, $this->getRegion());
        }

        return false;
    }

    protected function parse(string $phone, string $region)
    {
        try {
            return $this->phoneNumberUtil->parse($phone, $region);
        } catch (NumberParseException $exception) {
            return new PhoneNumber();
        }
    }

    abstract protected function getRegion(): string;
}
