<?php

namespace WemsCA\Handler;

use WemsCA\RequestCert\RequestCertParameters;

class RequestCert extends BaseHandler
{

    public function handle(RequestCertParameters $requestCertParameters)
    {
        // a unique ref for this transaction
        $uniqId = uniqid();

        // receive post data
        // extract data from post

        $inputKeyPath = $requestCertParameters->getTmpDir() . '/ssh_id_' . $uniqId . '.pub';
        $outputSignedFile = $requestCertParameters->getTmpDir() . '/tmp/ssh_id_' . $uniqId . '-cert.pub';

        /** @var \Zend\Diactoros\UploadedFile $inputKeyFile */
        $uploadedFiles = $this->request->getUploadedFiles();

        if (empty($uploadedFiles['key'])) {
            $this->response = $this->response->withStatus(400);
            $this->response->getBody()->write('Missing uploaded file under "key"' . PHP_EOL);

            return $this->response;
        }

        $inputKeyFile = $uploadedFiles['key'];
        $inputKeyContents = $inputKeyFile->getStream()->getContents();

        file_put_contents($inputKeyPath, $inputKeyContents);

        $parsedBody = $this->request->getParsedBody();
        $loginAs = !empty($parsedBody['user']) ? $parsedBody['user'] : 'wems'; // todo const this as DEFAULT_LOGIN_USER

        $userReference = 'signed by PHP';

        /*
         * @ref man ssh-keygen
         *    -O option
                      Specify a certificate option when signing a key.  This option may be specified multiple times.  Please see the CERTIFICATES section for details.  The options that are valid for user cer‐
                      tificates are:

         */

        $allowedPermissions = ['permit-pty', '"force-command=df"'];

        $permissions = '';
        foreach ($allowedPermissions as $permission) {
            $permissions .= ' -O ' . $permission;
        }

        $command = <<<STR
    ssh-keygen -V +{$requestCertParameters->getDefaultExpiry()} -s {$requestCertParameters->getCaPath()} -I "$userReference" -O clear $permissions -n $loginAs $inputKeyPath 2>&1
STR;

        ob_start();
        shell_exec($command);
        $stdOut = ob_get_clean();

        //echo $stdOut . PHP_EOL;

        // @todo log exit status of command and log the process into an audit trail
        // @todo figure out exactly what we want to capture to the log

        $output = trim(file_get_contents($outputSignedFile));

        foreach ([$inputKeyPath, $outputSignedFile] as $removeThisFile) {
            unlink($removeThisFile);
        }

        $this->response->getBody()->write($output);

        return $this->response;
    }

}
