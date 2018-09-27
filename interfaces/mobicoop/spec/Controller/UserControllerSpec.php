<?php

namespace App\Spec\Controller;

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
});
