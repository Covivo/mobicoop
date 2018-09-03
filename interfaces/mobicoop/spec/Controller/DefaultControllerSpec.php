<?php 

namespace App\Spec\Controller;
use Symfony\Component\DomCrawler\Crawler;

/* This is a sample functionnal Test */
describe('DefaultController', function () {
    describe('/', function () {
        it('Index page should return status code 200 & contains "hello" in a h1', function () {
            $request = $this->request->create('/', 'GET');
            $response = $this->kernel->handle($request);

            $status = $response->getStatusCode();
            $crawler = new Crawler($response->getContent());
            $h1 = trim($crawler->filter('body h1')->text());
            $splitedH1 = explode('Hello',$h1);
            $nb = $splitedH1[1];


            expect($status)->toEqual(200);
            expect($h1)->toContain('Hello');
            expect($splitedH1)->toHaveLength(2);
            expect($nb)->toBeGreaterThan(0);
            expect($nb)->toBeLessThan(26);
            expect($h1)->not->toContain('gloups');
        });
    });
});


 ?>