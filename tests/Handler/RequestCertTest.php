<?php

namespace Tests\Handler;

use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Psr\Http\Message\ResponseInterface;
use WemsCA\Handler\RequestCert;
use WemsCA\RequestCert\RecordDetails\DetailRecorderContract;
use WemsCA\RequestCert\RequestCertParameters;
use WemsCA\RequestCert\RequestCertParametersFactory;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\UploadedFile;

class RequestCertTest extends \TestCase
{
    public function testSomething()
    {
        $testLogHandler = new TestHandler();
        $logger = new Logger('testing', [$testLogHandler]);

        $file = new UploadedFile($this->getFilePathForTestSSHPublicKey(), filesize($this->getFilePathForTestSSHPublicKey()), UPLOAD_ERR_OK);

        $request = new ServerRequest();
        $request = $request->withUploadedFiles([RequestCert::POST_PARAM_FILE_KEY => $file]);
        $request = $request->withMethod('post');

        $response = new Response();
        $handler = new RequestCert($request, $response, $logger);

        $params = $this->getParams();

        $handler->setDetailRecorder(self::$container->get(DetailRecorderContract::class));

        $response = $handler->handle($params);
        $this->assertInstanceOf(ResponseInterface::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $this->assertTrue($testLogHandler->hasRecordThatContains('Created a cert', Logger::NOTICE));
    }

    protected function setUp()
    {
        $key = <<<STRING
ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDNA1PbV3X8frazehiGGyA16qH6VEmiKB5lzCrU3LFag4uY7h9TFUKFtfvnhNRAcs0kmiZHCfKmy+BaSG6wONap4FZMn2NRFEW7j1ckYkYFsBHy4cQZP8kttT3ll8xnrS+nZg6z3Qs6Q5KktvW7lPqFRk2JvcxeISdKCHYnGPg+aBPaG8jXX1GrE3wTA0R4duqGgIjKQXDIHKjMbrsKK9fZzO/nbjowSzyZVpEAyJbxrvPbWGTW9h4gBFyER5gNweb7q63w0JlXNfEv4XwDpPyGk0+2wUkWQWFjpZqpINsSlhKwQuWmmOSS5mQ4syJoJaNPszOdVyiUtkFAPLcqFp45 testkey
STRING;

        file_put_contents($this->getFilePathForTestSSHPublicKey(), $key);
    }

    protected function tearDown()
    {
        unlink($this->getFilePathForTestSSHPublicKey());
    }

    /**
     * @return RequestCertParameters
     */
    private function getParams()
    {
        $config = self::$container->get('config');

        return (new RequestCertParametersFactory($config))->getRequestCertParameters();
    }

    /**
     * @return string
     */
    private function getFilePathForTestSSHPublicKey()
    {
        return __DIR__ . '/../testsshcaprivatekey';
    }
}
