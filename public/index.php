<?php

use Zend\Expressive\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/container.php';

$app = AppFactory::create($container);

require __DIR__ . '/../app/routes.php';

$app->pipeRoutingMiddleware();
$app->pipeDispatchMiddleware();
$app->run();
