<?php

namespace WemsCA\RequestCert;

class RequestCertParametersFactory
{
    private $config = [];

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return RequestCertParameters
     */
    public function getRequestCertParameters()
    {
        $parameters = new RequestCertParameters($this->config['ca_path']);

        if (!empty($this->config['tmp_dir'])) {
            $parameters->setTmpDir($this->config['tmp_dir']);
        }

        if (!empty($this->config['default_expiry'])) {
            $parameters->setDefaultExpiry($this->config['default_expiry']);
        }

        if (!empty($this->config['default_login_user'])) {
            $parameters->setDefaultLoginUser($this->config['default_login_user']);
        }

        if (!empty($this->config['permissions'])) {
            $parameters->setPermissions($this->config['permissions']);
        }

        if (!empty($this->config['certificate_identity'])) {
            $parameters->setCertificateIdentity($this->config['certificate_identity']);
        }

        if (!empty($this->config['ip_whitelist'])) {
            $parameters->setAllowedIpAddresses($this->config['ip_whitelist']);
        }

        return $parameters;
    }

}
