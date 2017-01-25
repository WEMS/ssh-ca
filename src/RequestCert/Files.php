<?php

namespace WemsCA\RequestCert;

use Psr\Http\Message\UploadedFileInterface as UploadedFile;

class Files
{

    /** @var string */
    private $inputPublicKeyPath;

    /** @var string */
    private $outputSignedCertPath;

    /** @var string */
    private $tmpDir;

    /** @var string */
    private $uniqueReference;

    /**
     * @param string $uniqueReference
     * @param string $tmpDir
     */
    public function __construct($uniqueReference, $tmpDir = null)
    {
        $this->uniqueReference = $uniqueReference;
        $this->tmpDir = is_null($tmpDir) ? sys_get_temp_dir() : $tmpDir;

        $this->setupPaths();
    }

    /**
     * @param string $tmpDir
     *
     * @return $this
     */
    public function setTmpDir($tmpDir)
    {
        $this->tmpDir = $tmpDir;
        $this->setupPaths();

        return $this;
    }

    /**
     * @param string $uniqueReference
     *
     * @return $this
     */
    public function setUniqueReference($uniqueReference)
    {
        $this->uniqueReference = $uniqueReference;
        $this->setupPaths();

        return $this;
    }

    private function setupPaths()
    {
        $this->inputPublicKeyPath = $this->tmpDir . '/ssh_id_' . $this->uniqueReference . '.pub';
        $this->outputSignedCertPath = $this->tmpDir . '/ssh_id_' . $this->uniqueReference . '-cert.pub';
    }

    /**
     * @param UploadedFile $inputKeyFile
     */
    public function storeTemporaryInputPublicKeyFile(UploadedFile $inputKeyFile)
    {
        $inputKeyFile->moveTo($this->inputPublicKeyPath);
    }

    public function removeTemporaryArtifacts()
    {
        foreach ([$this->inputPublicKeyPath, $this->outputSignedCertPath] as $removeThisFile) {
            unlink($removeThisFile);
        }
    }

    /**
     * @return string
     */
    public function getInputPublicKeyPath()
    {
        return $this->inputPublicKeyPath;
    }

    /**
     * @return bool
     */
    public function signedCertExists()
    {
        return file_exists($this->outputSignedCertPath);
    }

    /**
     * @param bool $withTrailingNewLine
     *
     * @return string
     */
    public function getSignedCertificate($withTrailingNewLine = true)
    {
        return $this->getFileContents($this->outputSignedCertPath, $withTrailingNewLine);
    }

    /**
     * @param bool $withTrailingNewLine
     *
     * @return string
     */
    public function getPublicKeyContents($withTrailingNewLine = true)
    {
        return $this->getFileContents($this->inputPublicKeyPath, $withTrailingNewLine);
    }

    /**
     * @param string $filePath
     * @param bool $withTrailingNewLine
     *
     * @return string
     */
    private function getFileContents($filePath, $withTrailingNewLine = true)
    {
        $output = trim(file_get_contents($filePath));

        if ($withTrailingNewLine) {
            $output .= PHP_EOL;
        }

        return $output;
    }
}
