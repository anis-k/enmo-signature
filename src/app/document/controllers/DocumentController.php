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
use User\controllers\UserController;
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

        $substitutedUsers = UserModel::get(['select' => ['id'], 'where' => ['substitute = ?'], 'data' => [$GLOBALS['id']]]);

        $users = [$GLOBALS['id']];
        foreach ($substitutedUsers as $value) {
            $users[] = $value['id'];
        }

        $workflowSelect = "SELECT id FROM workflows ws WHERE workflows.main_document_id = main_document_id AND process_date IS NULL AND status IS NULL ORDER BY \"order\" LIMIT 1";
        $where = ['user_id in (?)', "(id) in ({$workflowSelect})"];
        $data = [$users];
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

        $count = empty($documents[0]['count']) ? 0 : $documents[0]['count'];
        foreach ($documents as $key => $document) {
            unset($documents[$key]['count']);
            $documents[$key]['mode'] = $workflowsShortcut[$document['id']]['mode'];
            $documents[$key]['owner'] = $workflowsShortcut[$document['id']]['user_id'] == $GLOBALS['id'];
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
            'where'   => ['main_document_id = ?', 'type != ?'],
            'data'    => [$args['id'], 'DOC']
        ]);
        if (empty($adr[0]['count'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Document thumbnails do not exist']);
        }

        $formattedDocument = [
            'id'                => $document['id'],
            'title'             => $document['title'],
            'reference'         => $document['reference'],
            'description'       => $document['description'],
            'sender'            => $document['sender'],
            'creationDate'      => $document['creation_date'],
            'modificationDate'  => $document['modification_date'],
            'pages'             => $adr[0]['count']
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

        $workflow = WorkflowModel::getByDocumentId(['select' => ['user_id', 'mode', 'process_date'], 'documentId' => $args['id'], 'orderBy' => ['"order"']]);
        $currentFound = false;
        foreach ($workflow as $value) {
            if (!empty($value['process_date'])) {
                $date = new \DateTime($document['process_date']);
                $value['process_date'] = $date->format('d-m-Y H:i');
            }
            $formattedDocument['workflow'][] = [
                'userId'        => $value['user_id'],
                'userDisplay'   => UserModel::getLabelledUserById(['id' => $value['user_id']]),
                'mode'          => $value['mode'],
                'processDate'   => $value['process_date'],
                'current'       => !$currentFound && empty($value['process_date'])
            ];
            if (empty($value['process_date'])) {
                $currentFound = true;
            }
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

        $adr = AdrModel::getDocumentsAdr([
            'select'    => ['path', 'filename', 'fingerprint'],
            'where'     => ['main_document_id = ?', 'type = ?'],
            'data'      => [$args['id'], 'DOC']
        ]);
        if (empty($adr[0])) {
            return $response->withJson(['encodedDocument' => null]);
        }

        $docserver = DocserverModel::getByType(['type' => 'DOC', 'select' => ['path']]);
        if (empty($docserver['path']) || !file_exists($docserver['path'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        $pathToDocument = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
        if (!is_file($pathToDocument)) {
            return $response->withStatus(404)->withJson(['errors' => 'Document not found on docserver']);
        }

        $fingerprint = DocserverController::getFingerPrint(['path' => $pathToDocument]);
        if ($adr[0]['fingerprint'] != $fingerprint) {
            return $response->withStatus(400)->withJson(['errors' => 'Fingerprints do not match']);
        }

        $document = DocumentModel::getById(['select' => ['title'], 'id' => $args['id']]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'main_documents',
            'objectId'      => $args['id'],
            'type'          => 'VIEW',
            'message'       => "{documentViewed} : {$document['title']}"
        ]);

        return $response->withJson(['encodedDocument' => base64_encode(file_get_contents($pathToDocument))]);
    }

    public function create(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
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

        foreach ($body['workflow'] as $key => $workflow) {
            if (!empty($workflow['processingUser'])) {
                $processingUser = UserModel::getByLogin(['select' => ['id'], 'login' => $workflow['processingUser']]);
            } elseif (!empty($workflow['userId'])) {
                $processingUser = UserModel::getById(['select' => ['id'], 'id' => $workflow['userId']]);
            }
            if (empty($processingUser)) {
                return $response->withStatus(400)->withJson(['errors' => "Body workflow[{$key}] processingUser/userId is empty or does not exist"]);
            } elseif (!Validator::stringType()->notEmpty()->validate($workflow['mode']) || !in_array($workflow['mode'], DocumentController::MODES)) {
                return $response->withStatus(400)->withJson(['errors' => "Body workflow[{$key}] mode is empty or not a string in ('visa', 'sign', 'note')"]);
            }
            $body['workflow'][$key]['userId'] = $processingUser['id'];
        }

        $encodedDocument = DocumentController::getEncodedDocumentFromEncodedZip(['encodedZipDocument' => $body['encodedDocument']]);
        if (!empty($encodedDocument['errors'])) {
            return $response->withStatus(500)->withJson(['errors' => $encodedDocument['errors']]);
        }

        $storeInfos = DocserverController::storeResourceOnDocServer([
            'encodedFile'       => $encodedDocument['encodedDocument'],
            'format'            => 'pdf',
            'docserverType'     => 'DOC'
        ]);
        if (!empty($storeInfos['errors'])) {
            return $response->withStatus(500)->withJson(['errors' => $storeInfos['errors']]);
        }

        DatabaseModel::beginTransaction();
        $id = DocumentModel::create([
            'title'             => $body['title'],
            'reference'         => empty($body['reference']) ? null : $body['reference'],
            'description'       => empty($body['description']) ? null : $body['description'],
            'sender'            => $body['sender'],
            'deadline'          => empty($body['deadline']) ? null : $body['deadline'],
            'metadata'          => empty($body['metadata']) ? '{}' : json_encode($body['metadata'])
        ]);

        AdrModel::createDocumentAdr([
            'documentId'     => $id,
            'type'           => 'DOC',
            'path'           => $storeInfos['path'],
            'filename'       => $storeInfos['filename'],
            'fingerprint'    => $storeInfos['fingerprint']
        ]);

        foreach ($body['workflow'] as $key => $workflow) {
            WorkflowModel::create([
                'userId'            => $workflow['userId'],
                'mainDocumentId'    => $id,
                'mode'              => $workflow['mode'],
                'order'             => $key + 1
            ]);
        }

        foreach ($body['attachments'] as $key => $value) {
            $value['mainDocumentId'] = $id;
            $attachment = AttachmentController::create($value);
            if (!empty($attachment['errors'])) {
                DatabaseModel::rollbackTransaction();
                return $response->withStatus(500)->withJson(['errors' => "An error occured for attachment {$key} : {$attachment['errors']}"]);
            }
        }

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'main_documents',
            'objectId'      => $id,
            'type'          => 'CREATION',
            'message'       => "{documentAdded} : {$body['title']}"
        ]);

        DatabaseModel::commitTransaction();

        EmailController::sendNotificationToNextUserInWorkflow(['documentId' => $id, 'userId' => $GLOBALS['id']]);

        $configPath = CoreConfigModel::getConfigPath();
        exec("php src/app/convert/scripts/ThumbnailScript.php '{$configPath}' {$id} 'document' '{$GLOBALS['id']}' > /dev/null &");

        return $response->withJson(['id' => $id]);
    }

    public function setAction(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $currentUser = UserModel::getById(['id' => $GLOBALS['id'], 'select' => ['substitute']]);
        if (!empty($currentUser['substitute'])) {
            return $response->withStatus(403)->withJson(['errors' => 'User can not make action with substituted account']);
        }

        if (empty(DocumentController::ACTIONS[$args['actionId']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Action does not exist']);
        }

        $workflow = WorkflowModel::getCurrentStep(['select' => ['id', 'mode'], 'documentId' => $args['id']]);

        $body = $request->getParsedBody();
        if (!empty($body['signatures'])) {
            foreach ($body['signatures'] as $signature) {
                foreach (['encodedImage', 'width', 'positionX', 'positionY', 'page', 'type'] as $value) {
                    if (!isset($signature[$value])) {
                        return $response->withStatus(400)->withJson(['errors' => $value . ' is empty']);
                    }
                }
            }

            $adr = AdrModel::getDocumentsAdr([
                'select'  => ['path', 'filename'],
                'where'   => ['main_document_id = ?', 'type = ?'],
                'data'    => [$args['id'], 'DOC']
            ]);
            if (empty($adr)) {
                return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
            }

            $docserver = DocserverModel::getByType(['type' => 'DOC', 'select' => ['path']]);
            if (empty($docserver['path']) || !file_exists($docserver['path'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
            }

            $pathToDocument = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
            if (!is_file($pathToDocument) || !is_readable($pathToDocument)) {
                return $response->withStatus(404)->withJson(['errors' => 'Document not found on docserver or not readable']);
            }

            $tmpPath     = CoreConfigModel::getTmpPath();
            $tmpFilename = $tmpPath . $GLOBALS['id'] . '_' . rand() . '_' . $adr[0]['filename'];
            copy($pathToDocument, $tmpFilename);

            $pdf     = new Fpdi('P');
            $nbPages = $pdf->setSourceFile($tmpFilename);
            $pdf->setPrintHeader(false);

            for ($i = 1; $i <= $nbPages; $i++) {
                $page = $pdf->importPage($i);
                $size = $pdf->getTemplateSize($page);
                $pdf->AddPage($size['orientation'], $size);
                $pdf->useImportedPage($page);
                $pdf->SetAutoPageBreak(false, 0);
                $pdf->SetMargins(0, 0, 0);
                $pdf->SetAutoPageBreak(false, 0);
                foreach ($body['signatures'] as $signature) {
                    if ($signature['page'] == $i) {
                        if ($signature['positionX'] == 0 && $signature['positionY'] == 0) {
                            $signWidth = $size['width'];
                            $signPosX = 0;
                            $signPosY = 0;
                        } else {
                            $signWidth = $size['width'] / 4;
                            $signPosX = ($signature['positionX'] * $size['width']) / 100;
                            $signPosY = ($signature['positionY'] * $size['height']) / 100;
                        }
                        if ($signature['type'] == 'SVG') {
                            $image = str_replace('data:image/svg+xml;base64,', '', $signature['encodedImage']);
                            $image = base64_decode($image);
                            if ($image === false) {
                                return $response->withStatus(400)->withJson(['errors' => 'base64_decode failed']);
                            }

                            $imageTmpPath = $tmpPath . $GLOBALS['id'] . '_' . rand() . '_writing.svg';
                            file_put_contents($imageTmpPath, $image);
                            $pdf->ImageSVG($imageTmpPath, $signPosX, $signPosY, $signWidth);
                        } else {
                            $image = base64_decode($signature['encodedImage']);
                            if ($image === false) {
                                return $response->withStatus(400)->withJson(['errors' => 'base64_decode failed']);
                            }

                            $imageTmpPath = $tmpPath . $GLOBALS['id'] . '_' . rand() . '_writing.png';
                            file_put_contents($imageTmpPath, $image);
                            $pdf->Image($imageTmpPath, $signPosX, $signPosY, $signWidth);
                        }
                    }
                }
            }

            if (DocumentController::ACTIONS[$args['actionId']] == 'VAL' && $workflow['mode'] == 'sign') {
                $loadedXml = CoreConfigModel::getConfig();
                if ($loadedXml->electronicSignature->enable == 'true') {
                    $certPath       = realpath((string)$loadedXml->electronicSignature->certPath);
                    $privateKeyPath = realpath((string)$loadedXml->electronicSignature->privateKeyPath);
                    if (is_file($certPath) && is_file($privateKeyPath)) {
                        $certificate = 'file://' . $certPath;
                        $privateKey = 'file://' . $privateKeyPath;
                        $info = [
                            'Name'        => (string)$loadedXml->electronicSignature->certInfo->name,
                            'Location'    => (string)$loadedXml->electronicSignature->certInfo->location,
                            'Reason'      => (string)$loadedXml->electronicSignature->certInfo->reason,
                            'ContactInfo' => (string)$loadedXml->electronicSignature->certInfo->contactInfo
                        ];
                        $pdf->setSignature($certificate, $privateKey, (string)$loadedXml->electronicSignature->password, '', 2, $info);
                    } else {
                        return $response->withStatus(400)->withJson(['errors' => 'certPath or privateKeyPath is not valid']);
                    }
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

        AdrModel::deleteDocumentAdr([
            'where' => ['main_document_id = ?', 'type != ?'],
            'data'  => [$args['id'], 'DOC']
        ]);

        $configPath = CoreConfigModel::getConfigPath();
        exec("php src/app/convert/scripts/ThumbnailScript.php '{$configPath}' {$args['id']} 'document' '{$GLOBALS['id']}' > /dev/null &");

        EmailController::sendNotificationToNextUserInWorkflow(['documentId' => $args['id'], 'userId' => $GLOBALS['id']]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'main_documents',
            'objectId'      => $args['id'],
            'type'          => 'ACTION',
            'message'       => "{actionDone} : " . DocumentController::ACTIONS[$args['actionId']],
            'data'          => ['actionId' => $args['actionId']]
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function getThumbnailContent(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'userId' => $GLOBALS['id']])) {
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
            return $response->withStatus(400)->withJson(['errors' => 'Document not found on docserver or not readable']);
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
}
