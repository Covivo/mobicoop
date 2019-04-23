<?php

/**
 * Copyright (c) 2018, MOBICOOP. All rights reserved.
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

namespace App\Match\Service;

use App\Match\Entity\Mass;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\FileManager;
use App\User\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use ProxyManager\Exception\FileNotWritableException;

/**
 * Mass import manager.
 *
 * This service contains methods related to mass matching file import.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 */
class MassImportManager
{
    private $userRepository;
    private $fileManager;
    private $logger;
    
    /**
     * Constructor.
     *
     * @param FileManager $fileManager
     * @param LoggerInterface $logger
     */
    public function __construct(UserRepository $userRepository, FileManager $fileManager, LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->fileManager = $fileManager;
        $this->logger = $logger;
    }
    
    /**
     * Get the owner of the file.
     * @param Mass $mass
     * @throws OwnerNotFoundException
     * @return object
     */
    public function getUser(Mass $mass): object
    {
        if (!is_null($mass->getUserId())) {
            return $this->userRepository->find($mass->getUserId());
        }
        throw new OwnerNotFoundException('The owner of this file cannot be found');
    }
}
