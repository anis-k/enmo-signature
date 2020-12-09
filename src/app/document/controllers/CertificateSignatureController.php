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

use Document\models\DocumentModel;
use Group\controllers\PrivilegeController;
use History\controllers\HistoryController;
use Slim\Http\Request;
use Slim\Http\Response;

class CertificateSignatureController
{
    public function getHashedCertificate(array $args)
    {
        if (isset($_SESSION['tmpDocument'])) {
            @unlink($_SESSION['tmpDocument']->getWriter()->getPath());
        }                $data = json_decode(file_get_contents('php://input'));
        if (!isset($data->certificate)) {
            throw new Exception('Missing certificate!');
        }                // load the PDF document
        $document = \SetaPDF_Core_Document::loadByString($args['document']);
        // create a signer instance
        $signer = new \SetaPDF_Signer($document);
        // create a module instance
        $module = new \SetaPDF_Signer_Signature_Module_Pades();                // create a certificate instance
        $certificate = new \SetaPDF_Signer_X509_Certificate($data->certificate);                // pass the user certificate to the module
        $module->setCertificate($certificate);                // setup information resolver manager
        $informationResolverManager = new \SetaPDF_Signer_InformationResolver_Manager();
        $informationResolverManager->addResolver(new \SetaPDF_Signer_InformationResolver_HttpCurlResolver());                $extraCerts = new \SetaPDF_Signer_X509_Collection();                // get issuer certificates
        if (isset($data->useAIA) && $data->useAIA) {
            $certificates = [$certificate];
            while (count($certificates) > 0) {
                /** @var \SetaPDF_Signer_X509_Certificate $currentCertificate */
                $currentCertificate = array_pop($certificates);
                /** @var \SetaPDF_Signer_X509_Extension_AuthorityInformationAccess $aia */
                $aia = $currentCertificate->getExtensions()->get(\SetaPDF_Signer_X509_Extension_AuthorityInformationAccess::OID);
                if ($aia instanceof \SetaPDF_Signer_X509_Extension_AuthorityInformationAccess) {
                    foreach ($aia->fetchIssuers($informationResolverManager)->getAll() as $issuer) {
                        $extraCerts->add($issuer);
                        $certificates[] = $issuer;
                    }
                }
            }
        }                $module->setExtraCertificates($extraCerts);                $signatureContentLength = 10000;
        foreach ($extraCerts->getAll() as $extraCert) {
            $signatureContentLength += (strlen($extraCert->get(\SetaPDF_Signer_X509_Format::DER)) * 2);
        }                $signer->setSignatureContentLength($signatureContentLength);                unset($_SESSION['tsUrl']);
        // get timestamp information and use it
        if (isset($data->useTimestamp) && $data->useTimestamp) {
            /** @var \SetaPDF_Signer_X509_Extension_TimeStamp $ts */
            $ts = $certificate->getExtensions()->get(\SetaPDF_Signer_X509_Extension_TimeStamp::OID);
            if ($ts && $ts->getVersion() === 1 && $ts->requiresAuth() === false) {
                $_SESSION['tsUrl'] = $ts->getLocation();
                $signer->setSignatureContentLength($signatureContentLength + 6000);
            }
        }                // you may use an own temporary file handler
        $tempPath = \SetaPDF_Core_Writer_TempFile::createTempPath();                // prepare the PDF
        $_SESSION['tmpDocument'] = $signer->preSign(
            new \SetaPDF_Core_Writer_File($tempPath),
            $module
        );                $_SESSION['module'] = $module;                // prepare the response
        $responseData = [
            'dataToSign' => \SetaPDF_Core_Type_HexString::str2hex(
                $module->getDataToSign($_SESSION['tmpDocument']->getHashFile())
            ),
            'extraCerts' => array_map(function (\SetaPDF_Signer_X509_Certificate $cert) {
                return $cert->get(\SetaPDF_Signer_X509_Format::PEM);
            }, $extraCerts->getAll()),
            'tsUrl'      => isset($_SESSION['tsUrl']) ? $_SESSION['tsUrl'] : false
        ];                // send it
//                header('Content-Type: application/json; charset=utf-8');
//                echo json_encode($response);
        return $response->withJson($responseData);
        break;            // This action embeddeds the signature in the CMS container
        // and optionally requests and embeds the timestamp    }
}
