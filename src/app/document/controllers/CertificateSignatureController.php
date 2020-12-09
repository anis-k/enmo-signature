<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Certificate Signature Controller
* @author dev@maarch.org
*/

namespace Document\controllers;

class CertificateSignatureController
{
    public static $signatureLength = 30000;

    public static function getHashedCertificate(array $args)
    {
        $document = \SetaPDF_Core_Document::loadByString($args['document']);

        $signer = new \SetaPDF_Signer($document);
        $module = new \SetaPDF_Signer_Signature_Module_Pades();
        $certificate = new \SetaPDF_Signer_X509_Certificate($args['certificate']);

        $module->setCertificate($certificate);

        $informationResolverManager = new \SetaPDF_Signer_InformationResolver_Manager();
        $informationResolverManager->addResolver(new \SetaPDF_Signer_InformationResolver_HttpCurlResolver());
        $extraCerts = new \SetaPDF_Signer_X509_Collection();


        $certificates = [$certificate];
        while (count($certificates) > 0) {
            $currentCertificate = array_pop($certificates);

            $aia = $currentCertificate->getExtensions()->get(\SetaPDF_Signer_X509_Extension_AuthorityInformationAccess::OID);
            if ($aia instanceof \SetaPDF_Signer_X509_Extension_AuthorityInformationAccess) {
                foreach ($aia->fetchIssuers($informationResolverManager)->getAll() as $issuer) {
                    $extraCerts->add($issuer);
                    $certificates[] = $issuer;
                }
            }
        }

        $module->setExtraCertificates($extraCerts);
        $signatureContentLength = CertificateSignatureController::$signatureLength;
        foreach ($extraCerts->getAll() as $extraCert) {
            $signatureContentLength += (strlen($extraCert->get(\SetaPDF_Signer_X509_Format::DER)) * 2);
        }
        $signer->setSignatureContentLength($signatureContentLength);

        $ts = $certificate->getExtensions()->get(\SetaPDF_Signer_X509_Extension_TimeStamp::OID);
        if ($ts && $ts->getVersion() === 1 && $ts->requiresAuth() === false) {
            $signer->setSignatureContentLength($signatureContentLength + 6000);
        }

        $tempPath = \SetaPDF_Core_Writer_TempFile::createTempPath();
        $tmpDocument = $signer->preSign(
            new \SetaPDF_Core_Writer_File($tempPath),
            $module
        );

        return [
            'dataToSign'                => \SetaPDF_Core_Type_HexString::str2hex($module->getDataToSign($tmpDocument->getHashFile())),
            'signatureContentLength'    => $signatureContentLength
        ];
    }
}
