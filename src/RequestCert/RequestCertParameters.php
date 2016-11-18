<?php

namespace WemsCA\RequestCert;

class RequestCertParameters
{

    const DEFAULT_LOGIN_USER = 'wems';
    const DEFAULT_EXPIRY = '1h';

    /** @var string */
    private $caPath;

    /** @var string */
    private $tmpDir;

    /** @var string */
    private $defaultExpiry;

    /** @var string */
    private $defaultLoginUser;

    /**
     * @param string $caPath
     */
    public function __construct($caPath)
    {
        $this->caPath = $caPath;
        $this->setTmpDir(sys_get_temp_dir());
        $this->setDefaultLoginUser(self::DEFAULT_LOGIN_USER);
        $this->setDefaultExpiry(self::DEFAULT_EXPIRY);
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

}
