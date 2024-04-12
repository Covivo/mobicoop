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
 */

namespace App\Geography\Service;

use App\Action\Event\ActionEvent;
use App\Action\Repository\ActionRepository;
use App\Geography\Entity\Address;
use App\Geography\Repository\AddressRepository;
use App\Geography\Repository\TerritoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Address management service.
 *
 * @author Sylvain Briat <sylvain.briat@mobicoop.org>
 */
class AddressManager
{
    private $entityManager;
    private $territoryRepository;
    private $addressRepository;
    private $geoSearcher;
    private $logger;
    private $actionRepository;
    private $eventDispatcher;
    private $geoTools;

    /**
     * Constructor.
     */
    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, TerritoryRepository $territoryRepository, AddressRepository $addressRepository, GeoSearcher $geoSearcher, ActionRepository $actionRepository, EventDispatcherInterface $eventDispatcher, GeoTools $geoTools)
    {
        $this->entityManager = $entityManager;
        $this->territoryRepository = $territoryRepository;
        $this->addressRepository = $addressRepository;
        $this->geoSearcher = $geoSearcher;
        $this->logger = $logger;
        $this->actionRepository = $actionRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->geoTools = $geoTools;
    }

    /**
     * Create territories for an Address.
     *
     * @param Address $address The address
     *
     * @return Address The address with its territories
     */
    public function createAddressTerritories(Address $address)
    {
        //$this->logger->info('Address Manager | Create address territories for Address #' . $address->getId() . ' | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        // first we check that the address is not linked yet to territories
        if (0 == count($address->getTerritories())) {
            // we search the territories
            if ($territories = $this->territoryRepository->findAddressTerritories($address)) {
                foreach ($territories as $territory) {
                    $address->addTerritory($territory);
                }
            }
        }

        return $address;
    }

    /**
     * Update territories for an Address.
     *
     * @param Address $address The address
     *
     * @return Address The address with its territories
     */
    public function updateAddressTerritories(Address $address)
    {
        //$this->logger->info('Address Manager | Update address territories for Address #' . $address->getId() . ' | ' . (new \DateTime("UTC"))->format("Ymd H:i:s.u"));
        // first we remove all territories
        $address->removeTerritories();
        // then we search the territories
        if ($territories = $this->territoryRepository->findAddressTerritories($address)) {
            foreach ($territories as $territory) {
                $address->addTerritory($territory);
            }
        }

        return $address;
    }

    /**
     * Create territories for an Address, only if the address is directly related to 'useful' entities :
     * - user (home)
     * - community
     * - event
     * - relay point
     * - proposal waypoint
     * - todo : add useful entities.
     *
     * @param Address $address The address
     *
     * @return Address The address (with its territories if needed)
     */
    public function createAddressTerritoriesForUsefulEntity(Address $address)
    {
        $createLink = false;
        if ($address->isHome()) {
            // home address
            $createLink = true;
        } elseif (!is_null($address->getCommunity())) {
            // community
            $createLink = true;
        } elseif (!is_null($address->getEvent())) {
            // event
            $createLink = true;
        } elseif (!is_null($address->getRelayPoint())) {
            // relay point
            $createLink = true;
        } elseif (!is_null($address->getWaypoint())) {
            // proposal waypoint
            if (!is_null($address->getWaypoint()->getProposal())) {
                $createLink = true;
            }
        }
        // todo : add any needed useful entity link
        if ($createLink) {
            return $this->createAddressTerritories($address);
        }

        return $address;
    }

    /**
     * Complete minimal addresses by reverse geocoding.
     */
    public function completeMinimalAddresses()
    {
        // first we search all addresses that have only latitude and longitude filled
        if ($addresses = $this->addressRepository->findMinimalAddresses()) {
            foreach ($addresses as $address) {
                $address = $this->reverseGeocodeAddress($address);
                $this->entityManager->persist($address);
            }
            $this->entityManager->flush();
        }
    }

    /**
     * Reverse geocoding on a partial Address (using Lat/Lon).
     *
     * @param Address $address Address to complete
     *
     * @return Address Completed address
     */
    public function reverseGeocodeAddress(Address $address): Address
    {
        $reversedGeocodeAddress = null;
        if ($foundAddresses = $this->geoSearcher->reverseGeoCode($address->getLatitude(), $address->getLongitude())) {
            $reversedGeocodeAddress = $foundAddresses[0];
        }
        if (!is_null($reversedGeocodeAddress)) {
            $address->setLayer($reversedGeocodeAddress->getLayer());
            $address->setStreetAddress($reversedGeocodeAddress->getStreetAddress());
            $address->setPostalCode($reversedGeocodeAddress->getPostalCode());
            $address->setAddressLocality($reversedGeocodeAddress->getAddressLocality());
            $address->setAddressCountry($reversedGeocodeAddress->getAddressCountry());
            $address->setElevation($reversedGeocodeAddress->getElevation());
            $address->setHouseNumber($reversedGeocodeAddress->getHouseNumber());
            $address->setStreet($reversedGeocodeAddress->getStreet());
            $address->setSubLocality($reversedGeocodeAddress->getSubLocality());
            $address->setLocalAdmin($reversedGeocodeAddress->getLocalAdmin());
            $address->setCounty($reversedGeocodeAddress->getCounty());
            $address->setMacroCounty($reversedGeocodeAddress->getMacroCounty());
            $address->setRegion($reversedGeocodeAddress->getRegion());
            $address->setMacroRegion($reversedGeocodeAddress->getMacroRegion());
            $address->setCountryCode($reversedGeocodeAddress->getCountryCode());
            $address->setVenue($reversedGeocodeAddress->getVenue());
        }

        return $address;
    }

    /**
     * Create an Address.
     *
     * @param Address $address The address
     *
     * @return Address The address
     */
    public function createAddress(Address $address)
    {
        $this->entityManager->persist($address);
        $this->entityManager->flush();

        if ($address->isHome()) {
            //  we dispatch the gamification event associated
            $action = $this->actionRepository->findOneBy(['name' => 'user_home_address_updated']);
            $actionEvent = new ActionEvent($action, $address->getUser());
            $this->eventDispatcher->dispatch($actionEvent, ActionEvent::NAME);
        }

        return $address;
    }

    /**
     * Update an address.
     *
     * @param Address $address The address data used to update the address
     *
     * @return Address The address updated
     */
    public function updateAddress(Address $address)
    {
        $this->entityManager->persist($address);
        $this->entityManager->flush();

        $address->setDisplayLabel($this->geoTools->getDisplayLabel($address, $address->getUser()));

        if ($address->isHome()) {
            //  we dispatch the gamification event associated
            $action = $this->actionRepository->findOneBy(['name' => 'user_home_address_updated']);
            $actionEvent = new ActionEvent($action, $address->getUser());
            $this->eventDispatcher->dispatch($actionEvent, ActionEvent::NAME);
        }

        return $address;
    }
}
