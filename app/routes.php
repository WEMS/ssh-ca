<?php

use WemsCA\Controller;

/** @var \Zend\Expressive\Application $app */

$app->pipe('/request-cert', \Psr7Middlewares\Middleware\ClientIp::class);
$app->pipe('/request-cert', $container->get(\Psr7Middlewares\Middleware\Firewall::class));
$app->post('/request-cert', [$container->get(Controller\RequestCertController::class), 'requestCert']);
