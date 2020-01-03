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

namespace Mobicoop\Bundle\MobicoopBundle\Import\Service;

use Mobicoop\Bundle\MobicoopBundle\Api\Service\DataProvider;
use Mobicoop\Bundle\MobicoopBundle\Import\Entity\Redirect;

/**
 * Redirect management service.
 */
class RedirectManager
{
    private $dataProvider;
    
    /**
     * Constructor.
     *
     * @param DataProvider $dataProvider
     */
    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
        $this->dataProvider->setClass(Redirect::class);
    }

    /**
     * Get a redirection by its uri
     *
     * @param string $originUri The original URI
     *
     * @return Redirect|null The redirection found or null if not found.
     */
    public function getRedirect(string $originUri)
    {
        $response = $this->dataProvider->getCollection(['originUri'=>$originUri]);
        if ($response->getCode() == 200) {
            $redirect = $response->getValue()->getMember();
            if (is_array($redirect) && count($redirect) == 1) {
                // we return the first (and normally only) result
                return $redirect[0];
            }
        }
        return null;
    }
}
