<?php

use App\Kernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies), Request::HEADER_X_FORWARDED_ALL ^ Request::HEADER_X_FORWARDED_HOST);
}

if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? $_ENV['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();

if (file_exists('maintenance.enable')) {
    if ('OPTIONS' == $_SERVER['REQUEST_METHOD']) {
        header('Access-Control-Allow-Origin: *');
        header('HTTP/1.1 200 OK');
        header('Access-Control-Allow-Headers: Content-Type,Access-Control-Allow-Headers,Authorization');

        exit();
    }
    $response = new JsonResponse('API under maintenance', 503, ['Access-Control-Allow-Origin' => '*']);
} else {
    $response = $kernel->handle($request);
}

$response->send();
$kernel->terminate($request, $response);
