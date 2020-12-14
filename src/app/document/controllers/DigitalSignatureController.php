<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Digital Signature Controller
* @author dev@maarch.org
*/

namespace Document\controllers;

use Docserver\controllers\DocserverController;
use Docserver\models\AdrModel;
use Docserver\models\DocserverModel;
use Document\models\DocumentModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\CurlModel;
use User\models\UserModel;
use Workflow\models\WorkflowModel;

class DigitalSignatureController
{
    public static $signatureLength = 30000;
    public static $signatureTimestampLength = 10000;

    public static function createTransaction($args = [])
    {
        $config = DigitalSignatureController::getConfig();

        $nbSignatories = 0;
        foreach ($args['workflow'] as $key => $currentUserId) {
            if (in_array($currentUserId['signature_mode'], ['eidas', 'inca_card_eidas'])) {
                $nbSignatories++;
            } else {
                unset($args['workflow'][$key]);
            }
        }
        $args['workflow'] = array_values($args['workflow']);
        $transactionId  = DigitalSignatureController::initiate(['config' => $config, 'documentId' => $args['documentId'], 'signatoriesCount' => $nbSignatories]);
        DocumentModel::update([
            'set'   => ['digital_signature_transaction_id' => $transactionId],
            'where' => ['id = ?'],
            'data'  => [$args['documentId']]
        ]);
        foreach ($args['workflow'] as $position => $currentUserId) {
            $signatoryId = DigitalSignatureController::signatory([
                'config'        => $config,
                'documentId'    => $args['documentId'],
                'transactionId' => $transactionId,
                'user_id'       => $currentUserId['user_id'],
                'position'      => $position + 1
            ]);
            if (!empty($signatoryId['errors'])) {
                DocumentModel::update([
                    'set'   => ['digital_signature_transaction_id' => null],
                    'where' => ['id = ?'],
                    'data'  => [$args['documentId']]
                ]);
                break;
            } else {
                WorkflowModel::update([
                    'set'   => ['digital_signature_id' => $signatoryId],
                    'where' => ['id = ?'],
                    'data'  => [$currentUserId['id']]
                ]);
            }
        }

        $storeInfos = DocserverController::storeResourceOnDocServer([
            'encodedFile'       => $args['encodedDocument'],
            'format'            => 'pdf',
            'docserverType'     => 'ESIGN'
        ]);

        if (!empty($storeInfos['errors'])) {
            return ['errors' => $storeInfos['errors']];
        }

        AdrModel::createDocumentAdr([
            'documentId'     => $args['documentId'],
            'type'           => 'ESIGN',
            'path'           => $storeInfos['path'],
            'filename'       => $storeInfos['filename'],
            'fingerprint'    => $storeInfos['fingerprint']
        ]);

        return true;
    }
    public static function getConfig()
    {
        $loadedXml = CoreConfigModel::getConfig();
        if ($loadedXml->docaposteSignature->enable == 'true') {
            $config = [
                'url'                    => (string)$loadedXml->docaposteSignature->url,
                'user'                   => (string)$loadedXml->docaposteSignature->user,
                'password'               => (string)$loadedXml->docaposteSignature->password,
                'offerCode'              => (string)$loadedXml->docaposteSignature->offerCode,
                'organizationalUnitCode' => (string)$loadedXml->docaposteSignature->organizationalUnitCode
            ];
            return $config;
        } else {
            return false;
        }
    }

    public static function initiate($args = [])
    {
        $curlResponse = CurlModel::execSimple([
            'url'           => $args['config']['url'] . '/' . $args['config']['offerCode'] . '/transactions',
            'basicAuth'     => ['user' => $args['config']['user'], 'password' => $args['config']['password']],
            'method'        => 'POST',
            'multipartBody' => [
                'customRef'              => $args['documentId'],
                'organizationalUnitCode' => $args['config']['organizationalUnitCode'],
                'signatoriesCount'       => $args['signatoriesCount']
            ],
            'isXml'         => true
        ]);
        if ($curlResponse['code'] != 200) {
            return ['errors' => $curlResponse['response']->message ?? 'Error with initiate function'];
        }

        return (string)$curlResponse['response']->attributes()['id'];
    }

