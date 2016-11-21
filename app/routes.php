<?php

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WemsCA\RequestCert\RecordDetails\DetailRecorderContract;

$route->post('/request-cert', function (ServerRequestInterface $request, ResponseInterface $response) use ($container) {

    $handler = new \WemsCA\Handler\RequestCert($request, $response, $container->get('logger'));
    $handler->setDetailRecorder($container->get(DetailRecorderContract::class));

    $config = $container->get('config');

    // we require a CA path so let's check for that before we go any further
    if (!isset($config['ca_path'])) {
        throw new \WemsCA\RequestCert\InvalidConfigurationException('The config MUST have a ca_path defined');
    }

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

    if (!empty($config['permissions'])) {
        $parameters->setPermissions($config['permissions']);
    }

    if (!empty($config['certificate_identity'])) {
        $parameters->setCertificateIdentity($config['certificate_identity']);
    }

    $handler->handle($parameters);

});
