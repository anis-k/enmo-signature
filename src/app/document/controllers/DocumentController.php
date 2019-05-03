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
use Respect\Validation\Validator;
use setasign\Fpdi\Tcpdf\Fpdi;
use SrcCore\controllers\LanguageController;
use SrcCore\controllers\UrlController;
use SrcCore\models\CoreConfigModel;
use Attachment\models\AttachmentModel;
use Docserver\controllers\DocserverController;
use Docserver\models\DocserverModel;
use Document\models\DocumentModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use Status\models\StatusModel;
use User\controllers\UserController;
use User\models\UserModel;
use History\controllers\HistoryController;
use Action\models\ActionModel;

class DocumentController
{
    public function get(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();

        $queryParams['offset'] = empty($queryParams['offset']) ? 0 : (int)$queryParams['offset'];
        $queryParams['limit'] = empty($queryParams['limit']) ? 0 : (int)$queryParams['limit'];

        $status = StatusModel::getByReference(['select' => ['id'], 'reference' => 'NEW']);

        $where = ['processing_user = ?', 'status = ?'];
        $dataGet = [$GLOBALS['id'], $status['id']];
        $count = [];
        if (!empty($queryParams['mode'])) {
            $where[] = 'mode = ?';
            $dataGet[] = $queryParams['mode'];
            $secondMode = ($queryParams['mode'] == 'SIGN' ? 'NOTE' : 'SIGN');
            $documents = DocumentModel::get([
                'select'    => ['count(1) OVER()'],
                'where'     => $where,
                'data'      => [$GLOBALS['id'], $status['id'], $secondMode]
            ]);
            $count[$secondMode] = empty($documents[0]['count']) ? 0 : $documents[0]['count'];
        }

        $documents = DocumentModel::get([
            'select'    => ['id', 'title', 'reference', 'status', 'mode', 'count(1) OVER()'],
            'where'     => $where,
            'data'      => $dataGet,
            'limit'     => $queryParams['limit'],
            'offset'    => $queryParams['offset'],
            'orderBy'   => ['creation_date desc']
        ]);
        $count[$queryParams['mode']] = empty($documents[0]['count']) ? 0 : $documents[0]['count'];
        foreach ($documents as $key => $document) {
            $status = StatusModel::getById(['select' => ['label'], 'id' => $document['status']]);
            $documents[$key]['statusDisplay'] = $status['label'];
            unset($documents[$key]['count']);
        }

        return $response->withJson(['documents' => $documents, 'count' => $count]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'userId' => $GLOBALS['id']]) && !UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $document = DocumentModel::getById(['select' => ['*'], 'id' => $args['id']]);
        if (empty($document)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
        }

        $adr = AdrModel::getDocumentsAdr([
            'select'    => ['path', 'filename', 'fingerprint'],
            'where'     => ['main_document_id = ?', 'type = ?'],
            'data'      => [$args['id'], 'DOC']
        ]);

