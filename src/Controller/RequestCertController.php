<?php

namespace WemsCA\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use WemsCA\Handler\RequestCert;
use WemsCA\RequestCert\InvalidConfigurationException;
use WemsCA\RequestCert\RecordDetails\DetailRecorderContract;
use WemsCA\RequestCert\RequestCertParameters;

class RequestCertController
{

    /** @var array */
    private $config;

    /** @var DetailRecorderContract */
    private $detailRecorder;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(array $config, DetailRecorderContract $detailRecorder, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->detailRecorder = $detailRecorder;
        $this->logger = $logger;
    }

    public function requestCert(ServerRequestInterface $request, ResponseInterface $response)
    {
        $handler = new RequestCert($request, $response, $this->logger);
        $handler->setDetailRecorder($this->detailRecorder);

        // we require a CA path so let's check for that before we go any further
        if (!isset($this->config['ca_path'])) {
            throw new InvalidConfigurationException('The config MUST have a ca_path defined');
        }

        $parameters = new RequestCertParameters($this->config['ca_path']);

        if (!empty($this->config['tmp_dir'])) {
            $parameters->setTmpDir($this->config['tmp_dir']);
        }

        if (!empty($this->config['default_expiry'])) {
            $parameters->setDefaultExpiry($this->config['default_expiry']);
        }

        if (!empty($this->config['default_login_user'])) {
            $parameters->setDefaultLoginUser($this->config['default_login_user']);
        }

        if (!empty($this->config['permissions'])) {
            $parameters->setPermissions($this->config['permissions']);
        }

        if (!empty($this->config['certificate_identity'])) {
            $parameters->setCertificateIdentity($this->config['certificate_identity']);
        }

        if (!empty($this->config['ip_whitelist'])) {
            $parameters->setAllowedIpAddresses($this->config['ip_whitelist']);
        }

        $handler->handle($parameters);
    }

}
