<?php

use League\Container\Container;
use Psr7Middlewares\Middleware\Firewall;
use WemsCA\Command\DatabaseCommand;
use WemsCA\RequestCert\RecordDetails\DetailRecorderContract;
use WemsCA\RequestCert\RecordDetails\PDODatabaseDetailRecorder;
use Zend\Expressive\Router\FastRouteRouter;
use Zend\Expressive\Router\RouterInterface;

$config = file_get_contents(__DIR__ . '/../config/config.yml');
$parsedConfig = Symfony\Component\Yaml\Yaml::parse($config);

// setup the IP address white and blacklists from the config
$ipBlacklist = isset($parsedConfig['blacklist_ips']) ? $parsedConfig['blacklist_ips'] : [];

$ipWhitelist = [];

if (isset($parsedConfig['request_whitelist_ips'])) {
    $ipWhitelist = array_merge($ipWhitelist, $parsedConfig['request_whitelist_ips']);
}

if (isset($parsedConfig['ssh_whitelist_ips'])) {
    $ipWhitelist = array_merge($ipWhitelist, $parsedConfig['ssh_whitelist_ips']);
}

$parsedConfig['ip_whitelist'] = $ipWhitelist;
$parsedConfig['ip_blacklist'] = $ipBlacklist;


$container = new Container;

$container->share(RouterInterface::class, FastRouteRouter::class);

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

$container->add('db', \PDO::class)->withArgument('sqlite:' . __DIR__ . '/../db/ca-signer.db');

$container
    ->add(DetailRecorderContract::class, PDODatabaseDetailRecorder::class)
    ->withArgument('db');

$container
    ->add(DatabaseCommand::class)
    ->withMethodCall('setDb', ['db'])
    ->withMethodCall('setDatabasePath', [__DIR__ . '/../db/schema.sql']);

// ip address filtering

$container
    ->add(Firewall::class)
    ->withMethodCall('trusted', [$ipWhitelist])
    ->withMethodCall('untrusted', [$ipBlacklist]);
