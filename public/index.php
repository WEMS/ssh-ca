<?php

use Whoops\Handler\PlainTextHandler;
use Whoops\Run as Whoops;
use Zend\Expressive\Application;
use Zend\Expressive\Router\RouterInterface;
use Zend\Expressive\WhoopsErrorHandler;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/container.php';

$whoops = new Whoops();
$whoops->writeToOutput(false);
$whoops->allowQuit(false);

// @todo if Dev env
$handler = new PlainTextHandler();
$whoops->pushHandler($handler);

$whoops->pushHandler(function (Exception $exception, $inspector, $run) use ($container) {
    $container->get('logger')->addError($exception->getMessage() . PHP_EOL . $exception->getTraceAsString());
});

$finalHandler = new WhoopsErrorHandler($whoops);

$app = new Application($container->get(RouterInterface::class), $container, $finalHandler);

require __DIR__ . '/../app/routes.php';

$app->pipeRoutingMiddleware();
$app->pipeDispatchMiddleware();

// Register Whoops just before running the application, as otherwise it can swallow bootstrap errors.
$whoops->register();

$app->run();
