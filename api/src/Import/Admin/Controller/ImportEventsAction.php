<?php

/**
 * Copyright (c) 2023, MOBICOOP. All rights reserved.
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

namespace App\Import\Admin\Controller;

use App\App\Repository\AppRepository;
use App\Import\Admin\Service\Importer;
use App\Import\Admin\Service\ImportManager;
use App\Utility\Entity\FtpDownloader;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Rémi Wortemann <remi.wortemann@mobicoop.org>
 */
final class ImportEventsAction
{
    private const LOCAL_FILE_PATH = __DIR__.'/../../../../public/import/Event/eventSdis.csv';
    private const FILE_NAME = 'eventsToImport.csv';
    private $_eventProviderServerIP;
    private $_importManager;
    private $_ftpDownloader;
    private $_eventRemoteFilePath;
    private $_eventFtpLogin;
    private $_eventFtpPassword;
    private $_logger;
    private $_eventProvider;

    /**
     * Constructor.
     */
    public function __construct(
        ImportManager $importManager,
        AppRepository $appRepository,
        LoggerInterface $logger,
        string $eventProviderServerIP,
        string $eventRemoteFilePath,
        string $eventFtpLogin,
        string $eventFtpPassword,
        string $eventProvider
    ) {
        $this->_importManager = $importManager;
        $this->_appRepository = $appRepository;
        $this->_ftpDownloader = null;
        $this->_eventProviderServerIP = $eventProviderServerIP;
        $this->_eventRemoteFilePath = $eventRemoteFilePath;
        $this->_eventFtpLogin = $eventFtpLogin;
        $this->_eventFtpPassword = $eventFtpPassword;
        $this->_logger = $logger;
        $this->_eventProvider = $eventProvider;
    }

    public function importEventsFromFile()
    {
        $this->_ftpDownloader = new FtpDownloader(
            $this->_eventProviderServerIP,
            $this->_eventFtpLogin,
            $this->_eventFtpPassword,
            $this->_eventRemoteFilePath,
            self::LOCAL_FILE_PATH
        );
        $this->_ftpDownloader->download();

        $importer = new Importer(new File(self::LOCAL_FILE_PATH), self::FILE_NAME, $this->_importManager, null, $this->_appRepository, $this->_eventProvider);
        $importer->importEvents();
        $importer->deleteEvents();
        $errors = $importer->getErrors();
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                $this->_logger->info('ImportEventsAction : '.$error.' '.(new \DateTime('UTC'))->format('Ymd H:i:s.u'));
            }

            return false;
        }

        return true;
    }
}
