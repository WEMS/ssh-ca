<?php

namespace WemsCA\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use WemsCA\Handler\RequestCert;
use WemsCA\RequestCert\InvalidConfigurationException;
use WemsCA\RequestCert\RecordDetails\DetailRecorderContract;
use WemsCA\RequestCert\RequestCertParametersFactory;

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

        $parameters = (new RequestCertParametersFactory($this->config))->getRequestCertParameters();

        $handler->handle($parameters);
    }
}
