<?php

namespace WemsCA\Handler;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

abstract class BaseHandler
{

    /** @var Request */
    protected $request;

    /** @var Response */
    protected $response;

    /** @var LoggerInterface */
    protected $logger;

    /** @var string */
    protected $uniqueReference;

    /**
     * @param Request         $request
     * @param Response        $response
     * @param LoggerInterface $logger
     */
    public function __construct(Request $request, Response $response, LoggerInterface $logger)
    {
        $this->request = $request;
        $this->response = $response;
        $this->logger = $logger;

        // uniqid gives a unique ref and crc32 ensures it's numeric so we can use it as a serial number in the cert
        $this->uniqueReference = crc32(uniqid());
    }

    /**
     * @param string $message
     * @param array $context
     */
    protected function logNotice($message, array $context = [])
    {
        $this->logger->notice('Ref [' . $this->uniqueReference . ']. ' . $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    protected function logError($message, array $context = [])
    {
        $this->logger->error('Ref [' . $this->uniqueReference . ']. ' . $message, $context);
    }

}
