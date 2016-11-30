<?php

use League\Container\Container;
use WemsCA\RequestCert\RecordDetails\DetailRecorderContract;
use WemsCA\RequestCert\RecordDetails\PDODatabaseDetailRecorder;

$config = file_get_contents(__DIR__ . '/../config/config.yml');
$parsedConfig = Symfony\Component\Yaml\Yaml::parse($config);

$container = new Container;

// doesn't seem to be needed - left commented out for now
//$container->share(Zend\Expressive\Router\RouterInterface::class, \Zend\Expressive\Router\FastRouteRouter::class);

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
    ->add(DetailRecorderContract::class, PDODatabaseDetailRecorder::class)
    ->withArgument('db');

$container
    ->add(\WemsCA\Command\DatabaseCommand::class)
    ->withMethodCall('setDb', ['db'])
    ->withMethodCall('setDatabasePath', [__DIR__ . '/../db/schema.sql']);

// ip address filtering
$ipBlacklist = isset($parsedConfig['blacklist_ips']) ? $parsedConfig['blacklist_ips'] : [];

$ipWhitelist = [];

if (isset($parsedConfig['request_whitelist_ips'])) {
    $ipWhitelist = array_merge($ipWhitelist, $parsedConfig['request_whitelist_ips']);
}

if (isset($parsedConfig['ssh_whitelist_ips'])) {
    $ipWhitelist = array_merge($ipWhitelist, $parsedConfig['ssh_whitelist_ips']);
}

$container
    ->add(\Psr7Middlewares\Middleware\Firewall::class)
    ->withMethodCall('trusted', [$ipWhitelist])
    ->withMethodCall('untrusted', [$ipBlacklist]);
