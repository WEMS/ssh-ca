<?php

namespace WemsCA\Handler;

use WemsCA\RequestCert\RequestCertParameters;
use Zend\Diactoros\UploadedFile;

class RequestCert extends BaseHandler
{

    const POST_PARAM_FILE_KEY = 'key';
    const POST_PARAM_USER = 'user';

    /** @var RequestCertParameters */
    private $parameters;

    /** @var string */
    private $uniqueRef;

    /** @var string */
    private $inputPublicKeyPath;

    /** @var string */
    private $outputSignedCertPath;

    public function handle(RequestCertParameters $requestCertParameters)
    {
        $this->parameters = $requestCertParameters;

        $this->setupPaths();

        $uploadedFiles = $this->request->getUploadedFiles();

        if (empty($uploadedFiles[self::POST_PARAM_FILE_KEY])) {
            $this->response = $this->response->withStatus(400);
            $this->response->getBody()->write('Missing uploaded file under "key"' . PHP_EOL);

            return $this->response;
        }

        $this->storeTemporaryInputPublicKeyFile($uploadedFiles[self::POST_PARAM_FILE_KEY]);

        $command = $this->getCommand();

        $stdOut = $this->runCommand($command);

        // @todo log exit status of command and log the process into an audit trail
        // @todo figure out exactly what we want to capture to the log

        if (!file_exists($this->outputSignedCertPath)) {
            $this->logger->error('Couldn\'t write a cert. STDOUT: ' . $stdOut);
            $this->logger->error('Couldn\'t write a cert. Command: ' . PHP_EOL . $command);

            $this->response = $this->response->withStatus(500);
            $this->response->getBody()->write('Something went wrong creating the cert');

            return $this->response;
        }

        $this->logger->notice('Created a cert for user ' . $this->getLoginAsUser());

        $this->response->getBody()->write($this->getSignedCertificate());

        $this->removeTemporaryArtifacts();

        return $this->response;
    }

    private function setupPaths()
    {
        // a unique ref for this transaction
        $this->uniqueRef = uniqid();

        $this->inputPublicKeyPath = $this->parameters->getTmpDir() . '/ssh_id_' . $this->uniqueRef . '.pub';
        $this->outputSignedCertPath = $this->parameters->getTmpDir() . '/ssh_id_' . $this->uniqueRef . '-cert.pub';
    }

    /**
     * @param UploadedFile $inputKeyFile
     */
    private function storeTemporaryInputPublicKeyFile($inputKeyFile)
    {
        $inputKeyFile->moveTo($this->inputPublicKeyPath);
    }

    /**
     * @return string
     */
    private function getCommand()
    {
        $command = 'ssh-keygen -V';
        $command .= ' +' . $this->parameters->getDefaultExpiry();
        $command .= ' -s ' . $this->parameters->getCaPath();
        $command .= ' -I "' . $this->parameters->getCertificateIdentity() . '"';
        $command .= ' -O clear ' . $this->getPermissionsString();
        $command .= ' -n ' . $this->getLoginAsUser();
        $command .= ' ' . $this->inputPublicKeyPath . ' 2>&1';

        return $command;
    }

    /**
     * @return string
     */
    private function getLoginAsUser()
    {
        $parsedBody = $this->request->getParsedBody();
        $loginAs = !empty($parsedBody[self::POST_PARAM_USER]) ? $parsedBody[self::POST_PARAM_USER] : $this->parameters->getDefaultLoginUser();

        return $loginAs;
    }

    /**
     * @return string
     */
    private function getPermissionsString()
    {
        $permissions = '';

        foreach ($this->parameters->getPermissions() as $permission) {
            $permissions .= ' -O "' . $permission . '"';
        }

        return $permissions;
    }

    /**
     * @param string $command
     *
     * @return string
     */
    private function runCommand($command)
    {
        ob_start();
        shell_exec($command);
        $stdOut = ob_get_clean();

        return $stdOut;
    }

    private function removeTemporaryArtifacts()
    {
        foreach ([$this->inputPublicKeyPath, $this->outputSignedCertPath] as $removeThisFile) {
            unlink($removeThisFile);
        }
    }

    /**
     * @return string
     */
    private function getSignedCertificate()
    {
        $output = trim(file_get_contents($this->outputSignedCertPath)) . PHP_EOL;

        return $output;
    }

}
