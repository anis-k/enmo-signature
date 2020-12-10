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
        session_start();

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

        $tempPath = \SetaPDF_Core_Writer_TempFile::createTempPath();
        $_SESSION['tmpDocument'] = $signer->preSign(
            new \SetaPDF_Core_Writer_File($tempPath),
            $module
        );
        $_SESSION['module'] = $module;

        return [
            'dataToSign'                => \SetaPDF_Core_Type_HexString::str2hex($module->getDataToSign($_SESSION['tmpDocument']->getHashFile())),
            'signatureContentLength'    => $signatureContentLength
        ];
    }

    public static function signDocument($args = [])
    {
        session_start();

        $certificateSignature = \SetaPDF_Core_Type_HexString::hex2str($args['hashSignature']);

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

        $_SESSION['module']->setSignatureValue($certificateSignature);
        $cms = (string)$_SESSION['module']->getCms();

        $signatureContentLength = $args['signatureContentLength'];
        try {
            $signer->saveSignature($_SESSION['tmpDocument'], $cms);
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

        return true;
    }
}
