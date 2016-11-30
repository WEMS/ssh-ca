<?php

namespace WemsCA\Controller;

use Interop\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use WemsCA\Handler\RequestCert;
use WemsCA\RequestCert\InvalidConfigurationException;
use WemsCA\RequestCert\RecordDetails\DetailRecorderContract;
use WemsCA\RequestCert\RequestCertParameters;

class RequestCertController
{

    /** @var ContainerInterface */
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function requestCert(ServerRequestInterface $request, ResponseInterface $response)
    {
        $handler = new RequestCert($request, $response, $this->container->get('logger'));
        $handler->setDetailRecorder($this->container->get(DetailRecorderContract::class));

        $config = $this->container->get('config');

        // we require a CA path so let's check for that before we go any further
        if (!isset($config['ca_path'])) {
            throw new InvalidConfigurationException('The config MUST have a ca_path defined');
        }

        $parameters = new RequestCertParameters($config['ca_path']);

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

        if (!empty($config['ip_whitelist'])) {
            $parameters->setAllowedIpAddresses($config['ip_whitelist']);
        }

        $handler->handle($parameters);
    }

}
