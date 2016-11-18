<?php

require __DIR__ . '/vendor/autoload.php';

define('CA_PATH', '/home/ben/ssh_ca/dev_em_auth_unsigned');

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

$container = new League\Container\Container;

$container->share('response', Zend\Diactoros\Response::class);
$container->share('request', function () {
    return Zend\Diactoros\ServerRequestFactory::fromGlobals(
        $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
    );
});

$container->share('emitter', Zend\Diactoros\Response\SapiEmitter::class);

$route = new League\Route\RouteCollection($container);

$route->map('POST', '/request-cert', function (ServerRequestInterface $request, ResponseInterface $response) {

    // a unique ref for this transaction
    $uniqId = uniqid();

    // receive post data
    // extract data from post

    $expires = '1h';

    $inputKeyPath = '/tmp/ssh_id_' . $uniqId . '.pub';
    $outputSignedFile = '/tmp/ssh_id_' . $uniqId . '-cert.pub';

    /** @var \Zend\Diactoros\UploadedFile $inputKeyFile */
    $uploadedFiles = $request->getUploadedFiles();

    if (empty($uploadedFiles['key'])) {
        $response = $response->withStatus(400);
        $response->getBody()->write('Missing uploaded file under "key"' . PHP_EOL);

        return $response;
    }

    $inputKeyFile = $uploadedFiles['key'];
    $inputKeyContents = $inputKeyFile->getStream()->getContents();

    file_put_contents($inputKeyPath, $inputKeyContents);

    $parsedBody = $request->getParsedBody();
    $loginAs = !empty($parsedBody['user']) ? $parsedBody['user'] : 'wems'; // todo const this as DEFAULT_LOGIN_USER

    $userReference = 'signed by PHP';

    $caPath = CA_PATH; //@todo tidy up

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
ssh-keygen -V +$expires -s $caPath -I "$userReference" -O clear $permissions -n $loginAs $inputKeyPath 2>&1
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

    $response->getBody()->write($output);

    return $response;
});

$response = $route->dispatch($container->get('request'), $container->get('response'));

$container->get('emitter')->emit($response);
