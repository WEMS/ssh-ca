<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$route->post('/request-cert', function (ServerRequestInterface $request, ResponseInterface $response) use ($container) {

    $handler = new \WemsCA\Handler\RequestCert($request, $response, $container->get('logger'));

    $config = $container->get('config');

    $parameters = new \WemsCA\RequestCert\RequestCertParameters($config['ca_path']);

    if (!empty($config['tmp_dir'])) {
        $parameters->setTmpDir($config['tmp_dir']);
    }

    if (!empty($config['default_expiry'])) {
        $parameters->setDefaultExpiry($config['default_expiry']);
    }

    if (!empty($config['default_login_user'])) {
        $parameters->setDefaultLoginUser($config['default_login_user']);
    }

    $handler->handle($parameters);

});
