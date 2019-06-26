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

namespace Mobicoop\Bundle\MobicoopBundle\Geography\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Geography\Entity\Address;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Psr\Log\LoggerInterface;

/**
 * Address management service.
 */
class AddressManager
{
    private $dataProvider;
    private $encoder;
    private $tokenStorage;
    private $logger;

    
    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     * @param UserPasswordEncoderInterface $encoder
     * @param TokenStorageInterface $tokenStorage
     * @param LoggerInterface $logger
     */
    public function __construct(DataProvider $dataProvider, UserPasswordEncoderInterface $encoder, TokenStorageInterface $tokenStorage, LoggerInterface $logger)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Address::class);
        $this->encoder = $encoder;
        $this->tokenStorage = $tokenStorage;
        $this->logger = $logger;
    }

    /**
     * Get a user by its identifier
     *
     * @param String $id The address id
     *
     * @return Addresss|null The address found or null if not found.
     */
    public function getAddress($id)
    {
        $response = $this->dataProvider->getItem($id);
        if ($response->getCode() == 200) {
            $address = $response->getValue();
            $this->logger->info('Address | Is found');
            return $address;
        }
        $this->logger->error('User | is Not found');
        return null;
    }

    
    /**
     * Update a Address
     *
     * @param Address $Address The address to update
     *
     * @return Address|null The address updated or null if error.
     */
    public function updateAddress(Address $address)
    {   
        $response = $this->dataProvider->put($address);
        if ($response->getCode() == 200) {
            $this->logger->info('Address Update | Start');
            return $response->getValue();
        }
        return null;
    }
}
