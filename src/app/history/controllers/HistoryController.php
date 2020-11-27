<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief History Controller
* @author dev@maarch.org
*/

namespace History\controllers;

use Attachment\models\AttachmentModel;
use Docserver\controllers\DocserverController;
use Docserver\models\AdrModel;
use Docserver\models\DocserverModel;
use Document\controllers\DigitalSignatureController;
use Document\controllers\DocumentController;
use Document\models\DocumentModel;
use Group\controllers\PrivilegeController;
use History\models\HistoryModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\LanguageController;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;
use Workflow\models\WorkflowModel;
use setasign\Fpdi\Tcpdf\Fpdi;

class HistoryController
{
    public static function add(array $args)
    {
        ValidatorModel::notEmpty($args, ['code', 'objectType', 'objectId', 'type', 'message']);
        ValidatorModel::stringType($args, ['code', 'objectType', 'type', 'message']);
        ValidatorModel::arrayType($args, ['data']);

        HistoryModel::create([
            'code'          => $args['code'],
            'object_type'   => $args['objectType'],
            'object_id'     => $args['objectId'],
            'type'          => $args['type'],
            'user_id'       => $GLOBALS['id'],
            'user'          => UserModel::getLabelledUserById(['id' => $GLOBALS['id']]),
            'message'       => $args['message'],
            'data'          => empty($args['data']) ? '{}' : json_encode($args['data']),
            'ip'            => empty($_SERVER['REMOTE_ADDR']) ? 'script' : $_SERVER['REMOTE_ADDR']
        ]);

        return true;
    }

