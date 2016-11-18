<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/app/container.php';

$route = new League\Route\RouteCollection($container);

require __DIR__ . '/app/routes.php';

$response = $route->dispatch($container->get('request'), $container->get('response'));

$container->get('emitter')->emit($response);
