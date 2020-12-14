<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Resource Controller
* @author dev@maarch.org
*/

namespace Document\controllers;

use Attachment\controllers\AttachmentController;
use Docserver\models\AdrModel;
use Email\controllers\EmailController;
use Group\controllers\PrivilegeController;
use Respect\Validation\Validator;
use setasign\Fpdi\Tcpdf\Fpdi;
use SrcCore\models\CoreConfigModel;
use Attachment\models\AttachmentModel;
use Docserver\controllers\DocserverController;
use Docserver\models\DocserverModel;
use Document\models\DocumentModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use User\controllers\SignatureController;
use User\models\UserModel;
use History\controllers\HistoryController;
use Workflow\models\WorkflowModel;

class DocumentController
{
    const ACTIONS   = [1 => 'VAL', 2 => 'REF'];
    const MODES     = ['visa', 'sign', 'note'];

    public function get(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();

        $queryParams['offset'] = empty($queryParams['offset']) ? 0 : (int)$queryParams['offset'];
        $queryParams['limit'] = empty($queryParams['limit']) ? 0 : (int)$queryParams['limit'];

        $userId = $GLOBALS['id'];
        if (!empty($queryParams['userId'])) {
            if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }
            if (!Validator::intVal()->notEmpty()->validate($queryParams['userId'])) {
                return $response->withStatus(400)->withJson(['errors' => 'QueryParams userId is not an integer']);
            }
            $userId = $queryParams['userId'];
        }

        $substitutedUsers = UserModel::get(['select' => ['id'], 'where' => ['substitute = ?'], 'data' => [$userId]]);

        $users = [$userId];
        foreach ($substitutedUsers as $value) {
            $users[] = $value['id'];
        }

        $workflowSelect = "SELECT id FROM workflows ws WHERE workflows.main_document_id = main_document_id AND process_date IS NULL AND status IS NULL ORDER BY \"order\" LIMIT 1";
        $where = ['user_id in (?)', "(id) in ({$workflowSelect})"];
        $data = [$users];
        $countWorkflows = WorkflowModel::get([
            'select'    => ['count(mode)', 'mode'],
            'where'     => $where,
            'data'      => $data,
            'groupBy'   => ['mode']
        ]);

        if (!empty($queryParams['mode']) && in_array($queryParams['mode'], DocumentController::MODES)) {
            $where[] = 'mode = ?';
            $data[] = $queryParams['mode'];
        }

        $workflows = WorkflowModel::get([
            'select'    => ['main_document_id', 'mode', 'user_id'],
            'where'     => $where,
            'data'      => $data
        ]);
        $documentIds = [];
        $workflowsShortcut = [];
        foreach ($workflows as $workflow) {
            $documentIds[] = $workflow['main_document_id'];
            $workflowsShortcut[$workflow['main_document_id']] = $workflow;
        }

        $documents = [];
        if (!empty($documentIds)) {
            $where = ['id in (?)'];
            $data = [$documentIds];
            if (!empty($queryParams['search'])) {
                $where[] = '(reference ilike ? OR translate(title, \'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ\', \'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyrr\') ilike translate(?, \'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿŔŕ\', \'aaaaaaaceeeeiiiidnoooooouuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyrr\'))';
                $data[] = "%{$queryParams['search']}%";
                $data[] = "%{$queryParams['search']}%";
            }

            $documents = DocumentModel::get([
                'select'    => ['id', 'title', 'reference', 'count(1) OVER()'],
                'where'     => $where,
                'data'      => $data,
                'limit'     => $queryParams['limit'],
                'offset'    => $queryParams['offset'],
                'orderBy'   => ['creation_date desc']
            ]);
        }

        $count = ['visa' => 0, 'sign' => 0, 'note' => 0, 'current' => empty($documents[0]['count']) ? 0 : $documents[0]['count']];
        foreach ($documents as $key => $document) {
            unset($documents[$key]['count']);
            $documents[$key]['mode'] = $workflowsShortcut[$document['id']]['mode'];
            $documents[$key]['owner'] = $workflowsShortcut[$document['id']]['user_id'] == $userId;
        }
        
        foreach ($countWorkflows as $mode) {
            $count[$mode['mode']] = $mode['count'];
        }

