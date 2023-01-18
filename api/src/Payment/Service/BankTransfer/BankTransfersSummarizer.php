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

namespace App\Payment\Service\BankTransfer;

use App\Communication\Entity\Email;
use App\Communication\Service\EmailManager;
use App\Payment\Entity\BankTransfer;
use App\Payment\Exception\BankTransferException;
use App\Payment\Repository\BankTransferRepository;
use App\TranslatorTrait;
use Psr\Log\LoggerInterface;
use Twig\Environment;

/**
 * Bank Transfert emitter.
 *
 * @author Maxime Bardot <maxime.bardot@mobicoop.org>
 */
class BankTransfersSummarizer
{
    use TranslatorTrait;

    public const PATH_TO_FILES = __DIR__.'/../../../../public/upload/bankTransfers/reports';
    public const FILES_EXTENTION = 'csv';
    public const CSV_DELIMITER = ';';

    public const CSV_HEADERS = ['batchId', 'createdDate', 'status', 'recipient', 'amount', 'territoryId'];

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
        array $emailRecipients
    ) {
        $this->_BankTransferRepository = $BankTransferRepository;
        $this->_logger = $logger;
        $this->_emailManager = $emailManager;
        $this->_communicationFolder = $communicationFolder;
        $this->_emailTemplatePath = $emailTemplatePath;
        $this->_emailTitleTemplatePath = $emailTitleTemplatePath;
        $this->_templating = $templating;
        $this->_emailRecipients = $emailRecipients;
    }

    public function summarize(string $batchId)
    {
        $this->_batchId = $batchId;
        $this->_getTransferts();
        $this->_makeCsvFile();
        $this->_sendEmail();
    }

    private function _makeCsvFile()
    {
        $file = fopen(self::PATH_TO_FILES.'/'.$this->_batchId.'.'.self::FILES_EXTENTION, 'w');
        fputcsv($file, self::CSV_HEADERS, self::CSV_DELIMITER);
        foreach ($this->_BankTransfers as $BankTransfer) {
            $line = [];
            $line[0] = $BankTransfer->getBatchId();
            $line[1] = $BankTransfer->getCreatedDate()->format('d/m/Y');
            $line[2] = $this->translator->trans(BankTransfer::STATUS_TXT[$BankTransfer->getStatus()]);
            $line[3] = (!is_null($BankTransfer->getRecipient())) ? $BankTransfer->getRecipient()->getId() : null;
            $line[4] = $BankTransfer->getAmount();
            $line[5] = (!is_null($BankTransfer->getTerritory())) ? $BankTransfer->getTerritory()->getId() : null;
            $line[6] = (!is_null($BankTransfer->getCarpoolProof())) ? $BankTransfer->getCarpoolProof()->getId() : null;
            $details = json_decode($BankTransfer->getDetails(), true);
            if (is_array($details)) {
                foreach ($details as $detail) {
                    $line[] = $detail;
                }
            }
            fputcsv($file, $line, self::CSV_DELIMITER);
        }
        fclose($file);
    }

    private function _getTransferts()
    {
        if (!$this->_BankTransfers = $this->_BankTransferRepository->findBy(['batchId' => $this->_batchId])) {
            $this->_logger->error('[BatchId : '.$this->_batchId.'] '.BankTransferException::SUMMARIZER_NO_TRANSFERT_FOR_THIS_BATCH_ID);
        }
    }

    private function _sendEmail()
    {
        $email = new Email();
        if (0 == count($this->_emailRecipients)) {
            throw new BankTransferException(BankTransferException::NO_REPORT_RECIPIENTS);
        }
        $email->setRecipientEmail($this->_emailRecipients[0]);

        if (count($this->_emailRecipients) > 1) {
            $email->setRecipientEmailCc(array_slice($this->_emailRecipients, 1));
        }

        $titleTemplate = $this->_communicationFolder.self::EMAIL_LANGUAGE.$this->_emailTitleTemplatePath.self::EMAIL_TEMPLATE.'.html.twig';
        $email->setObject($this->_templating->render($titleTemplate));

        $bodyContext = [];
        $attachements = [self::PATH_TO_FILES.'/'.$this->_batchId.'.'.self::FILES_EXTENTION];
        $this->_emailManager->send($email, $this->_communicationFolder.self::EMAIL_LANGUAGE.$this->_emailTemplatePath.self::EMAIL_TEMPLATE, $bodyContext, self::EMAIL_LANGUAGE, $attachements);
    }
}
