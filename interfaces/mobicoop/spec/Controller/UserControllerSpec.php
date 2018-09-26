<?php

namespace App\Spec\Controller;

use Symfony\Component\DomCrawler\Crawler;

/* This is a sample functionnal Test */
describe('UserController', function () {
    describe('/users', function () {
        it('Users page should return status code 200', function () {
            $request = $this->request->create('/users', 'GET');
            $response = $this->kernel->handle($request);

            $status = $response->getStatusCode();
            
            expect($status)->toEqual(200);
        });
    });
});
