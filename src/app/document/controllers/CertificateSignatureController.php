<?php

/**
* Copyright Maarch since 2008 under license.
* See LICENSE.txt file at the root folder for more details.
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
use Document\models\DocumentModel;
use SrcCore\models\CoreConfigModel;
use User\models\UserModel;

class CertificateSignatureController
{
    public static $signatureLength = 30000;

    public static function getHashedCertificate(array $args)
    {
        session_start();

        $tmpPath = CoreConfigModel::getTmpPath();

        if (!empty($args['body']['tmpUniqueId'])) {
            if (is_file("{$tmpPath}tmpSignatureEsign_{$GLOBALS['id']}_{$args['body']['tmpUniqueId']}.pdf")) {
                $pathToDocument = "{$tmpPath}tmpSignatureEsign_{$GLOBALS['id']}_{$args['body']['tmpUniqueId']}.pdf";
            }
        }
        if (empty($pathToDocument)) {
            $adr = AdrModel::getDocumentsAdr([
                'select'  => ['path', 'filename'],
                'where'   => ['main_document_id = ?', 'type = ?'],
                'data'    => [$args['id'], 'ESIGN']
            ]);
            if (!empty($adr)) {
                $docserver = DocserverModel::getByType(['type' => 'ESIGN', 'select' => ['path']]);
            } else {
                $adr = AdrModel::getDocumentsAdr([
                    'select' => ['path', 'filename'],
                    'where'  => ['main_document_id = ?', 'type = ?'],
                    'data'   => [$args['id'], 'DOC']
                ]);
                $docserver = DocserverModel::getByType(['type' => 'DOC', 'select' => ['path']]);
            }
            $pathToDocument = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
        }

        $signedDocumentPath = $tmpPath . $GLOBALS['id'] . '_' . rand() . '_signedDocument.pdf';

        $writer             = new \SetaPDF_Core_Writer_File($signedDocumentPath);
        $document           = \SetaPDF_Core_Document::loadByFilename($pathToDocument, $writer);
        $signer             = new \SetaPDF_Signer($document);

        $module             = new \SetaPDF_Signer_Signature_Module_Pades();
        $certificate        = new \SetaPDF_Signer_X509_Certificate($args['body']['certificate']);

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
        $signatureContentLength = $args['body']['signatureLength'] ?? CertificateSignatureController::$signatureLength;
        foreach ($extraCerts->getAll() as $extraCert) {
            $signatureContentLength += (strlen($extraCert->get(\SetaPDF_Signer_X509_Format::DER)) * 2);
        }

        $user = UserModel::getById(['id' => $GLOBALS['id'], 'select' => ['firstname', 'lastname', 'email']]);
        $signer->setName($user['firstname'] . ' ' . $user['lastname'] . ' ('. $user['email'] . ')');
        $signer->setReason('Signature du document par ' . $user['firstname'] . ' ' . $user['lastname']);
        $signer->setLocation('Maarch Parapheur');

        $signer->setSignatureContentLength($signatureContentLength);

        if (!empty($args['body']['signatures'][0])) {
            $pages = $document->getCatalog()->getPages();
            $pageCount = $pages->count();
    
            for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                $page = $pages->getPage($pageNumber);
    
                $format = \SetaPDF_Core_PageFormats::getFormat($page->getWidthAndHeight(), \SetaPDF_Core_PageFormats::ORIENTATION_AUTO);

                $signature = $args['body']['signatures'][0];
                if ($signature['page'] == $pageNumber) {
                    $image = base64_decode($signature['encodedImage']);
                    if ($image === false) {
                        return ['errors' => 'base64_decode failed'];
                    }
    
                    $imageTmpPath = $tmpPath . $GLOBALS['id'] . '_' . rand() . '_writing.png';
                    if ($signature['type'] == 'SVG') {
                        $Imagick = new \Imagick();
                        $Imagick->readImageBlob($image);
                        $Imagick->setImageFormat("png24");
                        $Imagick->writeImage($imageTmpPath);
                        $Imagick->clear();
                        $Imagick->destroy();
                    } else {
                        file_put_contents($imageTmpPath, $image);
                        if ($signature['positionX'] == 0 && $signature['positionY'] == 0) {
                            $signWidth = $format['width'];
                            $signPosX  = 0;
                            $signPosY  = 0;
                        } else {
                            $signWidth = ($signature['width'] * $format['width']) / 100;
                            $signPosX  = ($signature['positionX'] * $format['width']) / 100;
                            $signPosY  = ($signature['positionY'] * $format['height']) / 100;
                        }
                        $signatureInfo = [
                            'page'          => $signature['page'],
                            'positionX'     => $signPosX,
                            'positionY'     => $signPosY,
                            'filePath'      => $imageTmpPath,
                            'signWidth'     => $signWidth
                        ];
                    }
                }
            }
        }

        if (!empty($signatureInfo)) {
            $image = \SetaPDF_Core_Image::getByPath($signatureInfo['filePath']);
            $imageXObject = $image->toXObject($document);
            $width = $signatureInfo['signWidth'];
            $height = $imageXObject->getHeight($width);
            $signatureFieldWith = $width > 200 ? $width : 200;

            $fieldName = $signer->addSignatureField(
                'Signature_' . rand(),
                $signatureInfo['page'],
                \SetaPDF_Signer_SignatureField::POSITION_LEFT_TOP,
                ['x' => $signatureInfo['positionX'], 'y' => -$signatureInfo['positionY']],
                $signatureFieldWith,
                $height + 50
            )->getQualifiedName();

            $signer->setSignatureFieldName($fieldName);

            $xObject = \SetaPDF_Core_XObject_Form::create($document, [0, 0, $signatureFieldWith, $height + 50]);
            $canvas = $xObject->getCanvas();
            $imageXObject->draw($canvas, 0, 50, $width, $height);

            $font = new \SetaPDF_Core_Font_Type0_Subset(
                $document,
                __DIR__ . '/fonts/dejavu-fonts-ttf-2.37/ttf/DejaVuSans.ttf'
            );

            $loadedXml = CoreConfigModel::getConfig();
            if ($loadedXml->textWithDigitalSignature == 'true') {
                $textBlock = new \SetaPDF_Core_Text_Block($font, 6);
                $textBlock->setWidth($signatureFieldWith);
                $textBlock->setLineHeight(14);
                $textBlock->setPadding(2);
                $textBlock->setText("Signé électroniquement par : " . $user['firstname'] . ' ' . $user['lastname'] . "\nLe " . date('c'));
                $textBlock->draw($canvas, 0, 30);
            }

            $appearance = new \SetaPDF_Signer_Signature_Appearance_XObject($xObject);
            $signer->setAppearance($appearance);
        } else {
            $fieldName = $signer->addSignatureField()->getQualifiedName();
            $signer->setSignatureFieldName($fieldName);
        }
        $tempPath = \SetaPDF_Core_Writer_TempFile::createTempPath();
        $_SESSION['tmpDocument'] = $signer->preSign(
            new \SetaPDF_Core_Writer_File($tempPath),
            $module
        );
        $_SESSION['module'] = $module;

        return [
            'dataToSign'                => \SetaPDF_Core_Type_HexString::str2hex($module->getDataToSign($_SESSION['tmpDocument']->getHashFile())),
            'signatureContentLength'    => $signatureContentLength,
            'signatureFieldName'        => $fieldName,
            'tmpUniqueId'               => $args['body']['tmpUniqueId'] ?? null
        ];
    }

    public static function signDocument(array $args)
    {
        session_start();

        $certificateSignature   = \SetaPDF_Core_Type_HexString::hex2str($args['hashSignature']);
        $tmpPath                = CoreConfigModel::getTmpPath();

        if (!empty($args['tmpUniqueId'])) {
            if (is_file("{$tmpPath}tmpSignatureEsign_{$GLOBALS['id']}_{$args['tmpUniqueId']}.pdf")) {
                $pathToDocument = "{$tmpPath}tmpSignatureEsign_{$GLOBALS['id']}_{$args['tmpUniqueId']}.pdf";
            }
        }

        if (empty($pathToDocument)) {
            $adr = AdrModel::getDocumentsAdr([
                'select'  => ['path', 'filename'],
                'where'   => ['main_document_id = ?', 'type = ?'],
                'data'    => [$args['id'], 'ESIGN']
            ]);
            if (!empty($adr)) {
                $docserver = DocserverModel::getByType(['type' => 'ESIGN', 'select' => ['path']]);
            } else {
                $adr = AdrModel::getDocumentsAdr([
                    'select' => ['path', 'filename'],
                    'where'  => ['main_document_id = ?', 'type = ?'],
                    'data'   => [$args['id'], 'DOC']
                ]);
                $docserver = DocserverModel::getByType(['type' => 'DOC', 'select' => ['path']]);
            }
            $pathToDocument = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
        }
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
            return ['errors' => 'Not enough space for signature', 'newSignatureLength' => $signatureContentLength];
        }

        if ($args['signatureMode'] == 'rgs_2stars_timestamped') {
            $document = DocumentModel::getById(['select' => ['digital_signature_transaction_id'], 'id' => $args['id']]);
            $config = DigitalSignatureController::getConfig();
            $signedDocumentPath = DigitalSignatureController::timestampHashes([
                'config'             => $config,
                'signedDocumentPath' => $signedDocumentPath,
                'transactionId'      => $document['digital_signature_transaction_id'],
                'fieldName'          => $args['signatureFieldName'],
                'extraCertificate'   => $args['certificate']
            ]);
            DigitalSignatureController::terminate(['config' => $config, 'transactionId' => $document['digital_signature_transaction_id']]);
        }

        if ($args['lastStep']) {
            $storeInfos = DocserverController::storeResourceOnDocServer([
                'encodedFile'   => base64_encode(file_get_contents($signedDocumentPath)),
                'format'        => 'pdf',
                'docserverType' => 'ESIGN'
            ]);
             if (!empty($storeInfos['errors'])) {
                 return ['errors' => $storeInfos['errors']];
             }

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
        } else {
            copy($signedDocumentPath, "{$tmpPath}tmpSignatureEsign_{$GLOBALS['id']}_{$args['tmpUniqueId']}.pdf");
        }
        unlink($signedDocumentPath);

        return true;
    }

    public static function signWithServerCertificate(\setasign\Fpdi\Tcpdf\Fpdi &$pdf)
    {
        $loadedXml = CoreConfigModel::getConfig();

        if ($loadedXml->electronicSignature->enable == 'true') {
            $certPath       = realpath((string)$loadedXml->electronicSignature->certPath);
            $privateKeyPath = realpath((string)$loadedXml->electronicSignature->privateKeyPath);
            if (!is_file($certPath) || !is_file($privateKeyPath)) {
                return ['errors' => 'Signature with server certificate failed : certPath or privateKeyPath is not valid'];
            }
            $certificate = 'file://' . $certPath;
            $privateKey = 'file://' . $privateKeyPath;
            $info = [
                'Name'        => (string)$loadedXml->electronicSignature->certInfo->name,
                'Location'    => (string)$loadedXml->electronicSignature->certInfo->location,
                'Reason'      => (string)$loadedXml->electronicSignature->certInfo->reason,
                'ContactInfo' => (string)$loadedXml->electronicSignature->certInfo->contactInfo
            ];
            $pdf->setSignature($certificate, $privateKey, (string)$loadedXml->electronicSignature->password, '', 2, $info);
        }

        return true;
    }
}
