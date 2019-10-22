<?php

/**
 * Copyright (c) 2019, MOBICOOP. All rights reserved.
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


namespace Mobicoop\Bundle\MobicoopBundle\Solidary\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Solidary\Entity\Structure;
use Psr\Log\LoggerInterface;

class StructureManager
{
    private $dataProvider;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     * @param LoggerInterface $logger
     * @throws \ReflectionException
     */
    public function __construct(DataProvider $dataProvider, LoggerInterface $logger)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Structure::class);
        $this->logger = $logger;
    }

    /**
     * Get all structures
     * @return array|null        The structures found or null if not found.
     */
    public function getStructures()
    {
        $response = $this->dataProvider->getCollection();
        if ($response->getCode() >=200 && $response->getCode() <= 300) {
            return $response->getValue()->getMember();
        }
        return $response->getValue();
    }

    /**
     * Get a structure by its id
     *
     * @param int $id
     * @return array|null|object
     */
    public function getStructure(int $id)
    {
        $response = $this->dataProvider->getItem($id);
        if ($response->getCode() == 200) {
            $structure = $response->getValue();
            $this->logger->info('User | Is found');
            return $structure;
        }
        $this->logger->error('User | is Not found');
        return null;
    }
}
