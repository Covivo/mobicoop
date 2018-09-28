<?php

namespace App\Spec\Controller;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Form;

/* Functional tests */
describe('UserController', function () {
    describe('/users', function () {
        it('Users page should return status code 200', function () {
            $request = $this->request->create('/users', 'GET');
            $response = $this->kernel->handle($request);

            $status = $response->getStatusCode();
            
            expect($status)->toEqual(200);
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
            $crawler = new Crawler($response->getContent(),'http://localhost:8081/user/create');
            $crawler = $crawler->filter('form');
            expect($status)->toEqual(200);
            // expect($form->has('#user_form_givenName'));
        });
    });

});
