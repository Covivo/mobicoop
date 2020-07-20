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

namespace App\Payment\Exception;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PaymentException extends \LogicException
{
    const PAYMENT_INACTIVE = "Payment is not active on this platform";
    const PAYMENT_NO_PROVIDER = "No provider given";
    const UNSUPPORTED_PROVIDER = "This payment provider is not yet supported";
    const NO_PAYMENT_PROFILE = "No payment profile";
    
    // User
    const NO_IDENTIFIER = "No identifier found";
    const NO_BIRTHDATE = "No birthdate";
    const NO_COUNTRY = "No country";
    const NO_ADDRESS = "No home address";
    const ADDRESS_INVALID = "Some field in the address are missing";
    const REGISTER_USER_FAILED = "Registration of this User to the provider has failed";

    // BankAccount
    const NO_BANKACCOUNT_ID_IN_UPDATE_REQUEST = "This request must contains en idBankAccount parameter";
    const NOT_THE_OWNER = "The current User is not the owner of this BankAccount";
}
