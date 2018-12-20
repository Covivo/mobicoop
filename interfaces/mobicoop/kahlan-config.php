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
use Kahlan\Reporter\Coverage\Exporter\Coveralls;
use Kahlan\Cli\CommandLine;
use Kahlan\Reporter\Coverage;
use Kahlan\Reporter\Coverage\Driver\Xdebug;
use Kahlan\Reporter\Coverage\Exporter\CodeClimate;

// Use a panther trait  & add a public class to access protected method outside
class ExtendedPanther {
    use PantherTestCaseTrait;
    static public function createWebServer(){

        return self::createPantherClient('127.0.0.1',4242);

    }
}

/*$commandLine = $this->commandLine();
$commandLine->option('coverage', 'default', 3);

Filters::apply($this, 'coverage', function($next) {
    if (!extension_loaded('xdebug')) {
        return;
    }
    $reporters = $this->reporters();
    $coverage = new Coverage([
        'verbosity' => $this->commandLine()->get('coverage'),
        'driver'    => new Xdebug(),
        'path'      => $this->commandLine()->get('src'),
        'exclude'   => [
            //Exclude init script
            'src/init.php',
            'src/functions.php',
            //Exclude Workflow from code coverage reporting
            'src/Cli/Kahlan.php',
            //Exclude coverage classes from code coverage reporting (don't know how to test the tester)
            'src/Reporter/Coverage/Collector.php',
            'src/Reporter/Coverage/Driver/Xdebug.php',
            'src/Reporter/Coverage/Driver/HHVM.php',
            'src/Reporter/Coverage/Driver/Phpdbg.php',
            //Exclude text based reporter classes from code coverage reporting (a bit useless)
            'src/Reporter/Dot.php',
            'src/Reporter/Bar.php',
            'src/Reporter/Verbose.php',
            'src/Reporter/Terminal.php',
            'src/Reporter/Reporter.php',
            'src/Reporter/Coverage.php',
            'src/Reporter/Json.php',
            'src/Reporter/Tap.php',
        ],
        'colors'    => !$this->commandLine()->get('no-colors')
    ]);
    $reporters->add('coverage', $coverage);
});*/

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