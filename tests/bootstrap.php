<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../app/container.php';

class TestCase extends \PHPUnit\Framework\TestCase
{
    /** @var League\Container\Container */
    protected static $container;

    public static function setContainer(League\Container\Container $container)
    {
        self::$container = $container;
    }
}

TestCase::setContainer($container);
