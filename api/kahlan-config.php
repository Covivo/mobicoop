<?php
 
use App\Kernel;
use Kahlan\Filter\Filters;
use Symfony\Component\HttpFoundation\Request;
 
Filters::apply($this, 'bootstrap', function($next) {
 
    require __DIR__ . '/vendor/autoload.php';
 
    $root = $this->suite()->root();
    $root->beforeAll(function () {
        $this->request = Request::createFromGlobals();
        $this->kernel  = new Kernel('test', false);
    });
 
    return $next();
 
});