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

namespace App\DataProvider\Interfaces;

/**
 * Provider interface.
 *
 * A provider entity class must implement all these methods in order to retrieve data and populate entities.
 *
 * @author Sylvain Briat <sylvain.briat@covivo.eu>
 *
 */
interface ProviderInterface
{
    /**
     * Returns a collection of items.
     *
     * @param string $class     The name of the class associated with the collection.
     * @param string $apikey    The apikey used for the provider.
     * @param array $params     The params to send to the request.
     */
    public function getCollection(string $class, string $apikey, array $params);

    /**
     * Returns a single item.
     *
     * @param string $class     The name of the class associated with the item.
     * @param string $apikey    The apikey used for the provider.
     * @param array $params     The params to send to the request.
     */
    public function getItem(string $class, string $apikey, array $params);

    /**
     * Deserializes the data returned by the provider.
     *
     * @param string $class     The name of the class to deserialize.
     * @param array $data       The data to deserialize.
     */
    public function deserialize(string $class, array $data);
}