        return $response->withJson(['documents' => $documents, 'count' => $count]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'userId' => $GLOBALS['id']]) && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $document = DocumentModel::getById(['select' => ['*'], 'id' => $args['id']]);
        if (empty($document)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
        }

        $adr = AdrModel::getDocumentsAdr([
            'select'  => ['count(1)'],
            'where'   => ['main_document_id = ?', 'type != ?', 'type != ?'],
            'data'    => [$args['id'], 'DOC', 'ESIGN']
        ]);
        if (empty($adr[0]['count']) && $document['status'] != 'CONVERTING') {
            $configPath = CoreConfigModel::getConfigPath();
            exec("php src/app/convert/scripts/ThumbnailScript.php '{$configPath}' {$args['id']} 'document' '{$GLOBALS['id']}' > /dev/null");
            $adr[0]['count'] = 1;
        }

        $formattedDocument = [
            'id'                => $document['id'],
            'title'             => $document['title'],
            'reference'         => $document['reference'],
            'description'       => $document['description'],
            'sender'            => $document['sender'],
            'notes'             => !empty($document['notes']) ? json_decode($document['notes'], true) : null,
            'creationDate'      => $document['creation_date'],
            'modificationDate'  => $document['modification_date'],
            'pages'             => $adr[0]['count'],
            'status'            => $document['status']
        ];
        if (!empty($document['deadline'])) {
            $date = new \DateTime($document['deadline']);
            $formattedDocument['deadline'] = $date->format('d-m-Y H:i');
        }

        $formattedDocument['metadata'] = [];
        $metadata = json_decode($document['metadata'], true);
        if (is_array($metadata)) {
            foreach ($metadata as $key => $value) {
                $formattedDocument['metadata'][] = ['label' => $key, 'value' => $value];
            }
        }

        $workflow = WorkflowModel::getByDocumentId(['select' => ['user_id', 'mode', 'process_date', 'signature_mode', 'status', 'note', 'signature_positions', 'date_positions'], 'documentId' => $args['id'], 'orderBy' => ['"order"']]);
        $currentFound = false;
        $currentId = null;
        foreach ($workflow as $value) {
            if (!empty($value['process_date'])) {
                $date = new \DateTime($value['process_date']);
                $value['process_date'] = $date->format('d-m-Y H:i');
            }
            $userSignaturesModes = UserModel::getById(['id' => $value['user_id'], 'select' => ['signature_modes']]);

            $formattedDocument['workflow'][] = [
                'userId'                => $value['user_id'],
                'userDisplay'           => UserModel::getLabelledUserById(['id' => $value['user_id']]),
                'mode'                  => $value['mode'],
                'processDate'           => $value['process_date'],
                'current'               => !$currentFound && empty($value['status']),
                'signatureMode'         => $value['signature_mode'],
                'signaturePositions'    => json_decode($value['signature_positions'], true),
                'datePositions'         => json_decode($value['date_positions'], true),
                'userSignatureModes'    => json_decode($userSignaturesModes['signature_modes'], true),
                'note'                  => $value['note']
            ];
            if (!$currentId && empty($value['status'])) {
                $currentFound = true;
                $currentId = $value['user_id'];
            }
        }

        if (!empty($currentId) && $currentId != $GLOBALS['id']) {
            $substitute = UserModel::getById(['id' => $currentId, 'select' => ['substitute']]);
            $formattedDocument['readOnly'] = $GLOBALS['id'] != $substitute['substitute'] && PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents']);
        }

        $formattedDocument['attachments'] = [];
        $attachments = AttachmentModel::getByDocumentId(['select' => ['id', 'title'], 'documentId' => $args['id']]);
        foreach ($attachments as $attachment) {
            $pagesCount = 0;
            $adr = AdrModel::getAttachmentsAdr([
                'select'  => ['count(1)'],
                'where'   => ['attachment_id = ?', 'type != ?'],
                'data'    => [$attachment['id'], 'ATTACH']
            ]);
            if (!empty($adr[0]['count'])) {
                $pagesCount = $adr[0]['count'];
            }

            $formattedDocument['attachments'][] = [
                'id'    => $attachment['id'],
                'title' => $attachment['title'],
                'pages' => $pagesCount
            ];
        }

        if (!empty($document['link_id'])) {
            $formattedDocument['linkedDocuments'] = DocumentController::getLinkedDocuments(['id' => $args['id'], 'userId' => $GLOBALS['id'], 'linkId' => $document['link_id']]);
        }

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'main_documents',
            'objectId'      => $args['id'],
            'type'          => 'VIEW',
            'message'       => "{documentViewed} : {$document['title']}"
        ]);

        return $response->withJson(['document' => $formattedDocument]);
    }

    public function getContent(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'userId' => $GLOBALS['id']]) && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $queryParams = $request->getQueryParams();
        $content = DocumentController::getContentPath(['id' => $args['id'], 'eSignDocument' => $queryParams['eSignDocument']]);
        if (!empty($content['errors'])) {
            return $response->withStatus($content['code'])->withJson(['errors' => $content['errors']]);
        } elseif (empty($content['path'])) {
            return $response->withStatus(404)->withJson(['encodedDocument' => null]);
        }
        $pathToDocument = $content['path'];

        $document = DocumentModel::getById(['select' => ['title'], 'id' => $args['id']]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'main_documents',
            'objectId'      => $args['id'],
            'type'          => 'VIEW',
            'message'       => "{documentViewed} : {$document['title']}"
        ]);

        $fileContent = file_get_contents($pathToDocument);
        $finfo       = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType    = $finfo->buffer($fileContent);
        
        $data = $request->getQueryParams();
        if (empty($data['mode']) || $data['mode'] == 'base64') {
            return $response->withJson(['encodedDocument' => base64_encode($fileContent)]);
        } else {
            $pathInfo = pathinfo($pathToDocument);

            $response->write($fileContent);
            $response = $response->withAddedHeader('Content-Disposition', "inline; filename=maarch.{$pathInfo['extension']}");
            return $response->withHeader('Content-Type', $mimeType);
        }
    }

    public function create(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'indexation'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $body = $request->getParsedBody();

        if (empty($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is not set or empty']);
        } elseif (!Validator::notEmpty()->validate($body['encodedDocument'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body encodedDocument is empty']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['title'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body title is empty or not a string']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['sender'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body sender is empty or not a string']);
        } elseif (!Validator::arrayType()->notEmpty()->validate($body['workflow'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body workflow is empty or not an array']);
        }

        $body['attachments'] = empty($body['attachments']) ? [] : $body['attachments'];
        foreach ($body['attachments'] as $key => $attachment) {
            if (!Validator::notEmpty()->validate($attachment['encodedDocument'])) {
                return $response->withStatus(400)->withJson(['errors' => "Body attachments[{$key}] encodedDocument is empty"]);
            } elseif (!Validator::stringType()->notEmpty()->validate($attachment['title'])) {
                return $response->withStatus(400)->withJson(['errors' => "Body attachments[{$key}] title is empty"]);
            }
        }

        $hasEidas = false;
        $hasElectronicSignature = false;
        foreach ($body['workflow'] as $key => $workflow) {
            if (!empty($workflow['processingUser'])) {
                $processingUser = UserModel::getByLogin(['select' => ['id'], 'login' => strtolower($workflow['processingUser'])]);
            } elseif (!empty($workflow['userId'])) {
                $processingUser = UserModel::getById(['select' => ['id'], 'id' => $workflow['userId']]);
            }
            if (empty($processingUser)) {
                return $response->withStatus(400)->withJson(['errors' => "Body workflow[{$key}] processingUser/userId is empty or does not exist"]);
            } elseif (!Validator::stringType()->notEmpty()->validate($workflow['mode']) || !in_array($workflow['mode'], DocumentController::MODES)) {
                return $response->withStatus(400)->withJson(['errors' => "Body workflow[{$key}] mode is empty or not a string in ('visa', 'sign', 'note')"]);
            }
            if ($hasElectronicSignature && $workflow['signatureMode'] == 'stamp' && $workflow['mode'] == 'sign') {
                return $response->withStatus(400)->withJson(['errors' => "Body workflow[{$key}] signatureMode cannot be stamp after an electronic signature", 'lang' => 'stampInTheMiddleImpossible']);
            }
            if (!empty($workflow['signaturePositions'])) {
                if (!Validator::arrayType()->validate($workflow['signaturePositions'])) {
                    return $response->withStatus(400)->withJson(['errors' => "Body workflow[{$key}] signaturePositions is not an array"]);
                }
                foreach ($workflow['signaturePositions'] as $keySP => $signaturePosition) {
                    if (empty($signaturePosition['positionX']) || empty($signaturePosition['positionY']) || empty($signaturePosition['page'])) {
                        return $response->withStatus(400)->withJson(['errors' => "Body workflow[{$key}][signaturePositions][{$keySP}] is wrong formatted"]);
                    }
                }
            }
            if (!empty($workflow['datePositions'])) {
                if (!Validator::arrayType()->validate($workflow['datePositions'])) {
                    return $response->withStatus(400)->withJson(['errors' => "Body workflow[{$key}] datePositions is not an array"]);
                }
                foreach ($workflow['datePositions'] as $keyDP => $datePosition) {
                    if (empty($datePosition['positionX']) || empty($datePosition['positionY']) || empty($datePosition['page'])) {
                        return $response->withStatus(400)->withJson(['errors' => "Body workflow[{$key}][datePositions][{$keyDP}] is wrong formatted"]);
                    } elseif (empty($datePosition['color']) || empty($datePosition['font']) || empty($datePosition['format']) || empty($datePosition['size'])) {
                        return $response->withStatus(400)->withJson(['errors' => "Body workflow[{$key}][datePositions][{$keyDP}] is wrong formatted"]);
                    }
                }
            }
            if (in_array($workflow['signatureMode'], ['eidas', 'rgs_2stars_timestamped', 'inca_card_eidas'])) {
                $hasEidas = true;
            }
            if ($workflow['signatureMode'] != 'stamp' && $workflow['mode'] == 'sign') {
                $hasElectronicSignature = true;
            }
            $body['workflow'][$key]['userId'] = $processingUser['id'];
        }

        $libDir    = CoreConfigModel::getLibrariesDirectory();
        $loadedXml = CoreConfigModel::getConfig();
        if ($loadedXml->docaposteSignature->enable == 'true' && $hasEidas && (empty($libDir) || !is_file($libDir . 'SetaPDF-Signer/library/SetaPDF/Autoload.php'))) {
            return $response->withStatus(500)->withJson(['errors' => 'SetaPDF-Signer library is not installed', 'lang' => 'setAPdfSignerError']);
        }

        $isZipped = (!isset($body['isZipped']) || $body['isZipped']) ? true : false;

        if ($isZipped) {
            $encodedDocument = DocumentController::getEncodedDocumentFromEncodedZip(['encodedZipDocument' => $body['encodedDocument']]);
        } else {
            $encodedDocument['encodedDocument'] = $body['encodedDocument'];
        }
        if (!empty($encodedDocument['errors'])) {
            return $response->withStatus(500)->withJson(['errors' => $encodedDocument['errors']]);
        }

        $data     = base64_decode($encodedDocument['encodedDocument']);
        $finfo    = finfo_open();
        $mimeType = finfo_buffer($finfo, $data, FILEINFO_MIME_TYPE);
        if (strtolower($mimeType) != 'application/pdf') {
            return $response->withStatus(400)->withJson(['errors' => 'Document is not a pdf']);
        }

        if (!empty($libDir) && is_file($libDir . 'SetaPDF-FormFiller-Full/library/SetaPDF/Autoload.php')) {
            require_once($libDir . 'SetaPDF-FormFiller-Full/library/SetaPDF/Autoload.php');

            $targetFile = CoreConfigModel::getTmpPath() . "tmp_file_{$GLOBALS['id']}_" .rand(). "_target_watermark.pdf";
            $flattenedFile = CoreConfigModel::getTmpPath() . "tmp_file_{$GLOBALS['id']}_" .rand(). "_watermark.pdf";
            file_put_contents($targetFile, base64_decode($encodedDocument['encodedDocument']));
            $writer = new \SetaPDF_Core_Writer_File($flattenedFile);
            $document = \SetaPDF_Core_Document::loadByFilename($targetFile, $writer);

            $formFiller = new \SetaPDF_FormFiller($document);
            $fields = $formFiller->getFields();
            $fields->flatten();
            $document->save()->finish();

            $file = file_get_contents($flattenedFile);
            $encodedDocument['encodedDocument'] = base64_encode($file);
            unlink($flattenedFile);
            unlink($targetFile);
        }

        try {
            $storeInfos = DocserverController::storeResourceOnDocServer([
                'encodedFile'       => $encodedDocument['encodedDocument'],
                'format'            => 'pdf',
                'docserverType'     => 'DOC'
            ]);
            if (!empty($storeInfos['errors'])) {
                return $response->withStatus(500)->withJson(['errors' => $storeInfos['errors']]);
            }

            if (!empty($body['notes']) && !empty($body['notes']['value'])) {
                $notes = [
                    'value'         => $body['notes']['value'],
                    'creator'       => $body['notes']['creator'] ?? null,
                    'creationDate'  => $body['notes']['creationDate'] ?? null
                ];
                $notes = json_encode($notes);
            }

            DatabaseModel::beginTransaction();
            $id = DocumentModel::create([
                'title'         => $body['title'],
                'reference'     => empty($body['reference']) ? null : $body['reference'],
                'description'   => empty($body['description']) ? null : $body['description'],
                'sender'        => $body['sender'],
                'deadline'      => empty($body['deadline']) ? null : $body['deadline'],
                'notes'         => $notes ?? null,
                'link_id'       => (string)$body['linkId'] ?? null,
                'metadata'      => empty($body['metadata']) ? '{}' : json_encode($body['metadata']),
                'status'        => 'CREATED',
                'typist'        => $GLOBALS['id']
            ]);

            AdrModel::createDocumentAdr([
                'documentId'     => $id,
                'type'           => 'DOC',
                'path'           => $storeInfos['path'],
                'filename'       => $storeInfos['filename'],
                'fingerprint'    => $storeInfos['fingerprint']
            ]);

            foreach ($body['workflow'] as $key => $workflow) {
                if (!SignatureController::isValidSignatureMode(['mode' => $workflow['signatureMode']])) {
                    $workflow['signatureMode'] = 'stamp';
                }
                WorkflowModel::create([
                    'userId'                => $workflow['userId'],
                    'mainDocumentId'        => $id,
                    'mode'                  => $workflow['mode'],
                    'order'                 => $key + 1,
                    'signatureMode'         => $workflow['signatureMode'],
                    'signaturePositions'    => empty($workflow['signaturePositions']) ? '[]' : json_encode($workflow['signaturePositions'])
                ]);
            }

            foreach ($body['attachments'] as $key => $value) {
                $value['mainDocumentId'] = $id;
                $value['isZipped']       = $isZipped;
                $attachment = AttachmentController::create($value);
                if (!empty($attachment['errors'])) {
                    DatabaseModel::rollbackTransaction();
                    return $response->withStatus(500)->withJson(['errors' => "An error occured for attachment {$key} : {$attachment['errors']}"]);
                }
            }

            $data = empty($body['reference']) ? [] : ['reference' => $body['reference']];
            if (!empty($body['metadata'])) {
                foreach ($body['metadata'] as $key => $metadata) {
                    $data[ucwords($key)] = $metadata;
                }
            }

            HistoryController::add([
                'code'          => 'OK',
                'objectType'    => 'main_documents',
                'objectId'      => $id,
                'type'          => 'CREATION',
                'message'       => "{documentAdded} : {$body['title']}",
                'data'          => empty($data) ? [] : $data
            ]);

            DatabaseModel::commitTransaction();
        } catch (\Exception $e) {
            return $response->withStatus(500)->withJson(['errors' => $e->getMessage()]);
        }

        $workflow  = WorkflowModel::get([
            'select'  => ['id', 'user_id', 'signature_mode'],
            'where'   => ['mode = ?', 'main_document_id = ?'],
            'data'    => ['sign', $id],
            'orderBy' => ['"order" asc']
        ]);

        if ($loadedXml->docaposteSignature->enable == 'true' && $hasEidas) {
            $result = DigitalSignatureController::createTransaction(['documentId' => $id, 'workflow' => $workflow, 'encodedDocument' => $encodedDocument['encodedDocument']]);
            if (!empty($result['errors'])) {
                return $response->withStatus(500)->withJson(['errors' => $result['errors']]);
            }
        }
        EmailController::sendNotificationToNextUserInWorkflow(['documentId' => $id, 'userId' => $GLOBALS['id']]);

        $configPath = CoreConfigModel::getConfigPath();
        exec("php src/app/convert/scripts/ThumbnailScript.php '{$configPath}' {$id} 'document' '{$GLOBALS['id']}' > /dev/null &");

        return $response->withJson(['id' => $id]);
    }

    public function setAction(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        } elseif (empty(DocumentController::ACTIONS[$args['actionId']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Action does not exist']);
        }

        $workflow  = WorkflowModel::getCurrentStep(['select' => ['id', 'mode', 'user_id', 'signature_mode', 'digital_signature_id'], 'documentId' => $args['id']]);
        $libDir    = CoreConfigModel::getLibrariesDirectory();
        $loadedXml = CoreConfigModel::getConfig();
        $tmpPath   = CoreConfigModel::getTmpPath();

        if ($workflow['mode'] == 'sign' && $workflow['signature_mode'] != 'stamp') {
            if (empty($libDir) || !is_file($libDir . 'SetaPDF-Signer/library/SetaPDF/Autoload.php')) {
                return $response->withStatus(500)->withJson(['errors' => 'SetaPDF-Signer library is not installed', 'lang' => 'setAPdfSignerError']);
            }
            require_once($libDir . 'SetaPDF-Signer/library/SetaPDF/Autoload.php');
        }
        if (in_array($workflow['signature_mode'], ['eidas', 'inca_card_eidas']) && $loadedXml->docaposteSignature->enable != 'true') {
            return $response->withStatus(400)->withJson(['errors' => 'docaposteSignature is disabled', 'lang' => 'docaposteSignatureDisabled']);
        }

        $body = $request->getParsedBody();
        if (DocumentController::ACTIONS[$args['actionId']] == 'VAL' && $workflow['signature_mode'] == 'stamp') {
            $isCertificateSigned = WorkflowModel::get(['select' => [1], 'where' => ['main_document_id = ?', 'signature_mode != ?', 'status is not null'], 'data' => [$args['id'], 'stamp']]);
            if (!empty($isCertificateSigned)) {
                unset($body['signatures']);
            }
        }
        if (!empty($body['signatures']) && empty($body['signatureLength'])) {
            foreach ($body['signatures'] as $signature) {
                foreach (['encodedImage', 'width', 'positionX', 'positionY', 'page', 'type'] as $value) {
                    if (!isset($signature[$value])) {
                        return $response->withStatus(400)->withJson(['errors' => $value . ' is empty']);
                    }
                }
            }

            if (in_array($workflow['signature_mode'], ['rgs_2stars', 'rgs_2stars_timestamped', 'inca_card', 'inca_card_eidas'])) {
                if (!empty($body['step']) && $body['step'] == 'hashCertificate') {
                    $signWithServerCertificate = false;
                    if (DocumentController::ACTIONS[$args['actionId']] == 'VAL' && $workflow['mode'] == 'sign' && $loadedXml->electronicSignature->enable == 'true') {
                        $signWithServerCertificate = true;
                    }

                    if (empty($body['tmpUniqueId'])) {
                        $control = DocumentController::getDocumentPath(['id' => $args['id']]);
                        if (!empty($control['errors'])) {
                            return $response->withStatus(400)->withJson(['errors' => $control['errors']]);
                        }

                        $control = DocumentController::processSignatures(['path' => $control['path'], 'signature' => $body['signatures'][0], 'signWithServerCertificate' => $signWithServerCertificate]);
                        if (!empty($control['errors'])) {
                            return $response->withStatus(400)->withJson(['errors' => $control['errors']]);
                        }

                        $uniqueId = CoreConfigModel::getUniqueId();
                        $body['tmpUniqueId'] = $uniqueId;
                    } else {
                        $pathToDocument = "{$tmpPath}tmpSignatureDoc_{$GLOBALS['id']}_{$body['tmpUniqueId']}.pdf";
                        if (!is_file($pathToDocument)) {
                            return $response->withStatus(400)->withJson(['errors' => 'Unique temporary file does not exist']);
                        }

                        $control = DocumentController::processSignatures(['path' => $pathToDocument, 'signature' => $body['signatures'][0], 'signWithServerCertificate' => $signWithServerCertificate]);
                        if (!empty($control['errors'])) {
                            return $response->withStatus(400)->withJson(['errors' => $control['errors']]);
                        }
                    }
                    file_put_contents("{$tmpPath}tmpSignatureDoc_{$GLOBALS['id']}_{$body['tmpUniqueId']}.pdf", $control['fileContent']);
                }
            } else {
                $control = DocumentController::getDocumentPath(['id' => $args['id']]);
                if (!empty($control['errors'])) {
                    return $response->withStatus(400)->withJson(['errors' => $control['errors']]);
                }
                $pathToDocument = $control['path'];

                $tmpFilename = $tmpPath . $GLOBALS['id'] . '_' . rand() . 'adr.pdf';
                copy($pathToDocument, $tmpFilename);

                $configPath = CoreConfigModel::getConfigPath();
                $overrideFile = "{$configPath}/override/setasign/fpdi_pdf-parser/src/autoload.php";
                if (file_exists($overrideFile)) {
                    require_once($overrideFile);
                }
                $pdf            = new Fpdi('P');
                $pagesNumber    = $pdf->setSourceFile($tmpFilename);

                $control = DocumentController::setSignaturesOnPdf(['signatures' => $body['signatures'], 'pagesNumber' => $pagesNumber], $pdf);
                if (!empty($control['errors'])) {
                    return $response->withStatus(400)->withJson(['errors' => $control['errors']]);
                }
                $affectedPages = $control['affectedPages'];

                if (DocumentController::ACTIONS[$args['actionId']] == 'VAL' && $workflow['mode'] == 'sign' && $loadedXml->electronicSignature->enable == 'true') {
                    $control = CertificateSignatureController::signWithServerCertificate($pdf);
                    if (!empty($control['errors'])) {
                        return $response->withStatus(400)->withJson(['errors' => $control['errors']]);
                    }
                }

                $fileContent = $pdf->Output('', 'S');

                $storeInfos = DocserverController::storeResourceOnDocServer([
                    'encodedFile'     => base64_encode($fileContent),
                    'format'          => 'pdf',
                    'docserverType'   => 'DOC'
                ]);
                if (!empty($storeInfos['errors'])) {
                    return $response->withStatus(500)->withJson(['errors' => $storeInfos['errors']]);
                }

                unlink($pathToDocument);
                AdrModel::deleteDocumentAdr([
                    'where' => ['main_document_id = ?', 'type = ?'],
                    'data'  => [$args['id'], 'DOC']
                ]);
                AdrModel::createDocumentAdr([
                    'documentId'    => $args['id'],
                    'type'          => 'DOC',
                    'path'          => $storeInfos['path'],
                    'filename'      => $storeInfos['filename'],
                    'fingerprint'   => $storeInfos['fingerprint']
                ]);

                if (DocumentController::ACTIONS[$args['actionId']] == 'VAL' && $workflow['signature_mode'] == 'stamp') {
                    $storeInfos = DocserverController::storeResourceOnDocServer([
                        'encodedFile'     => base64_encode($fileContent),
                        'format'          => 'pdf',
                        'docserverType'   => 'ESIGN'
                    ]);
                    if (!empty($storeInfos['errors'])) {
                        return $response->withStatus(500)->withJson(['errors' => $storeInfos['errors']]);
                    }
    
                    AdrModel::deleteDocumentAdr([
                        'where' => ['main_document_id = ?', 'type = ?'],
                        'data'  => [$args['id'], 'ESIGN']
                    ]);
                    AdrModel::createDocumentAdr([
                        'documentId'    => $args['id'],
                        'type'          => 'ESIGN',
                        'path'          => $storeInfos['path'],
                        'filename'      => $storeInfos['filename'],
                        'fingerprint'   => $storeInfos['fingerprint']
                    ]);
                }

                $tnlPages = [];
                foreach ($affectedPages as $page) {
                    $tnlPages[] = "TNL{$page}";
                }
                if (!empty($tnlPages)) {
                    AdrModel::deleteDocumentAdr([
                        'where' => ['main_document_id = ?', 'type in (?)'],
                        'data'  => [$args['id'], $tnlPages]
                    ]);
                }

                $configPath = CoreConfigModel::getConfigPath();
                foreach ($affectedPages as $page) {
                    exec("php src/app/convert/scripts/ThumbnailScript.php '{$configPath}' {$args['id']} 'document' '{$GLOBALS['id']}' {$page} > /dev/null &");
                }
            }
        }
        if (!empty($body['step']) && $body['step'] == 'hashCertificate' && in_array($workflow['signature_mode'], ['rgs_2stars', 'rgs_2stars_timestamped', 'inca_card', 'inca_card_eidas'])) {
            if (empty($body['certificate'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Body certificate is empty']);
            }
            $hashInformations = CertificateSignatureController::getHashedCertificate(['id' => $args['id'], 'body' => $body]);
            if (!empty($hashInformations['errors'])) {
                return $response->withStatus(400)->withJson($hashInformations);
            }
            return $response->withJson($hashInformations);
        }

        if (in_array($workflow['signature_mode'], ['rgs_2stars', 'rgs_2stars_timestamped', 'inca_card', 'inca_card_eidas'])) {
            $lastStep = false;
            if (empty($body['signatures']) || count($body['signatures']) == 1) {
                $lastStep = true;
            }
            $return = CertificateSignatureController::signDocument([
                'id'                        => $args['id'],
                'certificate'               => $body['certificate'],
                'signatureContentLength'    => $body['signatureContentLength'],
                'signatureFieldName'        => $body['signatureFieldName'],
                'hashSignature'             => $body['hashSignature'],
                'signatureMode'             => $workflow['signature_mode'],
                'tmpUniqueId'               => $body['tmpUniqueId'] ?? null,
                'lastStep'                  => $lastStep
            ]);
            if (!empty($return['errors'])) {
                return $response->withStatus(400)->withJson($return);
            }
            if (!$lastStep) {
                return $response->withStatus(206)->withJson(['tmpUniqueId' => $body['tmpUniqueId']]);
            } elseif ($lastStep && !empty($body['signatures'])) {
                $storeInfos = DocserverController::storeResourceOnDocServer([
                    'encodedFile'     => base64_encode(file_get_contents("{$tmpPath}tmpSignatureDoc_{$GLOBALS['id']}_{$body['tmpUniqueId']}.pdf")),
                    'format'          => 'pdf',
                    'docserverType'   => 'DOC'
                ]);
                if (!empty($storeInfos['errors'])) {
                    return $response->withStatus(500)->withJson(['errors' => $storeInfos['errors']]);
                }

                unlink("{$tmpPath}tmpSignatureDoc_{$GLOBALS['id']}_{$body['tmpUniqueId']}.pdf");
                AdrModel::deleteDocumentAdr([
                    'where' => ['main_document_id = ?', 'type = ?'],
                    'data'  => [$args['id'], 'DOC']
                ]);
                AdrModel::createDocumentAdr([
                    'documentId'    => $args['id'],
                    'type'          => 'DOC',
                    'path'          => $storeInfos['path'],
                    'filename'      => $storeInfos['filename'],
                    'fingerprint'   => $storeInfos['fingerprint']
                ]);

                AdrModel::deleteDocumentAdr([
                    'where' => ['main_document_id = ?', 'type like ?'],
                    'data'  => [$args['id'], 'TNL%']
                ]);
            }
        }

        if (in_array($workflow['signature_mode'], ['eidas', 'inca_card_eidas'])) {
            if (DocumentController::ACTIONS[$args['actionId']] == 'VAL' && $workflow['mode'] == 'sign') {
                $imageSignature = false;
                $adr = AdrModel::getDocumentsAdr([
                    'select'  => ['path', 'filename'],
                    'where'   => ['main_document_id = ?', 'type = ?'],
                    'data'    => [$args['id'], 'ESIGN']
                ]);
                $docserver          = DocserverModel::getByType(['type' => 'ESIGN', 'select' => ['path']]);
                $pathToDocument     = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
                $tmpPath            = CoreConfigModel::getTmpPath();
                $signedDocumentPath = $tmpPath . $GLOBALS['id'] . '_' . rand() . '_signedDocument.pdf';
                $writer             = new \SetaPDF_Core_Writer_File($signedDocumentPath);
                $document           = \SetaPDF_Core_Document::loadByFilename($pathToDocument, $writer);

                $pages = $document->getCatalog()->getPages();
                $pageCount = $pages->count();

                for ($pageNumber = 1; $pageNumber <= $pageCount; $pageNumber++) {
                    $page = $pages->getPage($pageNumber);

                    $format = \SetaPDF_Core_PageFormats::getFormat($page->getWidthAndHeight(), \SetaPDF_Core_PageFormats::ORIENTATION_AUTO);
                    foreach ($body['signatures'] as $key => $signature) {
                        if ($signature['page'] == $pageNumber) {
                            $image = base64_decode($signature['encodedImage']);
                            if ($image === false) {
                                return $response->withStatus(400)->withJson(['errors' => 'base64_decode failed']);
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
                                $return = DigitalSignatureController::signHashes([
                                    'signatureId'   => $workflow['digital_signature_id'],
                                    'documentId'    => $args['id'],
                                    'signatureInfo' => [
                                        'page'          => $signature['page'],
                                        'positionX'     => $signPosX,
                                        'positionY'     => $signPosY,
                                        'filePath'      => $imageTmpPath,
                                        'signWidth'     => $signWidth
                                    ],
                                    'isLastSignature' => !isset($body['signatures'][$key + 1])
                                ]);
                                if (!empty($return['errors'])) {
                                    return $response->withStatus(400)->withJson($return);
                                }
                                $imageSignature = true;
                            }
                        }
                    }
                }
                if (!$imageSignature) {
                    $return = DigitalSignatureController::signHashes(['signatureId' => $workflow['digital_signature_id'], 'documentId' => $args['id'], 'isLastSignature' => true]);
                    if (!empty($return['errors'])) {
                        return $response->withStatus(400)->withJson($return);
                    }
                }
            } elseif (DocumentController::ACTIONS[$args['actionId']] == 'REF' && $workflow['mode'] == 'sign') {
                $return = DigitalSignatureController::abort(['signatureId' => $workflow['digital_signature_id'], 'documentId' => $args['id']]);
                if (!empty($return['errors'])) {
                    return $response->withStatus(400)->withJson($return);
                }
            }
        }

        $set = ['process_date' => 'CURRENT_TIMESTAMP', 'status' => DocumentController::ACTIONS[$args['actionId']]];
        if (!empty($body['note'])) {
            $set['note'] = $body['note'];
        }
        WorkflowModel::update([
            'set'   => $set,
            'where' => ['id = ?'],
            'data'  => [$workflow['id']]
        ]);

        if (DocumentController::ACTIONS[$args['actionId']] == 'REF') {
            WorkflowModel::update([
                'set'   => ['status' => 'END'],
                'where' => ['main_document_id = ?', 'process_date is null'],
                'data'  => [$args['id']]
            ]);
        }

        EmailController::sendNotificationToNextUserInWorkflow(['documentId' => $args['id'], 'userId' => $GLOBALS['id']]);

        $historyMessagePart = "{actionDone} : ";
        if ($workflow['user_id'] != $GLOBALS['id']) {
            $user = UserModel::getLabelledUserById(['id' => $workflow['user_id']]);
            $historyMessagePart = "{actionDoneInPlaceOf} {$user} : ";
        }

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'main_documents',
            'objectId'      => $args['id'],
            'type'          => 'ACTION',
            'message'       => $historyMessagePart . DocumentController::ACTIONS[$args['actionId']],
            'data'          => ['actionId' => $args['actionId'], 'mode' => $workflow['mode']]
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function getThumbnailContent(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'userId' => $GLOBALS['id']]) && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $adr = AdrModel::getDocumentsAdr([
            'select'  => ['path', 'filename'],
            'where'   => ['main_document_id = ?', 'type = ?'],
            'data'    => [$args['id'], 'TNL' . $args['page']]
        ]);

        $docserver = DocserverModel::getByType(['type' => 'DOC', 'select' => ['path']]);
        if (empty($docserver['path']) || !file_exists($docserver['path'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        $pathToThumbnail = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
        if (!is_file($pathToThumbnail) || !is_readable($pathToThumbnail)) {
            $configPath = CoreConfigModel::getConfigPath();
            exec("php src/app/convert/scripts/ThumbnailScript.php '{$configPath}' {$args['id']} 'document' '{$GLOBALS['id']}' {$args['page']} > /dev/null");
            $adr = AdrModel::getDocumentsAdr([
                'select'  => ['path', 'filename'],
                'where'   => ['main_document_id = ?', 'type = ?'],
                'data'    => [$args['id'], 'TNL' . $args['page']]
            ]);
            $pathToThumbnail = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
            if (!is_file($pathToThumbnail) || !is_readable($pathToThumbnail)) {
                return $response->withStatus(400)->withJson(['errors' => 'Thumbnail not found on docserver or not readable', 'lang' => 'thumbnailNotFound']);
            }
        }

        $fileContent = file_get_contents($pathToThumbnail);
        if ($fileContent === false) {
            return $response->withStatus(404)->withJson(['errors' => 'Thumbnail not found on docserver']);
        }

        $base64Content = base64_encode($fileContent);

        return $response->withJson(['fileContent' => $base64Content]);
    }

    public static function hasRightById(array $args)
    {
        ValidatorModel::notEmpty($args, ['id', 'userId']);
        ValidatorModel::intVal($args, ['id', 'userId']);

        $document = DocumentModel::getById(['select' => ['typist'], 'id' => $args['id']]);
        if ($document['typist'] == $GLOBALS['id']) {
            return true;
        }

        $workflow = WorkflowModel::getCurrentStep(['select' => ['user_id'], 'documentId' => $args['id']]);
        if (empty($workflow)) {
            return false;
        }

        if ($workflow['user_id'] != $args['userId']) {
            $user = UserModel::getById(['id' => $workflow['user_id'], 'select' => ['substitute']]);
            if ($user['substitute'] != $args['userId']) {
                return false;
            }
        }

        return true;
    }

    public static function getEncodedDocumentFromEncodedZip(array $args)
    {
        ValidatorModel::notEmpty($args, ['encodedZipDocument']);
        ValidatorModel::stringType($args, ['encodedZipDocument']);

        $tmpPath = CoreConfigModel::getTmpPath();

        $zipDocumentOnTmp = $tmpPath . mt_rand() . '_parapheur.zip';
        file_put_contents($zipDocumentOnTmp, base64_decode($args['encodedZipDocument']));

        $zipArchive = new \ZipArchive();
        $open = $zipArchive->open($zipDocumentOnTmp);
        if ($open != true) {
            return ['errors' => "getDocumentFromEncodedZip : $open"];
        }

        $dirOnTmp = $tmpPath . mt_rand() . '_parapheur';
        if (!@$zipArchive->extractTo($dirOnTmp)) {
            return ['errors' => "getDocumentFromEncodedZip : Extract failed"];
        }

        $filesOnTmp = scandir($dirOnTmp);
        foreach ($filesOnTmp as $fileOnTmp) {
            if ($fileOnTmp != '.' && $fileOnTmp != '..') {
                $base64Content = base64_encode(file_get_contents("{$dirOnTmp}/{$fileOnTmp}"));
                unlink($zipDocumentOnTmp);
                return ['encodedDocument' => $base64Content];
            }
        }

        return ['errors' => "getDocumentFromEncodedZip : No document was found in Zip"];
    }

    public static function getLinkedDocuments(array $args)
    {
        ValidatorModel::notEmpty($args, ['id', 'userId', 'linkId']);
        ValidatorModel::intVal($args, ['id', 'userId']);
        ValidatorModel::stringType($args, ['linkId']);

        $substitutedUsers = UserModel::get(['select' => ['id'], 'where' => ['substitute = ?'], 'data' => [$args['userId']]]);

        $users = [$args['userId']];
        foreach ($substitutedUsers as $value) {
            $users[] = $value['id'];
        }

        $workflowSelect = "SELECT id FROM workflows ws WHERE workflows.main_document_id = main_document_id AND process_date IS NULL AND status IS NULL ORDER BY \"order\" LIMIT 1";
        $workflows = WorkflowModel::get([
            'select'    => ['main_document_id', 'mode', 'user_id'],
            'where'     => ['user_id in (?)', "(id) in ({$workflowSelect})", 'main_document_id != ?'],
            'data'      => [$users, $args['id']]
        ]);
        $documentIds = array_column($workflows, 'main_document_id');

        $linkedDocuments = [];
        if (!empty($documentIds)) {
            $linkedDocuments = DocumentModel::get([
                'select'    => ['id', 'title', 'reference'],
                'where'     => ['id in (?)', 'link_id = ?'],
                'data'      => [$documentIds, $args['linkId']],
                'orderBy'   => ['creation_date desc']
            ]);
        }

        return $linkedDocuments;
    }

    public static function getContentPath($args = [])
    {
        if ($args['eSignDocument']) {
            $adr = AdrModel::getDocumentsAdr([
                'select'    => ['path', 'filename', 'fingerprint', 'type'],
                'where'     => ['main_document_id = ?', 'type = ?'],
                'data'      => [$args['id'], 'ESIGN']
            ]);
        }
        if (empty($adr)) {
            $adr = AdrModel::getDocumentsAdr([
                'select'    => ['path', 'filename', 'fingerprint', 'type'],
                'where'     => ['main_document_id = ?', 'type = ?'],
                'data'      => [$args['id'], 'DOC']
            ]);
        }

        if (empty($adr[0])) {
            return null;
        }

        $docserver = DocserverModel::getByType(['type' => $adr[0]['type'], 'select' => ['path']]);
        if (empty($docserver['path']) || !file_exists($docserver['path'])) {
            return ['errors' => 'Docserver does not exist', 'code' => 400];
        }

        $pathToDocument = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
        if (!is_file($pathToDocument)) {
            return ['errors' => 'Document not found on docserver', 'code' => 404];
        }

        $fingerprint = DocserverController::getFingerPrint(['path' => $pathToDocument]);
        if ($adr[0]['fingerprint'] != $fingerprint) {
            return ['errors' => 'Fingerprints do not match', 'code' => 400];
        }

        return ['path' => $pathToDocument];
    }

    public static function getPdfCertificate(array $args)
    {
        ValidatorModel::notEmpty($args, ['path', 'documentId']);
        ValidatorModel::intVal($args, ['documentId']);
        ValidatorModel::stringType($args, ['path']);

        $path = $args['path'];
        $documentId = $args['documentId'];

        $tmpPath = CoreConfigModel::getTmpPath();

        $signaturePath = $tmpPath . 'signature_' . $documentId . '.pkcs7';
        $signatureInfoPath = $tmpPath . 'signatureInfo_' . $documentId . '.txt';

        if (file_exists($signatureInfoPath)) {
            $content = file_get_contents($signatureInfoPath);
            if ($content !== false) {
                return $content;
            }
        }

        $content = file_get_contents($path);
        $regexp = '#ByteRange\[\s*(\d+) (\d+) (\d+)#'; // subexpressions are used to extract b and c

        $result = [];
        preg_match_all($regexp, $content, $result);

        // $result[2][0] and $result[3][0] are b and c
        if (isset($result[2]) && isset($result[3]) && isset($result[2][0]) && isset($result[3][0])) {
            $start = $result[2][0];
            $end = $result[3][0];
            if ($stream = fopen($path, 'rb')) {
                $signature = stream_get_contents($stream, $end - $start - 2, $start + 1); // because we need to exclude < and > from start and end
                fclose($stream);
                file_put_contents($signaturePath, hex2bin($signature));
            }
        }

        if (!file_exists($signaturePath)) {
            return false;
        }
        exec('openssl pkcs7 -in ' . $signaturePath . ' -inform DER -print_certs > ' . $signatureInfoPath . ' 2>&1', $output, $return);

        return file_get_contents($signatureInfoPath);
    }

    public static function setSignaturesOnPdf(array $args, Fpdi &$pdf)
    {
        ValidatorModel::notEmpty($args, ['signatures', 'pagesNumber']);
        ValidatorModel::arrayType($args, ['signatures']);
        ValidatorModel::intVal($args, ['pagesNumber']);

        $affectedPages = [];
        $tmpPath = CoreConfigModel::getTmpPath();

        $pdf->setPrintHeader(false);

        for ($i = 1; $i <= $args['pagesNumber']; $i++) {
            $page = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($page);
            $pdf->AddPage($size['orientation'], $size);
            $pdf->useImportedPage($page);
            $pdf->SetAutoPageBreak(false, 0);
            $pdf->SetMargins(0, 0, 0);
            $pdf->SetAutoPageBreak(false, 0);
            foreach ($args['signatures'] as $signature) {
                if ($signature['page'] == $i) {
                    if (!in_array($i, $affectedPages)) {
                        $affectedPages[] = $i;
                    }
                    if ($signature['positionX'] == 0 && $signature['positionY'] == 0) {
                        $signWidth = $size['width'];
                        $signPosX = 0;
                        $signPosY = 0;
                    } else {
                        $signWidth = ($signature['width'] * $size['width']) / 100;
                        $signPosX = ($signature['positionX'] * $size['width']) / 100;
                        $signPosY = ($signature['positionY'] * $size['height']) / 100;
                    }
                    if ($signature['type'] == 'SVG') {
                        $image = str_replace('data:image/svg+xml;base64,', '', $signature['encodedImage']);
                        $image = base64_decode($image);
                        if ($image === false) {
                            return ['errors' => 'setSignaturesOnPdf : base64_decode failed for SVP type signature'];
                        }

                        $imageTmpPath = $tmpPath . $GLOBALS['id'] . '_' . rand() . '_writing.svg';
                        file_put_contents($imageTmpPath, $image);
                        $pdf->ImageSVG($imageTmpPath, $signPosX, $signPosY, $signWidth);
                    } else {
                        $image = base64_decode($signature['encodedImage']);
                        if ($image === false) {
                            return ['errors' => 'setSignaturesOnPdf : base64_decode failed'];
                        }

                        $imageTmpPath = $tmpPath . $GLOBALS['id'] . '_' . rand() . '_writing.png';
                        file_put_contents($imageTmpPath, $image);
                        $pdf->Image($imageTmpPath, $signPosX, $signPosY, $signWidth);
                    }
                }
            }
        }

        return ['affectedPages' => $affectedPages];
    }

    public static function getDocumentPath(array $args)
    {
        ValidatorModel::notEmpty($args, ['id']);
        ValidatorModel::intVal($args, ['id']);

        $content = DocumentController::getContentPath(['id' => $args['id'], 'eSignDocument' => false]);
        if (empty($content)) {
            return ['errors' => 'Document does not exist'];
        } elseif (!empty($content['errors'])) {
            return ['errors' => $content['errors']];
        }

        return ['path' => $content['path']];
    }

    private static function processSignatures(array $args)
    {
        ValidatorModel::notEmpty($args, ['path', 'signature']);
        ValidatorModel::stringType($args, ['path']);

        $tmpPath = CoreConfigModel::getTmpPath();

        $tmpFilename = $tmpPath . $GLOBALS['id'] . '_' . rand() . 'adr.pdf';
        copy($args['path'], $tmpFilename);

        $configPath = CoreConfigModel::getConfigPath();
        $overrideFile = "{$configPath}/override/setasign/fpdi_pdf-parser/src/autoload.php";
        if (file_exists($overrideFile)) {
            require_once($overrideFile);
        }
        $pdf            = new Fpdi('P');
        $pagesNumber    = $pdf->setSourceFile($tmpFilename);

        $control = DocumentController::setSignaturesOnPdf(['signatures' => [$args['signature']], 'pagesNumber' => $pagesNumber], $pdf);
        if (!empty($control['errors'])) {
            return ['errors' => $control['errors']];
        }

        if ($args['signWithServerCertificate']) {
            $control = CertificateSignatureController::signWithServerCertificate($pdf);
            if (!empty($control['errors'])) {
                return ['errors' => $control['errors']];
            }
        }

        $fileContent = $pdf->Output('', 'S');
        unlink($tmpFilename);

        return ['fileContent' => $fileContent];
    }
}
