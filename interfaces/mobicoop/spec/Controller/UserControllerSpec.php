<?php

namespace App\Spec\Controller;
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
            $crawler = new Crawler($response->getContent(),LOCAL_URL);
            $link = $crawler->selectLink('Users')->link();
            $uri = $link->getUri();

            expect($uri)->toEqual(LOCAL_URL . '/users');
            expect($uri)->not->toEqual(LOCAL_URL . '/random');
        });
        it('Should be able to access /users pages with a client', function(){
            $realCrawler = $this->client->request('GET', LOCAL_URL . '/users');
            $link = $realCrawler->selectLink('Users')->link();
            $realCrawler = $this->client->click($link);
            $h1 = $realCrawler->filter('h1.title')->text();
            // $this->http->takeScreenshot('screen.png'); //I let this here for an exemple 🤪

            expect($realCrawler->getUri())->toBe(LOCAL_URL.'/users');
            expect(trim($h1))->toBe('Coviride Demo application - Users');
        });
        // This test is used for functionnal with a real navigator tests
        it('Should be able to really access /users pages with a navigator', function(){
            $realCrawler = $this->panther->request('GET', LOCAL_URL . '/users');
            $link = $realCrawler->selectLink('Users')->link();
            $realCrawler = $this->panther->click($link);
            $h1 = $realCrawler->filter('h1.title')->text();
            $this->panther->takeScreenshot('screen.png'); //I let this here for an exemple 🤪

            expect($realCrawler->getUri())->toBe(LOCAL_URL.'/users');
            expect(trim($h1))->toBe('Coviride Demo application - Users');
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
            $crawler = new Crawler($response->getContent(),LOCAL_URL.'/user/create');
            $form = $crawler->filter('form')->form();

            expect($form->has('user_form[givenName]'))->toBe(true);
        });
    });
});
