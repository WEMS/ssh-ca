<?php

use WemsCA\Controller;

/** @var \Zend\Expressive\Application $app */
$app->post('/request-cert', [new Controller\RequestCertController($container), 'requestCert']);
