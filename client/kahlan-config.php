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
            /* Kernel Symfony */
            'src/App/Kernel/*',
            /* Entities */
            'src/MobicoopBundle/Entity/*',
            'src/MobicoopBundle/Api/Entity/*',
            'src/MobicoopBundle/Carpool/Entity/*',
            'src/MobicoopBundle/ExternalJourney/Entity/*',
            'src/MobicoopBundle/Geography/Entity/*',
            'src/MobicoopBundle/JsonLD/Entity/*',
            'src/MobicoopBundle/PublicTransport/Entity/*',
            'src/MobicoopBundle/Travel/Entity/*',
            'src/MobicoopBundle/User/Entity/*',
            /* Form (disabled until we are able to launch api in gitlab-ci) */
            'src/MobicoopBundle/Form/*',
            'src/MobicoopBundle/Carpool/Form/*',
            'src/MobicoopBundle/Geography/Form/*',
            'src/MobicoopBundle/User/Form/*',
            /* Services (disabled until we are able to launch api in gitlab-ci) */
            'src/MobicoopBundle/Api/Service/DataProvider.php',
            'src/MobicoopBundle/Api/Service/JwtManager.php',
            'src/MobicoopBundle/Carpool/Service/ProposalManager.php',
            'src/MobicoopBundle/ExternalJourney/Service/ExternalJourneyManager.php',
            'src/MobicoopBundle/Geography/Service/GeoSearchManager.php',
            'src/MobicoopBundle/PublicTransport/Service/PublicTransportManager.php',
            'src/MobicoopBundle/User/Service/UserManager.php',
        ],'include'   => [
            /* Controllers */
            'src/Controller/*',
            'src/MobicoopBundle/Controller/*',
            'src/MobicoopBundle/User/Controller/*',
            /* Services */
            'src/MobicoopBundle/Api/Service/*',
            'src/MobicoopBundle/Carpool/Service/*',
            'src/MobicoopBundle/ExternalJourney/Service/*',
            'src/MobicoopBundle/Geography/Service/*',
            'src/MobicoopBundle/PublicTransport/Service/*',
            'src/MobicoopBundle/User/Service/*',            
        ],
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