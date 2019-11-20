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

namespace App\Carpool\DataProvider;

use ApiPlatform\Core\DataProvider\CollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Carpool\Service\ProposalManager;
use App\Carpool\Entity\Proposal;
use App\Geography\Entity\Address;

/**
 * Collection data provider for Matching simple search.
 * Only for punctual and one way trip.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
final class ProposalSimpleSearchCollectionDataProvider implements CollectionDataProviderInterface, RestrictedDataProviderInterface
{
    protected $request;
    
    public function __construct(RequestStack $requestStack, ProposalManager $proposalManager)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->proposalManager = $proposalManager;
    }
    
    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Proposal::class === $resourceClass && $operationName === "simple_search";
    }
    
    public function getCollection(string $resourceClass, string $operationName = null): ?array
    {
        $origin = $this->request->get("origin");
        $destination = $this->request->get("destination");
        $originAddress = new Address();
        $destinationAddress = new Address();
        if (isset($origin['houseNumber'])) {
            $originAddress->setHouseNumber($origin['houseNumber']);
        }
        if (isset($origin['street'])) {
            $originAddress->setStreet($origin['street']);
        }
        if (isset($origin['streetAddress'])) {
            $originAddress->setStreetAddress($origin['streetAddress']);
        }
        if (isset($origin['postalCode'])) {
            $originAddress->setPostalCode($origin['postalCode']);
        }
        if (isset($origin['subLocality'])) {
            $originAddress->setSubLocality($origin['subLocality']);
        }
        if (isset($origin['addressLocality'])) {
            $originAddress->setAddressLocality($origin['addressLocality']);
        }
        if (isset($origin['localAdmin'])) {
            $originAddress->setLocalAdmin($origin['localAdmin']);
        }
        if (isset($origin['county'])) {
            $originAddress->setCounty($origin['county']);
        }
        if (isset($origin['macroCounty'])) {
            $originAddress->setMacroCounty($origin['macroCounty']);
        }
        if (isset($origin['region'])) {
            $originAddress->setRegion($origin['region']);
        }
        if (isset($origin['macroRegion'])) {
            $originAddress->setMacroRegion($origin['macroRegion']);
        }
        if (isset($origin['addressCountry'])) {
            $originAddress->setAddressCountry($origin['addressCountry']);
        }
        if (isset($origin['countryCode'])) {
            $originAddress->setCountryCode($origin['countryCode']);
        }
        if (isset($origin['latitude'])) {
            $originAddress->setLatitude($origin['latitude']);
        }
        if (isset($origin['longitude'])) {
            $originAddress->setLongitude($origin['longitude']);
        }
        if (isset($origin['elevation'])) {
            $originAddress->setElevation($origin['elevation']);
        }
        if (isset($origin['name'])) {
            $originAddress->setName($origin['name']);
        }
        if (isset($origin['venue'])) {
            $originAddress->setVenue($origin['venue']);
        }
        if (isset($origin['home'])) {
            $originAddress->setHome($origin['home']);
        }
        if (isset($origin['displayLabel'])) {
            $originAddress->setDisplayLabel($origin['displayLabel']);
        }
        if (isset($destination['houseNumber'])) {
            $destinationAddress->setHouseNumber($destination['houseNumber']);
        }
        if (isset($destination['street'])) {
            $destinationAddress->setStreet($destination['street']);
        }
        if (isset($destination['streetAddress'])) {
            $destinationAddress->setStreetAddress($destination['streetAddress']);
        }
        if (isset($destination['postalCode'])) {
            $destinationAddress->setPostalCode($destination['postalCode']);
        }
        if (isset($destination['subLocality'])) {
            $destinationAddress->setSubLocality($destination['subLocality']);
        }
        if (isset($destination['addressLocality'])) {
            $destinationAddress->setAddressLocality($destination['addressLocality']);
        }
        if (isset($destination['localAdmin'])) {
            $destinationAddress->setLocalAdmin($destination['localAdmin']);
        }
        if (isset($destination['county'])) {
            $destinationAddress->setCounty($destination['county']);
        }
        if (isset($destination['macroCounty'])) {
            $destinationAddress->setMacroCounty($destination['macroCounty']);
        }
        if (isset($destination['region'])) {
            $destinationAddress->setRegion($destination['region']);
        }
        if (isset($destination['macroRegion'])) {
            $destinationAddress->setMacroRegion($destination['macroRegion']);
        }
        if (isset($destination['addressCountry'])) {
            $destinationAddress->setAddressCountry($destination['addressCountry']);
        }
        if (isset($destination['countryCode'])) {
            $destinationAddress->setCountryCode($destination['countryCode']);
        }
        if (isset($destination['latitude'])) {
            $destinationAddress->setLatitude($destination['latitude']);
        }
        if (isset($destination['longitude'])) {
            $destinationAddress->setLongitude($destination['longitude']);
        }
        if (isset($destination['elevation'])) {
            $destinationAddress->setElevation($destination['elevation']);
        }
        if (isset($destination['name'])) {
            $destinationAddress->setName($destination['name']);
        }
        if (isset($destination['venue'])) {
            $destinationAddress->setVenue($destination['venue']);
        }
        if (isset($destination['home'])) {
            $destinationAddress->setHome($destination['home']);
        }
        if (isset($destination['displayLabel'])) {
            $destinationAddress->setDisplayLabel($destination['displayLabel']);
        }
        return [$this->proposalManager->orderResultsBy($this->proposalManager->searchMatchings(
            $originAddress,
            $destinationAddress,
            $this->request->get("frequency"),
            \DateTime::createFromFormat(\DateTime::RFC3339, $this->request->get("date")),
            $this->request->get("useTime"),
            $this->request->get("strictDate"),
            $this->request->get("strictPunctual"),
            $this->request->get("strictRegular"),
            $this->request->get("marginTime"),
            $this->request->get("regularLifeTime"),
            $this->request->get("userId"),
            $this->request->get("role"),
            $this->request->get("type"),
            $this->request->get("anyRouteAsPassenger"),
            $this->request->get("communityId")
        ))];
    }
}
