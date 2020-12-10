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

use Docserver\controllers\DocserverController;
use Docserver\models\AdrModel;
use Docserver\models\DocserverModel;
use SrcCore\models\CoreConfigModel;

class CertificateSignatureController
{
    public static $signatureLength = 30000;

    public static function getHashedCertificate(array $args)
    {
        $libDir   = CoreConfigModel::getLibrariesDirectory();
        require_once($libDir . 'SetaPDF-Signer/library/SetaPDF/Autoload.php');

        $adr = AdrModel::getDocumentsAdr([
            'select'  => ['path', 'filename'],
            'where'   => ['main_document_id = ?', 'type = ?'],
            'data'    => [$args['id'], 'ESIGN']
        ]);
        if (!empty($adr)) {
            $docserver          = DocserverModel::getByType(['type' => 'ESIGN', 'select' => ['path']]);
            $pathToDocument     = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
        } else {
            $adr = AdrModel::getDocumentsAdr([
                'select' => ['path', 'filename'],
                'where'  => ['main_document_id = ?', 'type = ?'],
                'data'   => [$args['id'], 'DOC']
            ]);
            $docserver      = DocserverModel::getByType(['type' => 'DOC', 'select' => ['path']]);
            $pathToDocument = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
        }
        $tmpPath            = CoreConfigModel::getTmpPath();
        $signedDocumentPath = $tmpPath . $GLOBALS['id'] . '_' . rand() . '_signedDocument.pdf';

        $writer             = new \SetaPDF_Core_Writer_File($signedDocumentPath);
        $document           = \SetaPDF_Core_Document::loadByFilename($pathToDocument, $writer);
        $signer             = new \SetaPDF_Signer($document);

        $module             = new \SetaPDF_Signer_Signature_Module_Pades();
        $certificate        = new \SetaPDF_Signer_X509_Certificate($args['certificate']);

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

    public static function signDocument($args = [])
    {
        $libDir   = CoreConfigModel::getLibrariesDirectory();
        require_once($libDir . 'SetaPDF-Signer/library/SetaPDF/Autoload.php');
        $certificateSignature = \SetaPDF_Core_Type_HexString::hex2str($args['hashSignature']);
        $signatureContentLength = $args['signatureContentLength'];
        $certificate = $args['certificate'];

        $adr = AdrModel::getDocumentsAdr([
            'select'  => ['path', 'filename'],
            'where'   => ['main_document_id = ?', 'type = ?'],
            'data'    => [$args['id'], 'ESIGN']
        ]);
        if (!empty($adr)) {
            $docserver          = DocserverModel::getByType(['type' => 'ESIGN', 'select' => ['path']]);
            $pathToDocument     = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
        } else {
            $adr = AdrModel::getDocumentsAdr([
                'select' => ['path', 'filename'],
                'where'  => ['main_document_id = ?', 'type = ?'],
                'data'   => [$args['id'], 'DOC']
            ]);
            $docserver      = DocserverModel::getByType(['type' => 'DOC', 'select' => ['path']]);
            $pathToDocument = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
        }
        $tmpPath            = CoreConfigModel::getTmpPath();
        $signedDocumentPath = $tmpPath . $GLOBALS['id'] . '_' . rand() . '_signedDocument.pdf';
        $writer             = new \SetaPDF_Core_Writer_File($signedDocumentPath);
        $document           = \SetaPDF_Core_Document::loadByFilename($pathToDocument, $writer);
        $signer             = new \SetaPDF_Signer($document);

        // create a module instance
        $module = new \SetaPDF_Signer_Signature_Module_Pades();
        // create a certificate instance
        $certificate = new \SetaPDF_Signer_X509_Certificate($certificate);
        // pass the user certificate to the module
        $module->setCertificate($certificate);
        // setup information resolver manager
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
        $signer->setSignatureContentLength($signatureContentLength);

        // pass the signature to the signature module
        $module->setSignatureValue($certificateSignature);
        // get the CMS structur from the signature module
        $cms = (string)$module->getCms();
        
        $ts = $certificate->getExtensions()->get(\SetaPDF_Signer_X509_Extension_TimeStamp::OID);
        if ($ts && $ts->getVersion() === 1 && $ts->requiresAuth() === false) {
            $tsLocation = $ts->getLocation();
        }

        if (isset($tsLocation)) {
            $tsModule = new \SetaPDF_Signer_Timestamp_Module_Rfc3161_Curl($tsLocation);
            $signer->setTimestampModule($tsModule);
            $cms = $signer->addTimeStamp($cms, $tmpDocument);
        }

        $tempPath = \SetaPDF_Core_Writer_TempFile::createTempPath();
        $tmpDocument = $signer->preSign(
            new \SetaPDF_Core_Writer_File($tempPath),
            $module
        );

        // save the signature to the temporary document
        $signer->saveSignature($tmpDocument, $cms);
        try {
            $signer->saveSignature($tmpDocument, $cms);
            // unlink($tmpDocument->getWriter()->getPath());
        } catch (\SetaPDF_Signer_Exception_ContentLength $e) {
            // unlink($tmpDocument->getWriter()->getPath());
            $signatureContentLength = $signatureContentLength + 1000;
            return ['errors' => 'Not enought space for signature', 'newSignatureLength' => $signatureContentLength];
        }

        $storeInfos = DocserverController::storeResourceOnDocServer([
            'encodedFile'   => base64_encode(file_get_contents($signedDocumentPath)),
            'format'        => 'pdf',
            'docserverType' => 'ESIGN'
        ]);

        // if (!empty($storeInfos['errors'])) {
        //     return ['errors' => $storeInfos['errors']];
        // }

        AdrModel::deleteDocumentAdr([
            'where' => ['main_document_id = ?', 'type = ?'],
            'data'  => [$args['id'], 'ESIGN']
        ]);
        AdrModel::createDocumentAdr([
            'documentId'  => $args['id'],
            'type'        => 'ESIGN',
            'path'        => $storeInfos['path'],
            'filename'    => $storeInfos['filename'],
            'fingerprint' => $storeInfos['fingerprint']
        ]);
        // unlink($signedDocumentPath);
    }
}
