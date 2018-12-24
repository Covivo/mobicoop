<?php // kahlan-config.php 

use App\Kernel;
use Kahlan\Filter\Filters;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Client;
use Symfony\Component\Panther\PantherTestCaseTrait;
use Kahlan\Reporter\Coverage;
use Kahlan\Reporter\Coverage\Driver\Xdebug;

//Filter folders in code coverage report
Filters::apply($this, 'coverage', function($next) {
    if (!extension_loaded('xdebug')) {
        return;
    }
    $reporters = $this->reporters();
    $coverage = new Coverage([
        'verbosity' => 4,
        //'verbosity' => $this->commandLine()->get('coverage'),
        'driver'    => new Xdebug(),
        'path'      => $this->commandLine()->get('src'),
        'exclude'   => [
            'src/MobicoopBundle/Entity/*',
            /* Provider (disabled until we are able to launch api in gitlab-ci) */
            'src/MobicoopBundle/Service/DataProvider.php',
            /* Manager (disabled until we are able to launch api in gitlab-ci) */
            'src/MobicoopBundle/Service/ExternalJourneyManager.php',
            'src/MobicoopBundle/Service/GeoSearchManager.php',
            'src/MobicoopBundle/Service/ProposalManager.php',
            'src/MobicoopBundle/Service/PublicTransportManager.php',
            'src/MobicoopBundle/Service/UserManager.php',
        ],/*'include'   => [
            'src/MobicoopBundle/Service/*',
        ],*/
        'colors'    => !$this->commandLine()->get('no-colors')
    ]);
    $reporters->add('coverage', $coverage);
});


// Use a panther trait  & add a public class to access protected method outside
class ExtendedPanther {
    use PantherTestCaseTrait;
    static public function createWebServer(){

        return self::createPantherClient('127.0.0.1',4242);

    }
}

Filters::apply($this, 'bootstrap', function($next) {

    require __DIR__ . '/vendor/autoload.php';

    $root = $this->suite()->root();

    $root->beforeAll(function () {
        (new Dotenv())->load(__DIR__.'/.env');
        $env = $_SERVER['APP_ENV'] ?? 'dev';
        $this->request = Request::createFromGlobals();
        $this->kernel  = new Kernel('test', false);
        $panther = new ExtendedPanther();
        $client = new Client($this->kernel);
        // Create webserver for functionnals advanced test
        $this->panther = $panther::createWebServer();
        $this->client = $client;
    });

    return $next();

});