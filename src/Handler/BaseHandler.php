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

        $this->uniqueReference = uniqid();
    }

    /**
     * @param string $message
     */
    protected function logNotice($message)
    {
        $this->logger->notice('Ref [' . $this->uniqueReference . ']. ' . $message);
    }

    /**
     * @param string $message
     */
    protected function logError($message)
    {
        $this->logger->error('Ref [' . $this->uniqueReference . ']. ' . $message);
    }

}
