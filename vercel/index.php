<?php
use App\Kernel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

require_once __DIR__ . '/../vendor/autoload.php';

return function ($event, $context) {
    $request = Request::createFromGlobals();
    $kernel = new Kernel('prod', false);

    $response = $kernel->handle($request);
    $response->send();

    $kernel->terminate($request, $response);
};