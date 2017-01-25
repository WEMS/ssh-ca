<?php

namespace WemsCA\RequestCert\RecordDetails;

interface DetailRecorderContract
{

    public function recordCertificateSigningDetails(CertificateSigningDetails $certificateSigningDetails);
}
