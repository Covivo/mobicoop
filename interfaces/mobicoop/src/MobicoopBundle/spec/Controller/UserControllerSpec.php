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
const LOCAL_URL = 'http://'.LOCAL_IP.':'.LOCAL_PORT;

/* Functional tests */
describe('UserController', function () {
    // Given is used to save variables in context👌
    // given('client',function(){
    //     return new Client();
    // });
    describe('/users', function () {
        it('Users page should return status code 200', function () {
            $request = $this->request->create('/users', 'GET');
            $response = $this->kernel->handle($request);
            $status = $response->getStatusCode();

            expect($status)->toEqual(200);
        });
        it('Should be able to reference /users page from home page', function () {
            $request = $this->request->create('/', 'GET');
            $response = $this->kernel->handle($request);
            $status = $response->getStatusCode();
            $crawler = new Crawler($response->getContent(), LOCAL_URL);
            $link = $crawler->selectLink('Profile')->link();
            $uri = $link->getUri();

            expect($uri)->toEqual(LOCAL_URL . '/users');
            expect($uri)->not->toEqual(LOCAL_URL . '/random');
        });
        it('Should be able to access /users pages with a client', function () {
            $realCrawler = $this->client->request('GET', LOCAL_URL . '/users');
            $link = $realCrawler->selectLink('Profile')->link();
            $realCrawler = $this->client->click($link);
            $h1 = $realCrawler->filter('h1.title')->text();
            // $this->http->takeScreenshot('screen.png'); //I let this here for an example 🤪

            expect($realCrawler->getUri())->toBe(LOCAL_URL.'/users');
            expect(trim($h1))->toBe('Mobicoop - Users');
        });
        //This test is used for functionnal with a real navigator tests
        it('Should be able to really access /users pages with a navigator', function () {
            $realCrawler = $this->panther->request('GET', LOCAL_URL . '/users');
            $accountMenu = $realCrawler->filter('#accountMenu')->click();
            $link = $realCrawler->selectLink('Profile')->link();
            $realCrawler = $this->panther->click($link);
            $h1 = $realCrawler->filter('h1.title')->text();
            // $this->panther->takeScreenshot('screen.png'); //I let this here for an example 🤪

            expect($realCrawler->getUri())->toBe(LOCAL_URL.'/users');
            expect(trim($h1))->toBe('Mobicoop - Users');
        });
    });
    describe('/user', function () {
        it('User page without id should return status code 404', function () {
            $request = $this->request->create('/user', 'GET');
            $response = $this->kernel->handle($request);
            $status = $response->getStatusCode();

            expect($status)->toEqual(404);
        });
    });
    describe('/user/create', function () {
        it('User create page should return status code 200 and contains a givenName form input', function () {
            $request = $this->request->create('/user/create', 'GET');
            $response = $this->kernel->handle($request);
            $status = $response->getStatusCode();
            $crawler = new Crawler($response->getContent(), LOCAL_URL.'/user/create');
            $form = $crawler->filter('form')->form();

            expect($form->has('user_form[givenName]'))->toBe(true);
        });
    });
});