    public function getByDocumentId(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        if (!DocumentController::hasRightById(['id' => $args['id'], 'userId' => $GLOBALS['id']]) && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $document = DocumentModel::getById(['select' => [1], 'id' => $args['id']]);
        if (empty($document)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
        }

        $formattedHistory = HistoryController::getFormattedHistory(['id' => $args['id']]);
        if (!empty($formattedHistory['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $formattedHistory['errors']]);
        }

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'history',
            'objectId'      => $args['id'],
            'type'          => 'VIEW',
            'message'       => '{documentHistoryViewed}',
            'data'          => ['objectType' => 'main_documents']
        ]);

        $queryParams = $request->getQueryParams();
        if (!isset($queryParams['mode']) || $queryParams['mode'] == 'json') {
            return $response->withJson(['history' => $formattedHistory['formattedHistory']]);
        } else {
            $historyXml = HistoryController::arrayToXml(['data' => $formattedHistory['formattedHistory'], 'xml' => false]);
            $response->write($historyXml);
            $response = $response->withAddedHeader('Content-Disposition', "inline; filename=maarch_history.xml");
            return $response->withHeader('Content-Type', 'application/xml');
        }

        return $response->withJson(['history' => $formattedHistory['formattedHistory']]);
    }

    public static function getFormattedHistory($args = [])
    {
        $adr = AdrModel::getDocumentsAdr([
            'select'    => ['filename', 'fingerprint', 'path'],
            'where'     => ['main_document_id = ?', 'type = ?'],
            'data'      => [$args['id'], 'DOC']
        ]);
        $adr = $adr[0];

        $docserver = DocserverModel::getByType(['type' => 'DOC', 'select' => ['path']]);
        if (empty($docserver['path']) || !file_exists($docserver['path'])) {
            return ['errors' => 'Docserver does not exist'];
        }

        $pathToDocument = $docserver['path'] . $adr['path'] . $adr['filename'];
        unset($adr['path']);

        $fileContent = file_get_contents($pathToDocument);
        if ($fileContent !== false) {
            $finfo    = new \finfo(FILEINFO_MIME_TYPE);
            $adr['mimeType'] = $finfo->buffer($fileContent);
        }

        $certificate = DocumentController::getPdfCertificate(['path' => $pathToDocument, 'documentId' => $args['id']]);
        if (!empty($certificate)) {
            $adr['certificate'] = $certificate;
        }

        $history = HistoryModel::get([
            'select'    => ['code', 'type', '"user"', 'date', 'message', 'data', 'user_id', 'ip'],
            'where'     => ["(object_type = ? AND object_id = ?) OR (data->>'mainDocumentId' = ?)"],
            'data'      => ['main_documents', $args['id'], $args['id']],
            'orderBy'   => ['date']
        ]);

        $formattedHistory = [];

        $lang       = LanguageController::get();
        $langKeys   = [];
        $langValues = [];
        foreach ($lang as $key => $value) {
            $langKeys[]   = "/{{$key}}/";
            $langValues[] = $value;
        }

        foreach ($history as $value) {
            $date = new \DateTime($value['date']);

            $user = UserModel::getById(['id' => $value['user_id'], 'select' => ['id', 'email', 'firstname', 'lastname']]);
            $user['ip'] = $value['ip'];

            $data = json_decode($value['data'], true);
            $formatted = [
                'code'     => $value['code'],
                'type'     => $value['type'],
                'user'     => $user,
                'date'     => $date->format('d-m-Y H:i'),
                'message'  => preg_replace($langKeys, $langValues, $value['message']),
                'data'     => $data
            ];

            if ($value['type'] == 'ACTION' && $data['actionId'] == 1 && $data['mode'] == 'sign') {
                $formatted['document'] = $adr;
            }

            $formattedHistory[] = $formatted;
        }

        return ['formattedHistory' => $formattedHistory];
    }

    public function getHistoryProofByDocumentId(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        if (!DocumentController::hasRightById(['id' => $args['id'], 'userId' => $GLOBALS['id']]) && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $workflow = WorkflowModel::get([
            'select' => [1],
            'where'  => ['main_document_id = ?', 'status is null'],
            'data'   => [$args['id']]
        ]);
        if (!empty($workflow)) {
            return $response->withStatus(403)->withJson(['errors' => 'The document is still being processed']);
        }

        $proofDocument = DigitalSignatureController::proof(['documentId' => $args['id']]);
        if (!empty($proofDocument['errors'])) {
            return $response->withStatus(403)->withJson(['errors' => $proofDocument['errors']]);
        }

        $documentPathToZip = [];
        $tmpPath = CoreConfigModel::getTmpPath();

        if (!empty($proofDocument)) {
            $docapostePath = $tmpPath . 'docaposteProof' . $GLOBALS['id'] . "_" . rand() . '.pdf';
            file_put_contents($docapostePath, $proofDocument);
            $documentPathToZip[] = ['path' => $docapostePath, 'filename' => 'docaposteProof.pdf'];
        }

        $queryParams = $request->getQueryParams();
        if (!$queryParams['onlyProof'] || empty($proofDocument)) {
            $formattedHistory = HistoryController::getFormattedHistory(['id' => $args['id']]);
            $historyXml       = HistoryController::arrayToXml(['data' => $formattedHistory['formattedHistory'], 'xml' => false]);
            $historyXmlPath = $tmpPath . 'maarchProof' . $GLOBALS['id'] . "_" . rand() . '.xml';
            file_put_contents($historyXmlPath, $historyXml);

            $loadedXml  = simplexml_load_file($historyXmlPath);
            $historyXml = HistoryController::formatXml($loadedXml);
            file_put_contents($historyXmlPath, $historyXml);

            $documentPathToZip[] = ['path' => $historyXmlPath, 'filename' => 'maarchProof.xml'];
        }

        if ($queryParams['onlyProof']) {
            if (!empty($proofDocument)) {
                $content  = $proofDocument;
                $format   = 'pdf';
                $mimeType = 'application/pdf';
            } else {
                $content  = $historyXml;
                $format   = 'xml';
                $mimeType = 'application/xml';
            }
        } else {
            $mainDocument = DocumentController::getContentPath(['id' => $args['id'], 'eSignDocument' => $queryParams['eSignDocument']]);
            if (!empty($mainDocument['errors'])) {
                return $response->withStatus($mainDocument['code'])->withJson(['errors' => $mainDocument['errors']]);
            }
            $documentPathToZip[] = ['path' => $mainDocument['path'], 'filename' => 'mainDocument.pdf'];

            $attachments = AttachmentModel::getByDocumentId(['select' => ['id', 'title'], 'documentId' => $args['id']]);
            foreach ($attachments as $key => $attachment) {
                $adr = AdrModel::getAttachmentsAdr([
                    'select'  => ['type',' path', 'filename', 'fingerprint'],
                    'where'   => ['attachment_id = ?', 'type = ?'],
                    'data'    => [$attachment['id'], 'ATTACH']
                ]);
                if (empty($adr[0])) {
                    continue;
                }
        
                $docserver = DocserverModel::getByType(['type' => $adr[0]['type'], 'select' => ['path']]);
                if (empty($docserver['path']) || !file_exists($docserver['path'])) {
                    return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
                }
        
                $pathToDocument = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
                if (!is_file($pathToDocument)) {
                    return $response->withStatus(404)->withJson(['errors' => 'Document not found on docserver']);
                }
        
                $fingerprint = DocserverController::getFingerPrint(['path' => $pathToDocument]);
                if ($adr[0]['fingerprint'] != $fingerprint) {
                    return $response->withStatus(400)->withJson(['errors' => 'Fingerprint do not match']);
                }
                $documentPathToZip[] = ['path' => $pathToDocument, 'filename' => 'attachment_' . $key . '.pdf'];
            }

            $document = DocumentModel::getById(['select' => ['sender', 'creation_date', 'notes'], 'id' => $args['id']]);
            
            $notes = [];
            $documentNotes = json_decode($document['notes'], true);
            if (!empty($documentNotes)) {
                $notes[] = $documentNotes;
            }

            $workflow = WorkflowModel::getByDocumentId(['select' => ['user_id', 'process_date', 'note'], 'documentId' => $args['id'], 'orderBy' => ['"order"']]);
            foreach ($workflow as $step) {
                if (!empty($step['note'])) {
                    $notes[] = [
                        'creator'      => UserModel::getLabelledUserById(['id' => $step['user_id']]),
                        'creationDate' => $step['process_date'],
                        'value'        => $step['note']
                    ];
                }
            }

            if (!empty($notes)) {
                $documentPathToZip[] = HistoryController::createNotesFile(['notes' => $notes]);
            }

            $content = HistoryController::createZip(['documents' => $documentPathToZip]);
            if (!empty($content['errors'])) {
                return $response->withStatus(400)->withJson(['errors' => $content['errors']]);
            }
            $content  = $content['fileContent'];
            $format   = 'zip';
            $mimeType = 'application/xml';
        }

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'history',
            'objectId'      => $args['id'],
            'type'          => 'VIEW',
            'message'       => '{documentProofViewed}',
            'data'          => ['objectType' => 'main_documents']
        ]);

        if (empty($queryParams['mode']) || $queryParams['mode'] == 'base64') {
            return $response->withJson(['encodedProofDocument' => base64_encode($content), 'format' => $format]);
        } else {
            $response->write($content);
            $response = $response->withAddedHeader('Content-Disposition', "inline; filename=maarch_history_proof." . $format);
            return $response->withHeader('Content-Type', $mimeType);
        }
    }

