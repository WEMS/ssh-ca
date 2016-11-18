<?php

namespace WemsCA\RequestCert;

class RequestCertParameters
{

    const DEFAULT_LOGIN_USER = 'wems';
    const DEFAULT_EXPIRY = '1h';
    const BASE_PERMISSION = 'permit-pty';

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

    /**
     * @param string $caPath
     */
    public function __construct($caPath)
    {
        $this->caPath = $caPath;
        $this->setTmpDir(sys_get_temp_dir());
        $this->setDefaultLoginUser(self::DEFAULT_LOGIN_USER);
        $this->setDefaultExpiry(self::DEFAULT_EXPIRY);
        $this->setPermissions([self::BASE_PERMISSION]);
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

}
