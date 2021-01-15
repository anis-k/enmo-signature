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
use Configuration\models\ConfigurationModel;
use Docserver\controllers\DocserverController;
use Docserver\models\AdrModel;
use Docserver\models\DocserverModel;
use Document\controllers\DigitalSignatureController;
use Document\controllers\DocumentController;
use Document\models\DocumentModel;
use Email\models\EmailModel;
use Group\controllers\PrivilegeController;
use Group\models\GroupModel;
use History\models\HistoryModel;
use Respect\Validation\Validator;
use setasign\Fpdi\Tcpdf\Fpdi;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\LanguageController;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\PasswordModel;
use SrcCore\models\TextFormatModel;
use SrcCore\models\ValidatorModel;
use User\models\SignatureModel;
use User\models\UserModel;
use Workflow\models\WorkflowModel;
use Workflow\models\WorkflowTemplateModel;

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

    public function get(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_history'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $queryParams = $request->getQueryParams();
        $body = $request->getParsedBody();

        $limit = 25;
        if (!empty($queryParams['limit']) && is_numeric($queryParams['limit'])) {
            $limit = (int)$queryParams['limit'];
        }
        $offset = 0;
        if (!empty($queryParams['offset']) && is_numeric($queryParams['offset'])) {
            $offset = (int)$queryParams['offset'];
        }

        $where = [];
        $data = [];
        if (!empty($body['users']) && is_array($body['users'])) {
            $where[] = 'user_id in (?)';
            $data[]  = $body['users'];
        }
        if (!empty($body['user'])) {
            $userWhere = '"user" ilike ?';
            $data[] = "%{$body['user']}%";

            $matchedUsers = UserModel::get(['select' => ['id'], 'where' => ["CONCAT(firstname,' ',lastname) ilike ?"], 'data' => ["%{$body['user']}%"]]);
            if (!empty($matchedUsers)) {
                $matchedUsers = array_column($matchedUsers, 'id');
                $userWhere .= ' OR user_id in (?)';
                $userWhere = "({$userWhere})";
                $data[]  = $matchedUsers;
            }
            $where[] = $userWhere;
        }

        if (!empty($body['date']['start'])) {
            $where[] = 'date > ?';
            $data[]  = TextFormatModel::getStartDayDate(['date' => $body['date']['start']]);
        }
        if (!empty($body['date']['end'])) {
            $where[] = 'date < ?';
            $data[]  = TextFormatModel::getEndDayDate(['date' => $body['date']['end']]);
        }
        if (!empty($body['messageTypes']) && is_array($body['messageTypes'])) {
            $queryTypes = '{';
            foreach ($body['messageTypes'] as $key => $messageType) {
                if ($key > 0) {
                    $queryTypes .= ',';
                }
                $queryTypes .= "\"%{{$messageType}}%\"";
            }
            $queryTypes .= '}';
            $where[] = 'message like any (?)';
            $data[]  = $queryTypes;
        }

        $history = HistoryModel::get([
            'select'    => ['id', 'code', 'type', '"user"', 'date', 'message', 'data', 'user_id', 'ip', 'object_id', 'object_type', 'count(1) OVER()'],
            'where'     => $where,
            'data'      => $data,
            'orderBy'   => ['date DESC'],
            'limit'     => $limit,
            'offset'    => $offset
        ]);
        $total = $history[0]['count'] ?? 0;
        $history = array_column($history, null, 'id');

        $formattedHistory = [];

        $lang       = LanguageController::get();
        $langKeys   = [];
        $langValues = [];
        foreach ($lang as $key => $value) {
            $langKeys[]   = "/{{$key}}/";
            $langValues[] = $value;
        }

        $objectTypes = ['attachment', 'attachments', 'configurations', 'emails', 'document', 'groups', 'main_documents', 'password_rules', 'signatures', 'workflowTemplates', 'users'];

        foreach ($objectTypes as $objectType) {
            $filteredHistory = array_filter($history, function ($element) use ($objectType) {
                return $element['object_type'] == $objectType;
            });
            if (!empty($filteredHistory)) {
                $filteredHistoryIds = array_column($filteredHistory, 'object_id', 'id');
                $objects = [];
                if (in_array($objectType,  ['attachment', 'attachments'])) {
                    $objects = AttachmentModel::get(['select' => ['id', 'title'], 'where' => ['id in (?)'], 'data' => [$filteredHistoryIds]]);
                } elseif ($objectType == 'configurations') {
                    $objects = ConfigurationModel::get(['select' => ['id', 'label as title'], 'where' => ['id in (?)'], 'data' => [$filteredHistoryIds]]);
                }  elseif ($objectType == 'emails') {
                    $objects = EmailModel::get(['select' => ['id', 'subject as title'], 'where' => ['id in (?)'], 'data' => [$filteredHistoryIds]]);
                } elseif ($objectType == 'groups') {
                    $objects = GroupModel::get(['select' => ['id', 'label as title'], 'where' => ['id in (?)'], 'data' => [$filteredHistoryIds]]);
                } elseif (in_array($objectType, ['main_documents', 'document'])) {
                    $objects = DocumentModel::get(['select' => ['id', 'title'], 'where' => ['id in (?)'], 'data' => [$filteredHistoryIds]]);
                } elseif ($objectType == 'password_rules') {
                    $objects = PasswordModel::getRules(['select' => ['id', 'label as title'], 'where' => ['id in (?)'], 'data' => [$filteredHistoryIds]]);
                } elseif ($objectType == 'workflowTemplates') {
                    $objects = WorkflowTemplateModel::get(['select' => ['id', 'title'], 'where' => ['id in (?)'], 'data' => [$filteredHistoryIds]]);
                } elseif ($objectType == 'users') {
                    $objects = UserModel::get(['select' => ['id', "(firstname || ' ' || lastname) as title"], 'where' => ['id in (?)'], 'data' => [$filteredHistoryIds]]);
                }
                $objects = array_column($objects, 'title', 'id');

                foreach ($filteredHistoryIds as $key => $objectId) {
                    $history[$key]['label'] = $objects[$objectId];
                }
            }
        }

        foreach ($history as $value) {
            $date = new \DateTime($value['date']);

            $data = json_decode($value['data'], true);

            $message = preg_replace($langKeys, $langValues, $value['message']);

            foreach (PrivilegeController::PRIVILEGES as $privilege) {
                $message = str_replace($privilege['id'], $lang[$privilege['id']], $message);
            }

            if ($value['type'] == 'ACTION') {
                $message = str_replace('VAL', $lang['documentValidateAs'], $message);
                $message = str_replace('REF', $lang['documentRefusedAs'], $message);

                if (!empty($data['mode'])) {
                    $message .= ' ' . $lang[$data['mode'] . 'User'];
                    if (!empty($data['signatureMode']) && $data['mode'] == 'sign') {
                        $message .= ' (' . $lang[$data['signatureMode'] . 'User'] . ')';
                    }
                }
            }

            $formattedHistory[] = [
                'code'        => $value['code'],
                'objectId'    => $value['object_id'],
                'objectType'  => $value['object_type'],
                'type'        => $value['type'],
                'userId'      => $value['user_id'],
                'user'        => $value['user'],
                'date'        => $date->format('c'),
                'ip'          => $value['ip'],
                'message'     => $message,
                'data'        => $data,
                'objectLabel' => $value['label']
            ];
        }

        return $response->withJson(['history' => $formattedHistory, 'total' => $total]);
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
            'objectType'    => 'main_documents',
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
    }

    public function getMessageTypes(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_history'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $rawHistory = DatabaseModel::select([
            'select'    => ['DISTINCT h.msg'],
            'table'     => ["(SELECT REGEXP_MATCHES(message, '{[a-zA-Z]+}', 'g') AS msg FROM history) h"],
        ]);

        $messageTypes = [];
        foreach ($rawHistory as $value) {
            $messageTypes[] = str_replace(['"', '{', '}'], '', $value['msg']);
        }

        return $response->withJson(['messageTypes' => $messageTypes]);
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
                'date'     => $date->format('c'),
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

        $workflow = WorkflowModel::getByDocumentId(['select' => ['user_id', 'process_date', 'note', 'status', 'signature_mode'], 'documentId' => $args['id'], 'orderBy' => ['"order"']]);
        $hasCertificate       = false;
        $workflowTerminated   = true;
        $workflowCompleted    = true;
        foreach ($workflow as $step) {
            if (!$hasCertificate && in_array($step['signature_mode'], ['eidas', 'rgs_2stars', 'rgs_2stars_timestamped', 'inca_card', 'inca_card_eidas'])) {
                $hasCertificate = true;
            }
            if (empty($step['status'])) {
                $workflowTerminated = false;
            }
            if (in_array($step['status'], ['REF', 'STOP'])) {
                $workflowCompleted = false;
            }
        }
        if (empty($workflow) || !$workflowTerminated) {
            return $response->withStatus(403)->withJson(['errors' => 'The document is still being processed']);
        }

        if ($workflowCompleted) {
            $proofDocument = DigitalSignatureController::proof(['documentId' => $args['id']]);
            if (!empty($proofDocument['errors'])) {
                return $response->withStatus(403)->withJson(['errors' => $proofDocument['errors']]);
            }
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
            $eSignDocument = $workflowCompleted && ($queryParams['eSignDocument'] ?? $hasCertificate);

            $mainDocument = DocumentController::getContentPath(['id' => $args['id'], 'eSignDocument' => $eSignDocument]);
            if (!empty($mainDocument['errors'])) {
                return $response->withStatus($mainDocument['code'])->withJson(['errors' => $mainDocument['errors']]);
            }

            $document = DocumentModel::getById(['select' => ['sender', 'creation_date', 'notes', 'title'], 'id' => $args['id']]);
            $filename = TextFormatModel::createFilename([
                'label'     => $document['title'],
                'extension' => 'pdf'
            ]);
            $documentPathToZip[] = ['path' => $mainDocument['path'], 'filename' => 'mainDocument_'.$filename];

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
                $filename = TextFormatModel::createFilename([
                    'label'     => $attachment['title'],
                    'extension' => 'pdf'
                ]);
                $documentPathToZip[] = ['path' => $pathToDocument, 'filename' => 'attachment_' . $key . '_' . $filename];
            }
            
            $notes = [];
            $documentNotes = json_decode($document['notes'], true);
            if (!empty($documentNotes)) {
                $notes[] = $documentNotes;
            }

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
            $mimeType = 'application/zip';
        }

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'main_documents',
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

            $date = new \DateTime($note['creationDate']);
            $pdf->SetFont('', 'B', 10);
            $pdf->Cell($widthNotes, 20, $note['creator'], 1, 0, 'L', false);
            $pdf->SetFont('', '', 10);
            $pdf->Cell($widthNotes, 20, $date->format('d-m-Y H:i'), 1, 1, 'L', false);
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
            'objectType'    => 'users',
            'objectId'      => $args['id'],
            'type'          => 'VIEW',
            'message'       => '{userHistoryViewed}',
            'data'          => ['objectType' => 'users']
        ]);

        return $response->withJson(['history' => $formattedHistory]);
    }
}