    public static function signatory($args = [])
    {
        $user         = UserModel::getById(['id' => $args['user_id'], 'select' => ['firstname', 'lastname', 'email']]);
        $curlResponse = CurlModel::execSimple([
            'url'           => $args['config']['url'] . '/transactions/' . $args['transactionId'] . '/signatory/' . $args['position'],
            'basicAuth'     => ['user' => $args['config']['user'], 'password' => $args['config']['password']],
            'method'        => 'POST',
            'multipartBody' => [
                'firstname'     => $user['firstname'],
                'lastname'      => $user['lastname'],
                'email'         => $user['email'],
                'signatureType' => 'PAD'
            ],
            'isXml' => true
        ]);
        if ($curlResponse['code'] != 200) {
            return ['errors' => $curlResponse['response']->message ?? 'Error with signatory function'];
        }

        return (string)$curlResponse['response']->attributes()['id'];
    }

    public static function signHashes($args = [])
    {
        $config = DigitalSignatureController::getConfig();
        $adr = AdrModel::getDocumentsAdr([
            'select' => ['path', 'filename'],
            'where'  => ['main_document_id = ?', 'type = ?'],
            'data'   => [$args['documentId'], 'ESIGN']
        ]);
        if (empty($adr)) {
            return ['errors' => 'Document does not exist'];
        }

        $docserver = DocserverModel::getByType(['type' => 'ESIGN', 'select' => ['path']]);
        if (empty($docserver['path']) || !file_exists($docserver['path'])) {
            return ['errors' => 'Docserver does not exist'];
        }

        $pathToDocument = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
        if (!is_file($pathToDocument) || !is_readable($pathToDocument)) {
            return ['errors' => 'Document not found on docserver or not readable'];
        }

        $libDir = CoreConfigModel::getLibrariesDirectory();
        if (!empty($libDir) && is_file($libDir . 'SetaPDF-Signer/library/SetaPDF/Autoload.php')) {
            require_once($libDir . 'SetaPDF-Signer/library/SetaPDF/Autoload.php');

            $tmpPath            = CoreConfigModel::getTmpPath();
            $signedDocumentPath = $tmpPath . $GLOBALS['id'] . '_' . rand() . '_signedDocument.pdf';
            $writer             = new \SetaPDF_Core_Writer_File($signedDocumentPath);
            $document           = \SetaPDF_Core_Document::loadByFilename($pathToDocument, $writer);
    
            $signer = new \SetaPDF_Signer($document);

            $user = UserModel::getById(['id' => $GLOBALS['id'], 'select' => ['firstname', 'lastname', 'email']]);
            $signer->setName($user['firstname'] . ' ' . $user['lastname'] . ' ('. $user['email'] . ')');
            $signer->setReason('Signature du document par ' . $user['firstname'] . ' ' . $user['lastname']);
            $signer->setLocation('Maarch Parapheur');

            $signer->setSignatureContentLength(self::$signatureLength);
            $signer->setAllowSignatureContentLengthChange(false);

            if (!empty($args['signatureInfo'])) {
                $image = \SetaPDF_Core_Image::getByPath($args['signatureInfo']['filePath']);
                $imageXObject = $image->toXObject($document);
                $width = $args['signatureInfo']['signWidth'];
                $height = $imageXObject->getHeight($width);

                $fieldName = $signer->addSignatureField(
                    'Signature_' . rand(),
                    $args['signatureInfo']['page'],
                    \SetaPDF_Signer_SignatureField::POSITION_LEFT_TOP,
                    ['x' => $args['signatureInfo']['positionX'], 'y' => -$args['signatureInfo']['positionY']],
                    $width,
                    $height + 50
                )->getQualifiedName();

                $signer->setSignatureFieldName($fieldName);

                $xObject = \SetaPDF_Core_XObject_Form::create($document, [0, 0, $width, $height + 50]);
                $canvas = $xObject->getCanvas();
                $imageXObject->draw($canvas, 0, 50, $width, $height);

                $font = new \SetaPDF_Core_Font_Type0_Subset(
                    $document,
                    __DIR__ . '/fonts/dejavu-fonts-ttf-2.37/ttf/DejaVuSans.ttf'
                );
    
                $textBlock = new \SetaPDF_Core_Text_Block($font, 7);
                $textBlock->setWidth($width);
                $textBlock->setLineHeight(14);
                $textBlock->setPadding(2);
                $textBlock->setText("Signé électroniquement par : " . $user['firstname'] . ' ' . $user['lastname'] . "\nLe " . date('c'));
                $textBlock->draw($canvas, 0, 30, $width, null);

                $appearance = new \SetaPDF_Signer_Signature_Appearance_XObject($xObject);
                $signer->setAppearance($appearance);
            } else {
                $fieldName = $signer->addSignatureField()->getQualifiedName();
                $signer->setSignatureFieldName($fieldName);
            }
    
            $tmpDocument = $signer->preSign(new \SetaPDF_Core_Writer_TempFile());
    
            $document = DocumentModel::getById(['select' => ['digital_signature_transaction_id'], 'id' => $args['documentId']]);
            if (empty($document['digital_signature_transaction_id'])) {
                return ['errors' => 'No digital_signature_transaction_id for resource : ' . $args['documentId']];
            }
            $curlResponse = CurlModel::execSimple([
                    'url'           => $config['url'] . '/transactions/' . $document['digital_signature_transaction_id'] . '/signHashes',
                    'basicAuth'     => ['user' => $config['user'], 'password' => $config['password']],
                    'method'        => 'POST',
                    'multipartBody' => [
                        'signatureId' => (integer)$args['signatureId'],
                        'hashes'      => hash_file('sha256', $tmpDocument->getHashFile()->getPath()),
                        'hashAlgo'    => 'SHA-256'
                    ],
                    'isXml' => true
                ]);
            if ($curlResponse['code'] != 200) {
                return ['errors' => $curlResponse['response']->message ?? 'Error with signHashes function'];
            }
        
            $signature = base64_decode((string)$curlResponse['response']->signature);

            try {
                $signer->saveSignature($tmpDocument, $signature);
            } catch (\SetaPDF_Signer_Exception_ContentLength $e) {
                self::$signatureLength = self::$signatureLength + 1000;
                DigitalSignatureController::signHashes($args);
                return true;
            }

            $workflow = WorkflowModel::get([
                'select' => [1],
                'where'  => ['mode = ?', 'main_document_id = ?', 'digital_signature_id != ?', 'process_date is null'],
                'data'   => ['sign', $args['documentId'], $args['signatureId']]
            ]);

            if (empty($workflow) && $args['isLastSignature']) {
                $signedDocumentPath = DigitalSignatureController::timestampHashes([
                    'config'             => $config,
                    'signedDocumentPath' => $signedDocumentPath,
                    'transactionId'      => $document['digital_signature_transaction_id'],
                    'fieldName'          => $fieldName
                ]);
                DigitalSignatureController::terminate(['config' => $config, 'transactionId' => $document['digital_signature_transaction_id']]);
            }

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
                'data'  => [$args['documentId'], 'ESIGN']
            ]);
            AdrModel::createDocumentAdr([
                'documentId'  => $args['documentId'],
                'type'        => 'ESIGN',
                'path'        => $storeInfos['path'],
                'filename'    => $storeInfos['filename'],
                'fingerprint' => $storeInfos['fingerprint']
            ]);
            unlink($signedDocumentPath);