        $docserver = DocserverModel::getByType(['type' => 'DOC', 'select' => ['path']]);
        if (empty($docserver['path']) || !file_exists($docserver['path'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        $pathToDocument = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
        if (!file_exists($pathToDocument)) {
            return $response->withStatus(404)->withJson(['errors' => 'Document not found on docserver']);
        }

        $fingerprint = DocserverController::getFingerPrint(['path' => $pathToDocument]);
        if ($adr[0]['fingerprint'] != $fingerprint) {
            return $response->withStatus(404)->withJson(['errors' => 'Fingerprints do not match']);
        }

        $formattedDocument = [
            'id'                => $document['id'],
            'title'             => $document['title'],
            'reference'         => $document['reference'],
            'description'       => $document['description'],
            'mode'              => $document['mode'],
            'sender'            => $document['sender'],
            'creationDate'      => $document['creation_date'],
            'modificationDate'  => $document['modification_date']
        ];
        if (!empty($document['deadline'])) {
            $date = new \DateTime($document['deadline']);
            $formattedDocument['deadline'] = $date->format('d-m-Y H:i');
        }
        $formattedDocument['status'] = StatusModel::getById(['select' => ['*'], 'id' => $document['status']]);
        $processingUser = UserModel::getById(['select' => ['firstname', 'lastname', 'login'], 'id' => $document['processing_user']]);
        $formattedDocument['processingUser'] = $processingUser['login'];
        $formattedDocument['processingUserDisplay'] = "{$processingUser['firstname']} {$processingUser['lastname']}";
        $creator = UserModel::getById(['select' => ['firstname', 'lastname', 'login'], 'id' => $document['creator']]);
        $formattedDocument['creator'] = $creator['login'];
        $formattedDocument['creatorDisplay'] = "{$creator['firstname']} {$creator['lastname']}";
        $formattedDocument['encodedDocument'] = base64_encode(file_get_contents($pathToDocument));

        $formattedDocument['metadata'] = [];
        $metadata = json_decode($document['metadata'], true);
        if (is_array($metadata)) {
            foreach ($metadata as $key => $value) {
                $formattedDocument['metadata'][] = ['label' => $key, 'value' => $value];
            }
        }

        $formattedDocument['actionsAllowed'] = [];
        $actions = ActionModel::get(['select' => ['id'], 'where' => ['mode = ?', 'status_id = ?'], 'data' => [$document['mode'], $document['status']], 'orderBy' => ['id']]);
        foreach ($actions as $action) {
            $formattedDocument['actionsAllowed'][] = $action['id'];
        }

        $formattedDocument['attachments'] = [];
        $attachments = AttachmentModel::getByDocumentId(['select' => ['id'], 'documentId' => $args['id']]);
        foreach ($attachments as $attachment) {
            $formattedDocument['attachments'][] = $attachment['id'];
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

    public function create(Request $request, Response $response)
    {
        if (!UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $body = $request->getParsedBody();

        if (empty($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is not set or empty']);
        } elseif (!Validator::notEmpty()->validate($body['encodedDocument'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body encodedDocument is empty']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['title'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body title is empty or not a string']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['mode'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body mode is empty or not a string']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['processingUser'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body processingUser is empty or not a string']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['sender'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body sender is empty or not a string']);
        }

        $body['attachments'] = empty($body['attachments']) ? [] : $body['attachments'];
        foreach ($body['attachments'] as $key => $attachment) {
            if (!Validator::notEmpty()->validate($attachment['encodedDocument'])) {
                return $response->withStatus(400)->withJson(['errors' => "Body attachments[{$key}] encodedDocument is empty"]);
            } elseif (!Validator::stringType()->notEmpty()->validate($attachment['title'])) {
                return $response->withStatus(400)->withJson(['errors' => "Body attachments[{$key}] title is empty"]);
            }
        }

        $processingUser = UserModel::getByLogin(['select' => ['id', 'email', 'preferences'], 'login' => $body['processingUser']]);
        if (empty($processingUser)) {
            return $response->withStatus(400)->withJson(['errors' => 'Processing user does not exist']);
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

        $status = StatusModel::get(['select' => ['id'], 'where' => ['reference = ?'], 'data' => ['NEW']]);

        DatabaseModel::beginTransaction();
        $id = DocumentModel::create([
            'title'             => $body['title'],
            'reference'         => empty($body['reference']) ? null : $body['reference'],
            'description'       => empty($body['description']) ? null : $body['description'],
            'mode'              => $body['mode'] == 'SIGN' ? 'SIGN' : 'NOTE',
            'status'            => $status[0]['id'],
            'processing_user'   => $processingUser['id'],
            'sender'            => $body['sender'],
            'deadline'          => empty($body['deadline']) ? null : $body['deadline'],
            'metadata'          => empty($body['metadata']) ? '{}' : json_encode($body['metadata']),
            'creator'           => $GLOBALS['id']
        ]);

        AdrModel::createDocumentAdr([
            'documentId'     => $id,
            'type'           => 'DOC',
            'path'           => $storeInfos['path'],
            'filename'       => $storeInfos['filename'],
            'fingerprint'    => $storeInfos['fingerprint']
        ]);

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

        $processingUser['preferences'] = json_decode($processingUser['preferences'], true);
        if ($processingUser['preferences']['notifications']) {
            $lang = LanguageController::get(['lang' => $processingUser['preferences']['lang']]);
            $url = UrlController::getCoreUrl() . 'dist/index.html#/documents/' . $id;
            EmailController::createEmail([
                'userId'    => $GLOBALS['id'],
                'data'      => [
                    'sender'        => 'Notification',
                    'recipients'    => [$processingUser['email']],
                    'subject'       => $lang['notificationDocumentAddedSubject'],
                    'body'          => $lang['notificationDocumentAddedBody'] . $url . $lang['notificationFooter'],
                    'isHtml'        => true
                ]
            ]);
        }

        return $response->withJson(['id' => $id]);
    }

    public function setAction(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $document = DocumentModel::getById(['select' => ['mode', 'status'], 'id' => $args['id']]);
        $action = ActionModel::get([
            'select'    => ['next_status_id', 'label'],
            'where'     => ['mode = ?', 'status_id = ?', 'id = ?'],
            'data'      => [$document['mode'], $document['status'], $args['actionId']]
        ]);

        if (empty($action[0])) {
            return $response->withStatus(400)->withJson(['errors' => 'Action does not exist']);
        }
        $action = $action[0];

        $data = $request->getParams();
        if (!empty($data['signatures'])) {
            foreach ($data['signatures'] as $signature) {
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
            if (!file_exists($pathToDocument)) {
                return $response->withStatus(404)->withJson(['errors' => 'Document not found on docserver']);
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
                foreach ($data['signatures'] as $signature) {
                    if ($signature['page'] == $i) {
                        if ($signature['positionX'] == 0 && $signature['positionY'] == 0) {
                            $signWidth = $size['width']*2;
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

            $status = StatusModel::getById(['select' => ['reference'], 'id' => $action['next_status_id']]);
            if ($status['reference'] == 'VAL' && $document['mode'] == 'SIGN') {
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
                'docserverType'   => 'HANDWRITTEN'
            ]);

            AdrModel::createDocumentAdr([
                'documentId'     => $args['id'],
                'type'           => 'HANDWRITTEN',
                'path'           => $storeInfos['path'],
                'filename'       => $storeInfos['filename'],
                'fingerprint'    => $storeInfos['fingerprint']
            ]);
        }

        DocumentModel::update([
            'set'   => ['status' => $action['next_status_id']],
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'main_documents',
            'objectId'      => $args['id'],
            'type'          => 'ACTION',
            'message'       => "{actionDone} : {$action['label']}",
            'data'          => ['actionId' => $args['actionId']]
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function getProcessedDocumentById(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'userId' => $GLOBALS['id']]) && !UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $adr = AdrModel::getDocumentsAdr([
            'select'    => ['path', 'filename', 'fingerprint'],
            'where'     => ['main_document_id = ?', 'type = ?'],
            'data'      => [$args['id'], 'HANDWRITTEN']
        ]);
        if (empty($adr[0])) {
            return $response->withJson(['encodedDocument' => null]);
        }

        $docserver = DocserverModel::getByType(['type' => 'HANDWRITTEN', 'select' => ['path']]);
        if (empty($docserver['path']) || !file_exists($docserver['path'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        $pathToDocument = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
        if (!file_exists($pathToDocument)) {
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
            'message'       => "{processedDocumentViewed} : {$document['title']}"
        ]);

        return $response->withJson(['encodedDocument' => base64_encode(file_get_contents($pathToDocument))]);
    }

    public function getStatusById(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'userId' => $GLOBALS['id']]) && !UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $document = DocumentModel::getById(['select' => ['status', 'mode'], 'id' => $args['id']]);
        if (empty($document)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
        }

        $status = StatusModel::getById(['select' => ['id', 'reference', 'label'], 'id' => $document['status']]);
        $status['mode'] = $document['mode'];

        return $response->withJson(['status' => $status]);
    }

    public static function hasRightById(array $args)
    {
        ValidatorModel::notEmpty($args, ['id', 'userId']);
        ValidatorModel::intVal($args, ['id', 'userId']);

        $document = DocumentModel::get(['select' => [1], 'where' => ['processing_user = ?', 'id = ?'], 'data' => [$args['userId'], $args['id']]]);
        if (empty($document)) {
            return false;
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
