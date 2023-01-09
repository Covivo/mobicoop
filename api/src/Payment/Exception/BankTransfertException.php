<?php
/**
 * Copyright (c) 2022, MOBICOOP. All rights reserved.
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

namespace App\Payment\Exception;

/**
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransfertException extends \LogicException
{
    public const ERROR_OPENING_FILE = 'Error opening file : ';
    public const BAD_DELIMITER = 'Bad CSV delimiter. The CSV file MUST use semicolon ; as delimiter : ';

    // Bank Transfert Builder
    public const BT_BUILDER_NO_DATA = 'No data to build';

    // Bank Transfert Emitter
    public const EMITTER_NO_TRANSFERT_FOR_THIS_BATCH_ID = 'No bank transfert found for this batch id';

    // Bank Transfert Emitter Validator
    public const EMITTER_VALIDATOR_NO_TRANSFERT = 'No bank transfert';
    public const FUNDS_UNAVAILABLE = 'Not enough funds';
    public const NO_PAYMENT_PROVIDER = 'No payment provider';
    public const NO_HOLDER_ID = 'No Holder id';
    public const NO_HOLDER_FOUND = 'No Holder found';
    public const NO_HOLDER_WALLET = 'No holder wallet';

    // Bank Transfert Summarizer Validator
    public const SUMMARIZER_NO_TRANSFERT_FOR_THIS_BATCH_ID = 'No bank transfert to summarize';
}
