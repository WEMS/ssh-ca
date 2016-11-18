<?php

$config = file_get_contents(__DIR__ . '/../config/config.yml');
$parsedConfig = Symfony\Component\Yaml\Yaml::parse($config);

$container = new League\Container\Container;

$container->share('response', Zend\Diactoros\Response::class);
$container->share('request', function () {
    return Zend\Diactoros\ServerRequestFactory::fromGlobals(
        $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
    );
});

$container->share('emitter', Zend\Diactoros\Response\SapiEmitter::class);

$container->share('config', $parsedConfig);

$logLevel = isset($parsedConfig['log_level']) ? $parsedConfig['log_level'] : \Psr\Log\LogLevel::NOTICE;

$log = new \Monolog\Logger('ca_signer');
$log->pushHandler(
    new \Monolog\Handler\StreamHandler(
        __DIR__ . '/../log/ca_signer.log',
        \Monolog\Logger::toMonologLevel($logLevel)
    )
);

$container->share('logger', $log);
