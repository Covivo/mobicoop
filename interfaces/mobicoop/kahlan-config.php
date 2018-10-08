<?php // kahlan-config.php 

use App\Kernel;
use Kahlan\Filter\Filters;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Client;
//use Symfony\Component\
use Symfony\Component\Panther\PantherTestCaseTrait;

// Use use a panther trait  & add a public class to access protected method outside
class ExtendedPanther {
    use PantherTestCaseTrait;
    static public function createWebServer(){
        var_dump('BEFORE BUG');

        return self::createPantherClient('127.0.0.1',4242);

    }
}

Filters::apply($this, 'bootstrap', function($next) {

    require __DIR__ . '/vendor/autoload.php';

    var_dump('should passed here');

    $root = $this->suite()->root();

    $root->beforeAll(function () {
        (new Dotenv())->load(__DIR__.'/.env');
        $env = $_SERVER['APP_ENV'] ?? 'dev';
        $this->request = Request::createFromGlobals();
        $this->kernel  = new Kernel('test', false);
        $panther = new ExtendedPanther();
        //$client = new Client($this->kernel);
        // Create webserver for functionnals advanced test
        $this->panther = $panther::createWebServer();
        // $this->client = $client;
    });

    return $next();

});