    public static function createZip(array $aArgs)
    {
        $zip = new \ZipArchive();

        $tmpPath     = CoreConfigModel::getTmpPath();
        $zipFilename = $tmpPath . 'archivedProof' . $GLOBALS['id'] . '_' . rand() . '.zip';

        if ($zip->open($zipFilename, \ZipArchive::CREATE) === true) {
            foreach ($aArgs['documents'] as $document) {
                $zip->addFile($document['path'], $document['filename']);
            }

            $zip->close();

            $fileContent = file_get_contents($zipFilename);
            unlink($zipFilename);
            return ['fileContent' => $fileContent];
        } else {
            return ['errors' => 'Cannot create archive'];
        }
    }

    public function formatXml($simpleXMLElement)
    {
        $xmlDocument = new \DOMDocument('1.0');
        $xmlDocument->preserveWhiteSpace = false;
        $xmlDocument->formatOutput = true;
        $xmlDocument->loadXML($simpleXMLElement->asXML());

        return $xmlDocument->saveXML();
    }

    public static function arrayToXml($args = [])
    {
        if ($args['xml'] === false) {
            $args['xml'] = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><root></root>', null, false);
            $historyNode = 'History';
        }
    
        foreach ($args['data'] as $key => $value) {
            $node = $historyNode ?? $key;

            if (is_array($value)) {
                HistoryController::arrayToXml(['data' => $value, 'xml' => $args['xml']->addChild(str_replace(' ', '', $node))]);
            } else {
                $args['xml']->addChild(str_replace(' ', '', $node), $value);
            }
        }
    
        return $args['xml']->asXML();
    }

