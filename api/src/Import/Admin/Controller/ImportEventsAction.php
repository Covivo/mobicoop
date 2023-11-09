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
use Symfony\Component\HttpFoundation\File\File;

/**
 * @author Rémi Wortemann <remi.wortemann@mobicoop.org>
 */
final class ImportEventsAction
{
    private const LOCAL_FILE_PATH = __DIR__.'/../../../../public/import/Event/eventSdis.csv';
    private const FILE_NAME = 'eventsToImport.csv';
    private $eventProviderServerIP;
    private $importManager;
    private $_ftpDownloader;
    private $eventRemoteFilePath;
    private $eventFtpLogin;
    private $eventFtpPassword;

    /**
     * Constructor.
     */
    public function __construct(
        ImportManager $importManager,
        AppRepository $appRepository,
        string $eventProviderServerIP,
        string $eventRemoteFilePath,
        string $eventFtpLogin,
        string $eventFtpPassword
    ) {
        $this->importManager = $importManager;
        $this->appRepository = $appRepository;
        $this->_ftpDownloader = null;
        $this->eventProviderServerIP = $eventProviderServerIP;
        $this->eventRemoteFilePath = $eventRemoteFilePath;
        $this->eventFtpLogin = $eventFtpLogin;
        $this->eventFtpPassword = $eventFtpPassword;
    }

    public function importEventsFromFile()
    {
        $this->_ftpDownloader = new FtpDownloader(
            $this->eventProviderServerIP,
            $this->eventFtpLogin,
            $this->eventFtpPassword,
            $this->eventRemoteFilePath,
            self::LOCAL_FILE_PATH
        );
        $this->_ftpDownloader->download();

        $importer = new Importer(new File(self::LOCAL_FILE_PATH), self::FILE_NAME, $this->importManager, null, $this->appRepository);

        return $importer->importEvents();
    }
}
