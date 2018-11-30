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

namespace App\Geography\Controller;

use Symfony\Component\HttpFoundation\RequestStack;

use App\Address\Entity\Address;
use Geocoder\Plugin\PluginProvider;
use Geocoder\Query\GeocodeQuery;

/**
 * GeoSearchController.php
 * Controller that requests a provider list
 * @author Sofiane Belaribi <sofiane.belaribi@mobicoop.org>
 * Date: 16/11/2018
 * Time: 9:25
 *
 */
class GeoSearchController
{
    protected $request;
    protected $container;

    /**
     * GeoSearchController constructor.
     * @param RequestStack $requestStack
     * @param PluginProvider $chain
     */
    public function __construct(RequestStack $requestStack, PluginProvider $chain)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->container = $chain;
    }

    /**
     * This method is invoked when autocomplete function is called.
     * @param array $data
     * @return array
     * @throws \Geocoder\Exception\Exception
     */
    public function __invoke(array $data): array
    {
        $input = $this->request->get("input");
        $result= $this->container
            ->geocodeQuery(GeocodeQuery::create($input))->all();

        $resultArray = [];
        foreach ($result as $value){
            $address = new Address(1);
            $address->setLatitude($value->getCoordinates()->getLatitude());
            $address->setLongitude($value->getCoordinates()->getLongitude()) ;

            $streetNumber = $value->getStreetNumber();
            $streetName = $value->getStreetName();
            $address->setStreetAddress($streetNumber.' '.$streetName);
            $address->setAddressLocality($value->getLocality());
            $address->setPostalCode($value->getPostalCode());
            $address->setAddressCountry($value->getCountry()->getName());

            $resultArray[] = $address;
        }

        return $resultArray;
    }
}
