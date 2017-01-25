<?php

namespace WemsCA\RequestCert\RecordDetails;

class PDODatabaseDetailRecorder implements DetailRecorderContract
{

    /** @var \PDO */
    private $dbh;

    /**
     * @param \PDO $dbh
     */
    public function __construct(\PDO $dbh)
    {
        $this->dbh = $dbh;
    }

    public function recordCertificateSigningDetails(CertificateSigningDetails $certificateSigningDetails)
    {
        $tableName = $this->dbh->quote('certs');

        $stmt = $this->dbh->prepare(
            'INSERT INTO ' . $tableName . ' VALUES (:serialNumber, :publicKey, :loginAs, :parameters, :createdAt)'
        );

        $stmt->bindValue(':serialNumber', $certificateSigningDetails->getSerialNumber());
        $stmt->bindValue(':publicKey', $certificateSigningDetails->getPublicKey());
        $stmt->bindValue(':loginAs', $certificateSigningDetails->getLoginAsUser());
        $stmt->bindValue(':parameters', json_encode($certificateSigningDetails->getRequestCertParameters()->toArray()));
        $stmt->bindValue(':createdAt', $certificateSigningDetails->getTimestamp());

        $stmt->execute();
    }
}
