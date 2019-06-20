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

namespace Mobicoop\Bundle\MobicoopBundle\Spec\Controller;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

const LOCAL_PORT = '4242';
const LOCAL_IP = '127.0.0.1';
const LOCAL_URL = 'http://' . LOCAL_IP . ':' . LOCAL_PORT;

/* Functional tests */
describe('UserController', function () {
    // Given is used to save variables in context👌
    // given('client',function(){
    //     return new Client();
    // });
    it('User page without id should return status code 404', function () {
        $request = $this->request->create('/user', 'GET');
        $response = $this->kernel->handle($request);
        $status = $response->getStatusCode();

        expect($status)->toEqual(404);
    });
    // the form is now in vueJs so it's no more possible to test inputs
    it('User sign up page should return status code 200', function () {
        $request = $this->request->create('/user/signup', 'GET');
        $response = $this->kernel->handle($request);
        $status = $response->getStatusCode();
        $crawler = new Crawler($response->getContent(), LOCAL_URL . '/user/signup');
        $form = $crawler->filter('form')->form();

        expect($status)->toEqual(200);
    });
});