            return true;
        } else {
            return ['errors' => 'SetaPDF-Signer library is not installed'];
        }
    }

    public static function timestampHashes($args = [])
    {
        $libDir = CoreConfigModel::getLibrariesDirectory();
        if (!empty($libDir) && is_file($libDir . 'SetaPDF-Signer/library/SetaPDF/Autoload.php')) {
            require_once($libDir . 'SetaPDF-Signer/library/SetaPDF/Autoload.php');

            $tmpPath            = CoreConfigModel::getTmpPath();
            $signedDocumentPath = $tmpPath . $GLOBALS['id'] . '_' . rand() . '_tsDocument.pdf';
            $writer             = new \SetaPDF_Core_Writer_File($signedDocumentPath);
            
            $document = \SetaPDF_Core_Document::loadByFilename($args['signedDocumentPath'], $writer);
            $signer   = new \SetaPDF_Signer($document);

            $signer->setSignatureContentLength(self::$signatureTimestampLength);
            $signer->setAllowSignatureContentLengthChange(false);

            $fieldName = $signer->addSignatureField()->getQualifiedName();
            $signer->setSignatureFieldName($fieldName);

            $tmpDocument = $signer->preTimestamp(new \SetaPDF_Core_Writer_TempFile());

            $curlResponse = CurlModel::execSimple([
                'url'           => $args['config']['url'] . '/transactions/' . $args['transactionId'] . '/timestampHashes',
                'basicAuth'     => ['user' => $args['config']['user'], 'password' => $args['config']['password']],
                'method'        => 'POST',
                'multipartBody' => [
                    'hashes'      => hash_file('sha256', $tmpDocument->getHashFile()->getPath()),
                    'hashAlgo'    => 'SHA-256'
                ],
                'isXml' => true
            ]);
            if ($curlResponse['code'] != 200) {
                return ['errors' => $curlResponse['response']->message ?? 'Error with timestamp function'];
            }
    
            $signature = base64_decode((string)$curlResponse['response']->timestamp);

            try {
                $signer->saveSignature($tmpDocument, $signature);
            } catch (\SetaPDF_Signer_Exception_ContentLength $e) {
                self::$signatureTimestampLength = self::$signatureTimestampLength + 1000;
                $ltvDocumentException = DigitalSignatureController::timestampHashes($args);
                return $ltvDocumentException;
            }

            $LtvDocument = $tmpPath . $GLOBALS['id'] . '_' . rand() . '_LtvDocument.pdf';

            $writer = new \SetaPDF_Core_Writer_File($LtvDocument);
            $document = \SetaPDF_Core_Document::loadByFilename($signedDocumentPath, $writer);

            $tmpTimestampP7b = $tmpPath . $GLOBALS['id'] . '_' . rand() . '_timestamp.p7b';
            $tmpTimestampPEM = $tmpPath . $GLOBALS['id'] . '_' . rand() . '_timestamp.pem';
            file_put_contents($tmpTimestampP7b, $signature);

            exec(escapeshellcmd('openssl pkcs7 -in '.$tmpTimestampP7b.' -inform DER -print_certs -out '.$tmpTimestampPEM));

            // Create a collection of trusted certificats:
            $trustedCertificates = new \SetaPDF_Signer_X509_Collection(\SetaPDF_Signer_Pem::extractFromFile($tmpTimestampPEM));

            $extraTrustedCertificate = false;
            if (!empty($args['extraCertificate'])) {
                $certificate = new \SetaPDF_Signer_X509_Certificate($args['extraCertificate']);
        
                $informationResolverManager = new \SetaPDF_Signer_InformationResolver_Manager();
                $informationResolverManager->addResolver(new \SetaPDF_Signer_InformationResolver_HttpCurlResolver());
        
                $certificates = [$certificate];
                while (count($certificates) > 0) {
                    $currentCertificate = array_pop($certificates);

                    $aia = $currentCertificate->getExtensions()->get(\SetaPDF_Signer_X509_Extension_AuthorityInformationAccess::OID);
                    if ($aia instanceof \SetaPDF_Signer_X509_Extension_AuthorityInformationAccess) {
                        foreach ($aia->fetchIssuers($informationResolverManager)->getAll() as $issuer) {
                            $trustedCertificates->add($issuer);
                            $certificates[] = $issuer;
                            $extraTrustedCertificate = true;
                        }
                    }
                }
            }

            if (empty($args['extraCertificate']) || (!empty($args['extraCertificate']) && $extraTrustedCertificate)) {
                // Create a collector instance
                $collector = new \SetaPDF_Signer_ValidationRelatedInfo_Collector($trustedCertificates);
                $vriData = $collector->getByFieldName($document, $args['fieldName']);
    
                $dss = new \SetaPDF_Signer_DocumentSecurityStore($document);
                $dss->addValidationRelatedInfoByFieldName(
                    $args['fieldName'],
                    $vriData->getCrls(),
                    $vriData->getOcspResponses(),
                    $vriData->getCertificates()
                );
            }

            $document->save()->finish();

            unlink($tmpTimestampP7b);
            unlink($tmpTimestampPEM);
            unlink($args['signedDocumentPath']);

            return $LtvDocument;
        }
    }

    public static function abort($args = [])
    {
        $config = DigitalSignatureController::getConfig();
        $document = DocumentModel::getById(['select' => ['digital_signature_transaction_id'], 'id' => $args['documentId']]);
        if (empty($document['digital_signature_transaction_id'])) {
            return ['errors' => 'No digital_signature_transaction_id for resource : ' . $args['documentId']];
        }
        $curlResponse = CurlModel::execSimple([
            'url'           => $config['url'] . '/transactions/' . $document['digital_signature_transaction_id'] . '/abort',
            'basicAuth'     => ['user' => $config['user'], 'password' => $config['password']],
            'method'        => 'POST',
            'multipartBody' => [
                'signatoryId' => $args['signatureId'],
                'abortReason' => 'Refusé dans Maarch Parapheur'
            ],
            'isXml'         => true
        ]);
        if ($curlResponse['code'] != 200) {
            return ['errors' => $curlResponse['response']->message ?? 'Error with terminate function'];
        }

        return true;
    }

    public static function proof($args = [])
    {
        $config = DigitalSignatureController::getConfig();
        $document = DocumentModel::getById(['select' => ['digital_signature_transaction_id'], 'id' => $args['documentId']]);
        if (empty($document['digital_signature_transaction_id'])) {
            return null;
        }
        $curlResponse = CurlModel::execSimple([
            'url'           => $config['url'] . '/transactions/' . $document['digital_signature_transaction_id'] . '/proof',
            'basicAuth'     => ['user' => $config['user'], 'password' => $config['password']],
            'method'        => 'GET',
            'headers'       => [CURLOPT_BINARYTRANSFER => true],
            'queryParams' => [
                'proofFormat' => 'PDF'
            ]
        ]);
        if ($curlResponse['code'] != 200) {
            $response = simplexml_load_string($curlResponse['response']);
            return ['errors' => (string)$response->message ?? 'Error with proof function'];
        }

        return (string)$curlResponse['response'];
    }

    public static function terminate($args = [])
    {
        $curlResponse = CurlModel::execSimple([
            'url'           => $args['config']['url'] . '/transactions/' . $args['transactionId'] . '/terminate',
            'basicAuth'     => ['user' => $args['config']['user'], 'password' => $args['config']['password']],
            'method'        => 'POST',
            'multipartBody' => [],
            'isXml'         => true
        ]);
        if ($curlResponse['code'] != 200) {
            return ['errors' => $curlResponse['response']->message ?? 'Error with terminate function'];
        }

        return true;
    }
}
