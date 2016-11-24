<?php

use WemsCA\Controller;

$route->post('/request-cert', [new Controller\RequestCertController($container), 'requestCert']);
