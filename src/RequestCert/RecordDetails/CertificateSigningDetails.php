<?php

namespace WemsCA\RequestCert\RecordDetails;

use WemsCA\RequestCert\RequestCertParameters;

class CertificateSigningDetails
{

    /** @var int */
    private $serialNumber;

    /** @var string */
    private $publicKey;

    /** @var string */
    private $loginAsUser;

    /** @var int */
    private $timestamp;

    /** @var RequestCertParameters */
    private $requestCertParameters;

    /**
     * @return int
     */
    public function getSerialNumber()
    {
        return $this->serialNumber;
    }

    /**
     * @param int $serialNumber
     *
     * @return $this
     */
    public function setSerialNumber($serialNumber)
    {
        $this->serialNumber = $serialNumber;

        return $this;
    }

    /**
     * @return string
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * @param string $publicKey
     *
     * @return $this
     */
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;

        return $this;
    }

    /**
     * @return string
     */
    public function getLoginAsUser()
    {
        return $this->loginAsUser;
    }

    /**
     * @param string $loginAsUser
     *
     * @return $this
     */
    public function setLoginAsUser($loginAsUser)
    {
        $this->loginAsUser = $loginAsUser;

        return $this;
    }

    /**
     * @return int
     */
    public function getTimestamp()
    {
        return $this->timestamp;
    }

    /**
     * @param int $timestamp
     *
     * @return $this
     */
    public function setTimestamp($timestamp)
    {
        $this->timestamp = $timestamp;

        return $this;
    }

    /**
     * @return RequestCertParameters
     */
    public function getRequestCertParameters()
    {
        return $this->requestCertParameters;
    }

    /**
     * @param RequestCertParameters $requestCertParameters
     *
     * @return $this
     */
    public function setRequestCertParameters($requestCertParameters)
    {
        $this->requestCertParameters = $requestCertParameters;

        return $this;
    }

}