    public static function createNotesFile($args = [])
    {
        $pdf = new Fpdi('P', 'pt');
        $pdf->setPrintHeader(false);

        $pdf->AddPage();
        $dimensions     = $pdf->getPageDimensions();
        $widthNoMargins = $dimensions['w'] - $dimensions['rm'] - $dimensions['lm'];
        $bottomHeight   = $dimensions['h'] - $dimensions['bm'];
        $widthNotes     = $widthNoMargins / 2;

        if (($pdf->GetY() + 80) > $bottomHeight) {
            $pdf->AddPage();
        }

        $pdf->SetFont('', 'B', 11);
        $pdf->Cell(0, 15, 'Notes', 0, 2, 'L', false);

        $pdf->SetY($pdf->GetY() + 2);
        $pdf->SetFont('', '', 10);

        foreach ($args['notes'] as $note) {
            if (($pdf->GetY() + 65) > $bottomHeight) {
                $pdf->AddPage();
            }
            $pdf->SetFont('', 'B', 10);
            $pdf->Cell($widthNotes, 20, $note['creator'], 1, 0, 'L', false);
            $pdf->SetFont('', '', 10);
            $pdf->Cell($widthNotes, 20, $note['creationDate'], 1, 1, 'L', false);
            $pdf->MultiCell(0, 40, $note['value'], 1, 'L', false);
            $pdf->SetY($pdf->GetY() + 5);
        }

        $noteFileContent = $pdf->Output('', 'S');
        $tmpPath         = CoreConfigModel::getTmpPath();
        $notesFilePath   = $tmpPath . 'notes' . $GLOBALS['id'] . "_" . rand() . '.pdf';
        file_put_contents($notesFilePath, $noteFileContent);
        $documentPathToZip = ['path' => $notesFilePath, 'filename' => 'notes.pdf'];

        return $documentPathToZip;
    }

    public function getByUserId(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id'] && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        $user = UserModel::getById(['select' => [1], 'id' => $args['id']]);
        if (empty($user)) {
            return $response->withStatus(400)->withJson(['errors' => 'User does not exist']);
        }

        $history = HistoryModel::get([
            'select'    => ['code', 'type', '"user"', 'date', 'message', 'data'],
            'where'     => ["(object_type = ? AND object_id = ?) OR (data->>'userId' = ?) OR (user_id = ?)"],
            'data'      => ['main_documents', $args['id'], $args['id'], $args['id']],
            'orderBy'   => ['date']
        ]);

        $formattedHistory = [];

        $lang = LanguageController::get();
        $langKeys = [];
        $langValues = [];
        foreach ($lang as $key => $value) {
            $langKeys[] = "/{{$key}}/";
            $langValues[] = $value;
        }

        foreach ($history as $value) {
            $date = new \DateTime($value['date']);

            $formattedHistory[] = [
                'code'          => $value['code'],
                'type'          => $value['type'],
                'user'          => $value['user'],
                'date'          => $date->format('d-m-Y H:i'),
                'message'       => preg_replace($langKeys, $langValues, $value['message']),
                'data'          => json_decode($value['data'], true)
            ];
        }

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'history',
            'objectId'      => $args['id'],
            'type'          => 'VIEW',
            'message'       => '{userHistoryViewed}',
            'data'          => ['objectType' => 'users']
        ]);

        return $response->withJson(['history' => $formattedHistory]);
    }
}
