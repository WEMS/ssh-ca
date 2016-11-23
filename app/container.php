<?php

use WemsCA\RequestCert\RecordDetails\DetailRecorderContract;
use WemsCA\RequestCert\RecordDetails\SqliteDetailRecorder;

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

$container->add('logger', function () use ($container) {

    $config = $container->get('config');

    $logLevel = isset($config['log_level']) ? $config['log_level'] : \Psr\Log\LogLevel::NOTICE;

    $log = new \Monolog\Logger('ca_signer');
    $log->pushHandler(
        new \Monolog\Handler\StreamHandler(
            __DIR__ . '/../log/ca_signer.log',
            \Monolog\Logger::toMonologLevel($logLevel)
        )
    );

    return $log;
});

$container->add('db', '\PDO')->withArgument('sqlite:' . __DIR__ . '/../db/ca-signer.db');

$container
    ->add(DetailRecorderContract::class, SqliteDetailRecorder::class)
    ->withArgument('db');

$container
    ->add(\WemsCA\Command\DatabaseCommand::class)
    ->withMethodCall('setDb', ['db'])
    ->withMethodCall('setDatabasePath', [__DIR__ . '/../db/schema.sql']);
