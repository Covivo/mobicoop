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
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class PaymentException extends \LogicException
{
    const DAY_OR_WEEK_NOT_PROVIDED = "Day or week number must be provided for regular carpools";
    const WEEK_WRONG_FORMAT = "Wrong week number format";
    
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
    const UPDATE_USER_FAILED = "Update of this User to the provider failed";
    const ADD_WALLET_USER_FAILED = "The addition of a Wallet has failed";
    const USER_INVALID = "User invalid for online payment. He may miss a few mandatory profile informations";

    // BankAccount
    const NO_BANKACCOUNT_ID_IN_UPDATE_REQUEST = "This request must contains en idBankAccount parameter";
    const NOT_THE_OWNER = "The current User is not the owner of this BankAccount";
    const ERROR_CREATING = "Error the bank account cannot be created";

    // Ad/Ask
    const NO_ASK_FOUND = "No Ask found";
    const NO_CARPOOL_ITEM = "No CarpoolItem found";
    const NO_CARPOOL_ITEMS = "No CarpoolItem found";
    const INVALID_USER  = "The User must be driver or passenger of this Ask";

    // Electronic payment
    const GET_URL_PAYIN_FAILED = "Failed to get the secured URL for electronic payment";

    // Documents
    const ERROR_UPLOAD = "Error uploading the document";
    const ERROR_CREATING_DOC_TO_PROVIDER = "The creation of the document to the provider has failed";
    const ERROR_VALIDATION_DOC_BAD_EXTENTION = "Validation file bad extension";
    const ERROR_CREATING_DOC_PAGE_TO_PROVIDER = "The creation of a page for a document to the provider has failed";
    const ERROR_VALIDATION_ASK_DOC = "The validation ask for a document has failed";
    const ERROR_VALIDATION_ASK_DOC_BAD_STATUS = "The validation ask for a document has failed (BAD STATUS)";
    const ERROR_DOC = "The document does not exist ";

    // Web hooks
    const MISSING_PARAMETER = "Missing parameter";
    const INVALID_SECURITY_TOKEN = "Security token invalid";
    const CARPOOL_PAYMENT_NOT_FOUND = "CarpoolPayment not found";
}
