<?php
/**
 * Copyright (c) 2024, MOBICOOP. All rights reserved.
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

namespace App\Payment\Service\Transaction;

use App\Communication\Entity\Email;
use App\Communication\Service\EmailManager;
use App\Payment\Entity\BankTransfer;
use App\Payment\Exception\BankTransferException;
use App\Payment\Repository\BankTransferRepository;
use App\TranslatorTrait;
use Psr\Log\LoggerInterface;
use Twig\Environment;

/**
 * @author Remi Wortemann <remi.wortemann@mobicoop.org>
 */
class TransactionsSummarizer
{
    use TranslatorTrait;

    public const PATH_TO_FILES = __DIR__.'/../../../../public/upload/transactions';
    public const FILES_EXTENTION = 'csv';
    public const CSV_DELIMITER = ';';

    public const CSV_HEADERS = ['ExecutionDate:ISO', 'CreditedUserId', 'Email', 'Libelle', 'Montant', 'Sens'];

    public const EMAIL_TEMPLATE = 'bank_transfers_report';
    public const EMAIL_LANGUAGE = 'fr';

    private $_BankTransferRepository;
    private $_logger;
    private $_emailManager;
    private $_communicationFolder;
    private $_emailTemplatePath;
    private $_emailTitleTemplatePath;
    private $_templating;
    private $_emailRecipients;
    private $_platformName;

    /**
     * @var BankTransfer[]
     */
    private $_BankTransfers;

    /**
     * @var string
     */
    private $_batchId;

    public function __construct(
        BankTransferRepository $BankTransferRepository,
        LoggerInterface $logger,
        EmailManager $emailManager,
        Environment $templating,
        string $communicationFolder,
        string $emailTemplatePath,
        string $emailTitleTemplatePath,
        array $emailRecipients,
        string $platformName
    ) {
        $this->_BankTransferRepository = $BankTransferRepository;
        $this->_logger = $logger;
        $this->_emailManager = $emailManager;
        $this->_communicationFolder = $communicationFolder;
        $this->_emailTemplatePath = $emailTemplatePath;
        $this->_emailTitleTemplatePath = $emailTitleTemplatePath;
        $this->_templating = $templating;
        $this->_emailRecipients = $emailRecipients;
        $this->_platformName = $platformName;
    }

    public function summarize(array $transactions)
    {
        $this->_makeCsvFile($transactions);
        // $this->_sendEmail();
    }

    private function _makeCsvFile(array $transactions)
    {
        $file = fopen(self::PATH_TO_FILES.'/'.$this->_platformName.'-'.date('m-Y').'.'.self::FILES_EXTENTION, 'w');
        fputcsv($file, self::CSV_HEADERS, self::CSV_DELIMITER);
        foreach ($transactions as $transaction) {
            $line = [];
            $line[0] = date('d/m/Y', $transaction['CreationDate']);
            $line[1] = $transaction['CreditedUserId'];

            // todo call to mangopy api to get user email
            $line[2] = 'comptabilite@mobicoop.org';
            $line[3] = $transaction['CreditedUserId'].'comptabilite@mobicoop.org';
            $line[4] = ((int) $transaction['CreditedFunds']['Amount']) / 100;

            switch ($transaction['Type']) {
                case 'PAYIN':
                    $line[5] = 'c';

                    break;

                case 'TRANSFER':
                case 'PAYOUT':
                    $line[5] = 'd';

                    break;
            }
            fputcsv($file, $line, self::CSV_DELIMITER);
        }
        fclose($file);
    }

    // private function _sendEmail()
    // {
    //     $email = new Email();
    //     if (0 == count($this->_emailRecipients)) {
    //         throw new BankTransferException(BankTransferException::NO_REPORT_RECIPIENTS);
    //     }
    //     $email->setRecipientEmail($this->_emailRecipients[0]);

    //     if (count($this->_emailRecipients) > 1) {
    //         $email->setRecipientEmailCc(array_slice($this->_emailRecipients, 1));
    //     }

    //     $titleTemplate = $this->_communicationFolder.self::EMAIL_LANGUAGE.$this->_emailTitleTemplatePath.self::EMAIL_TEMPLATE.'.html.twig';
    //     $email->setObject($this->_templating->render($titleTemplate));

    //     $bodyContext = [];
    //     $attachements = [self::PATH_TO_FILES.'/'.$this->_batchId.'.'.self::FILES_EXTENTION];
    //     $this->_emailManager->send($email, $this->_communicationFolder.self::EMAIL_LANGUAGE.$this->_emailTemplatePath.self::EMAIL_TEMPLATE, $bodyContext, self::EMAIL_LANGUAGE, $attachements);
    // }
}
