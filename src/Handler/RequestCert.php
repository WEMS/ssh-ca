<?php

namespace WemsCA\Handler;

use WemsCA\RequestCert\Files;
use WemsCA\RequestCert\RecordDetails\CertificateSigningDetails;
use WemsCA\RequestCert\RecordDetails\DetailRecorderContract;
use WemsCA\RequestCert\RequestCertParameters;

class RequestCert extends BaseHandler
{

    const POST_PARAM_FILE_KEY = 'key';
    const POST_PARAM_USER = 'user';

    /** @var RequestCertParameters */
    private $parameters;

    /** @var DetailRecorderContract */
    private $detailRecorder;

    /** @var Files */
    private $fileHandler;

    /**
     * @param DetailRecorderContract $detailRecorder
     *
     * @return $this
     */
    public function setDetailRecorder($detailRecorder)
    {
        $this->detailRecorder = $detailRecorder;

        return $this;
    }

    public function handle(RequestCertParameters $requestCertParameters)
    {
        $this->parameters = $requestCertParameters;

        $this->fileHandler = new Files($this->uniqueReference, $this->parameters->getTmpDir());

        $uploadedFiles = $this->request->getUploadedFiles();

        if (empty($uploadedFiles[self::POST_PARAM_FILE_KEY])) {
            $this->response = $this->response->withStatus(400);

            $this->response->getBody()->write(
                'Missing uploaded file under "' . self::POST_PARAM_FILE_KEY . '"' . PHP_EOL
            );

            return $this->response;
        }

        $this->fileHandler->storeTemporaryInputPublicKeyFile($uploadedFiles[self::POST_PARAM_FILE_KEY]);

        $command = $this->getCommand();

        $stdOut = $this->runCommand($command);

        if (!$this->fileHandler->signedCertExists()) {
            $this->logError('Couldn\'t write a cert. STDOUT: ' . $stdOut);
            $this->logError('Couldn\'t write a cert. Command: ' . PHP_EOL . $command);

            $this->response = $this->response->withStatus(500);
            $this->response->getBody()->write('Something went wrong creating the cert');

            return $this->response;
        }

        $this->recordCertificateSigningDetails();

        $this->response->getBody()->write($this->getSignedCertificate());

        $this->fileHandler->removeTemporaryArtifacts();

        return $this->response;
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
        $command .= ' -z "' . $this->uniqueReference . '"';
        $command .= ' ' . $this->fileHandler->getInputPublicKeyPath() . ' 2>&1';

        return $command;
    }

    /**
     * @return string
     */
    private function getLoginAsUser()
    {
        $parsedBody = $this->request->getParsedBody();

        $loginAs = !empty($parsedBody[self::POST_PARAM_USER])
                    ? $parsedBody[self::POST_PARAM_USER]
                    : $this->parameters->getDefaultLoginUser();

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

        $allowedIpAddresses = $this->parameters->getAllowedIpAddresses();

        if (!empty($allowedIpAddresses)) {
            $commaSeparatedAllowedIps = implode(',', $allowedIpAddresses);
            $permissions .= ' -O "source-address=' . $commaSeparatedAllowedIps . '"';
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

    /**
     * @return string
     */
    private function getSignedCertificate()
    {
        return $this->fileHandler->getSignedCertificate();
    }

    private function recordCertificateSigningDetails()
    {
        $certificateSigningDetails = new CertificateSigningDetails();

        $certificateSigningDetails
            ->setSerialNumber($this->uniqueReference)
            ->setPublicKey($this->fileHandler->getPublicKeyContents($withTrailingNewLine = false))
            ->setLoginAsUser($this->getLoginAsUser())
            ->setRequestCertParameters($this->parameters)
            ->setTimestamp(time());

        $this->detailRecorder->recordCertificateSigningDetails($certificateSigningDetails);

        $this->logNotice('Created a cert', ['serial-number' => $certificateSigningDetails->getSerialNumber()]);
    }
}
