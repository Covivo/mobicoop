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

namespace Mobicoop\Bundle\MobicoopBundle\Controller;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

/**
 * AutoCompleteControllerSpec.php
 * Test Class for autocomplete Controller
 * @author Sofiane Belaribi <sofiane.belaribi@mobicoop.org>
 * Date: 19/12/2018
 * Time: 11:25
 *
 */

/* Functional tests */
describe('AutoComplete', function () {
    describe('/aut', function () {
        it('AutoComplete page should return status code 200', function () {
            $request = $this->request->create('/aut', 'GET');
            $response = $this->kernel->handle($request);
            $status = $response->getStatusCode();
            
            expect($status)->toEqual(200);
        });
    });
});
