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

namespace App\Utility\Entity;

use phpseclib3\Net\SFTP;

/**
 * FTP file uploader.
 */
class FtpDownloader
{
    private $_serverUri;
    private $_login;
    private $_password;
    private $_remotePath;
    private $_localPath;

    public function __construct($serverUri, $login, $password, $remotePath = '/', $localPath = '/')
    {
        $this->_serverUri = $serverUri;
        $this->_login = $login;
        $this->_password = $password;
        $this->_remotePath = $remotePath;
        $this->_localPath = $localPath;
    }

    public function download()
    {
        $sftp = new SFTP($this->_serverUri);
        if (!$sftp->login($this->_login, $this->_password)) {
            throw new \Exception('Cannot login into your server !');
        }

        $file = fopen($this->_localPath, 'w+');
        fwrite($file, $sftp->get($this->_remotePath));
        fclose($file);
    }
}
