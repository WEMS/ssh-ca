<?php

namespace WemsCA\RequestCert;

class RequestCertParameters
{

    const DEFAULT_LOGIN_USER = 'wems';
    const DEFAULT_EXPIRY = '1h';
    const BASE_PERMISSION = 'permit-pty';
    const DEFAULT_CERTIFICATE_IDENTITY = 'signed by PHP';

    /** @var string */
    private $caPath;

    /** @var string */
    private $tmpDir;

    /** @var string */
    private $defaultExpiry;

    /** @var string */
    private $defaultLoginUser;

    /** @var array */
    private $permissions;

    /** @var string */
    private $certificateIdentity;

    /**
     * @param string $caPath
     * @throws InvalidConfigurationException
     */
    public function __construct($caPath)
    {
        $this->caPath = $caPath;
        $this->setTmpDir(sys_get_temp_dir());
        $this->setDefaultLoginUser(self::DEFAULT_LOGIN_USER);
        $this->setDefaultExpiry(self::DEFAULT_EXPIRY);
        $this->setPermissions([self::BASE_PERMISSION]);

        $this->validate();
    }

    /**
     * @throws InvalidConfigurationException
     */
    private function validate()
    {
        if (!file_exists($this->caPath)) {
            throw new InvalidConfigurationException('No CA exists at path "' . $this->caPath . '"');
        }
    }

    /**
     * @param string $caPath
     *
     * @return $this
     */
    public function setCAPath($caPath)
    {
        $this->caPath = $caPath;

        return $this;
    }

    /**
     * @param string $tmpDir
     *
     * @return $this
     */
    public function setTmpDir($tmpDir)
    {
        // remove trailing slash if there was one
        if (strpos($tmpDir, '/', strlen($tmpDir) - 1)) {
            $tmpDir = substr($tmpDir, 0, -1);
        }

        $this->tmpDir = $tmpDir;

        return $this;
    }

    /**
     * @param string $defaultExpiry
     *
     * @return $this
     */
    public function setDefaultExpiry($defaultExpiry)
    {
        $this->defaultExpiry = $defaultExpiry;

        return $this;
    }

    /**
     * @param string $defaultLoginUser
     *
     * @return $this
     */
    public function setDefaultLoginUser($defaultLoginUser)
    {
        $this->defaultLoginUser = $defaultLoginUser;

        return $this;
    }

    /**
     * @ref man ssh-keygen -O option
     * Specify a certificate option when signing a key.
     * This option may be specified multiple times.  Please see the CERTIFICATES section for details.
     *
     * @param array $permissions
     *
     * @return $this
     */
    public function setPermissions(array $permissions)
    {
        $this->permissions = $permissions;

        return $this;
    }

    /**
     * @param string $certificateIdentity
     *
     * @return $this
     */
    public function setCertificateIdentity($certificateIdentity)
    {
        $this->certificateIdentity = $certificateIdentity;

        return $this;
    }

    /**
     * @return string
     */
    public function getCaPath()
    {
        return $this->caPath;
    }

    /**
     * @return string
     */
    public function getTmpDir()
    {
        return $this->tmpDir;
    }

    /**
     * @return string
     */
    public function getDefaultExpiry()
    {
        return $this->defaultExpiry;
    }

    /**
     * @return string
     */
    public function getDefaultLoginUser()
    {
        return $this->defaultLoginUser;
    }

    /**
     * @return array
     */
    public function getPermissions()
    {
        return $this->permissions;
    }

    /**
     * @return string
     */
    public function getCertificateIdentity()
    {
        return $this->certificateIdentity;
    }

    public function toArray()
    {
        return [
            'ca-path' => $this->getCaPath(),
            'default-expiry' => $this->getDefaultExpiry(),
            'default-login-user' => $this->getDefaultLoginUser(),
            'tmp-dir' => $this->getTmpDir(),
            'permissions' => $this->getPermissions(),
            'certificate-identity' => $this->getCertificateIdentity(),
        ];
    }

}
