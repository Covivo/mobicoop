<?php

declare(strict_types=1);

namespace App\Validator\Phone;

use libphonenumber\PhoneNumberUtil;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PhoneValidator
{
    public const ERROR_MESSAGE = 'errors.phoneNumberInvalid';
    public $message;
    private $validators;

    public function __construct(PhoneNumberUtil $phoneNumberUtil, array $phoneValidationRegions)
    {
        foreach ($phoneValidationRegions as $region) {
            $this->validators[] = new PhoneValidatorRegion($phoneNumberUtil, $region);
        }

        foreach ($this->validators as $key => $validator) {
            if (isset($this->validators[$key + 1])) {
                $validator->setNext($this->validators[$key + 1]);
            }
        }
    }

    public function validate(string $phone): bool
    {
        if (0 == count($this->validators)) {
            $this->message = self::ERROR_MESSAGE;

            return false;
        }

        $result = $this->validators[0]->validate($phone);

        if (!$result) {
            $this->message = self::ERROR_MESSAGE;
        }

        return $result;
    }
}
