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
use Status\models\StatusModel;
use User\models\UserModel;
use History\controllers\HistoryController;
use Action\models\ActionModel;

class DocumentController
{
    public function get(Request $request, Response $response)
    {
        $data = $request->getQueryParams();

        $data['offset'] = empty($data['offset']) ? 0 : (int)$data['offset'];
        $data['limit'] = empty($data['limit']) ? 0 : (int)$data['limit'];

        $user = UserModel::getByEmail(['email' => $GLOBALS['email'], 'select' => ['id']]);

        $where = ['processing_user = ?'];
        $dataGet = [$user['id']];
        if (!empty($data['mode'])) {
            $where[] = 'mode = ?';
            $dataGet[] = $data['mode'];
        }
        $documents = DocumentModel::get([
            'select'    => ['id', 'reference', 'subject', 'status', 'count(1) OVER()'],
            'where'     => $where,
            'data'      => $dataGet,
            'limit'     => $data['limit'],
            'offset'    => $data['offset'],
            'orderBy'   => ['creation_date desc']
        ]);
        $fullCount = empty($documents[0]['count']) ? 0 : $documents[0]['count'];
        foreach ($documents as $key => $document) {
            $status = StatusModel::getById(['select' => ['label'], 'id' => $document['status']]);
            $documents[$key]['statusDisplay'] = $status['label'];
            unset($documents[$key]['count']);
        }

        return $response->withJson(['documents' => $documents, 'fullCount' => $fullCount]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'email' => $GLOBALS['email']])) {
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

        //TODO Commenté pour tests
//        $fingerprint = DocserverController::getFingerPrint(['path' => $pathToDocument]);
//        if ($adr[0]['fingerprint'] != $fingerprint) {
//            return $response->withStatus(404)->withJson(['errors' => 'Fingerprints do not match']);
//        }

        $document['encodedDocument'] = base64_encode(file_get_contents($pathToDocument));
        $document['statusDisplay'] = StatusModel::getById(['select' => ['label'], 'id' => $document['status']])['label'];
        $document['actionsAllowed'] = ActionModel::get(['select' => ['id', 'label', 'color', 'logo', 'event'], 'where' => ['mode = ?'], 'data' => [$document['mode']]]);
        $document['processingUserDisplay'] = UserModel::getLabelledUserById(['id' => $document['processing_user']]);
        $document['attachments'] = AttachmentModel::getByDocumentId(['select' => ['id'], 'documentId' => $args['id']]);

        HistoryController::add([
            'tableName' => 'main_documents',
            'recordId'  => $args['id'],
            'eventType' => 'VIEW',
            'info'      => "documentViewed {$document['subject']}"
        ]);

        return $response->withJson(['document' => $document]);
    }

    public function create(Request $request, Response $response)
    {
        $data = $request->getParams();

        $check = DocumentController::controlData([
            ['type' => 'string', 'value' => $data['encodedZipDocument']],
            ['type' => 'string', 'value' => $data['subject']],
            ['type' => 'string', 'value' => $data['mode']],
            ['type' => 'int', 'value' => $data['processing_user']],
            ['type' => 'string', 'value' => $data['sender']],
        ]);
        if (!empty($check['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $check['errors']]);
        }

        $data['attachments'] = empty($data['attachments']) ? [] : $data['attachments'];
        foreach ($data['attachments'] as $key => $attachment) {
            $check = Validator::stringType()->notEmpty()->validate($attachment['encodedZipDocument']);
            $check = $check && Validator::stringType()->notEmpty()->validate($attachment['subject']);
            if (!$check) {
                return $response->withStatus(400)->withJson(['errors' => "Missing data for attachment {$key}"]);
            }
        }

        $encodedDocument = DocumentController::getEncodedDocumentFromEncodedZip(['encodedZipDocument' => $data['encodedZipDocument']]);
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
        $data['status'] = $status[0]['id'];
        if ($data['mode'] != 'SIGN') {
            $data['mode'] = 'NOTE';
        }

        DatabaseModel::beginTransaction();
        $id = DocumentModel::create($data);

        AdrModel::createDocumentAdr([
            'documentId'     => $id,
            'type'           => 'DOC',
            'path'           => $storeInfos['path'],
            'filename'       => $storeInfos['filename'],
            'fingerprint'    => $storeInfos['fingerprint']
        ]);

        foreach ($data['attachments'] as $key => $value) {
            $value['main_document_id'] = $id;
            $attachment = AttachmentController::create($value);
            if (!empty($attachment['errors'])) {
                DatabaseModel::rollbackTransaction();
                return $response->withStatus(500)->withJson(['errors' => "An error occured for attachment {$key} : {$attachment['errors']}"]);
            }
        }
        HistoryController::add([
            'tableName' => 'main_documents',
            'recordId'  => $id,
            'eventType' => 'CREATION',
            'info'      => "documentAdded {$data['subject']}"
        ]);

        DatabaseModel::commitTransaction();

        return $response->withJson(['documentId' => $id]);
    }

    public function makeAction(Request $request, Response $response, array $args)
    {
        $data = $request->getParams();

        ValidatorModel::notEmpty($data, ['action_id']);
        ValidatorModel::intVal($data, ['action_id']);

        /*if (!empty($data['signatures'])) {
            foreach ($data['signatures'] as $signature) {
                foreach (['fullPath', 'width', 'positionX', 'positionY', 'page'] as $value) {
                    if (empty($signature[$value])) {
                        return $response->withStatus(400)->withJson(['errors' => $value . ' is empty']);
                    }
                }
            }
        }*/

        if (!DocumentController::hasRightById(['id' => $args['id'], 'email' => $GLOBALS['email']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $action = ActionModel::getById(['select' => ['next_status_id', 'label'], 'id' => $data['action_id']]);
        if (empty($action)) {
            return $response->withStatus(403)->withJson(['errors' => 'Action does not exist']);
        }

        $adr = AdrModel::getDocumentsAdr([
            'select'  => ['path', 'filename'],
            'where'   => ['main_document_id = ?', 'type = ?'],
            'data'    => [$args['id'], 'DOC']
        ]);
        if (empty($adr)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document does not exist in database']);
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
        $tmpFilename = $tmpPath . $GLOBALS['email'] . '_' . rand() . '_' . $adr[0]['filename'];
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
            if (!empty($data['signatures'])) {
                foreach ($data['signatures'] as $signature) {
                    if ($signature['page'] == $i) {
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
                            $data = str_replace('data:image/svg+xml;base64,', '', $signature['fullPath']);

                            $image = base64_decode($data);
            
                            if ($image === false) {
                                return $response->withStatus(400)->withJson(['errors' => 'base64_decode failed']);
                            }

                            $imageTmpPath = $tmpPath . $GLOBALS['email'] . '_' . rand() . '_writing.svg';
                            file_put_contents($imageTmpPath, $image);

                            $pdf->ImageSVG($imageTmpPath, $signPosX, $signPosY, $signWidth);
                        } else {
                            $data = $signature['fullPath'];

                            $image = base64_decode($data);
            
                            if ($image === false) {
                                return $response->withStatus(400)->withJson(['errors' => 'base64_decode failed']);
                            }

                            $imageTmpPath = $tmpPath . $GLOBALS['email'] . '_' . rand() . '_writing.png';
                            file_put_contents($imageTmpPath, $image);

                            $pdf->Image($imageTmpPath, $signPosX, $signPosY, $signWidth);
                        }
                    }
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

        DocumentModel::update([
            'set'   => ['status' => $action['next_status_id']],
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'tableName' => 'main_documents',
            'recordId'  => $args['id'],
            'eventType' => 'UP',
            'info'      => 'actionDone' . ' : ' . $action['label']
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function getHandwrittenDocumentById(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'email' => $GLOBALS['email']])) {
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

        return $response->withJson(['encodedDocument' => base64_encode(file_get_contents($pathToDocument))]);
    }

    public function getStatusById(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'email' => $GLOBALS['email']])) {
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
        ValidatorModel::notEmpty($args, ['id', 'email']);
        ValidatorModel::intVal($args, ['id']);
        ValidatorModel::stringType($args, ['email']);

        $user = UserModel::getByEmail(['email' => $GLOBALS['email'], 'select' => ['id']]);

        $document = DocumentModel::get(['select' => [1], 'where' => ['processing_user = ?', 'id = ?'], 'data' => [$user['id'], $args['id']]]);
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
        if (!$zipArchive->extractTo($dirOnTmp)) {
            return ['errors' => "getDocumentFromEncodedZip : Extract failed"];
        }

        $filesOnTmp = scandir($dirOnTmp);
        foreach ($filesOnTmp as $fileOnTmp) {
            if ($fileOnTmp != '.' && $fileOnTmp != '..') {
                return ['encodedDocument' => base64_encode(file_get_contents("{$dirOnTmp}/{$fileOnTmp}"))];
            }
        }

        return ['errors' => "getDocumentFromEncodedZip : No document was found in Zip"];
    }

    private static function controlData(array $args)
    {
        foreach ($args as $value) {
            if ($value['type'] == 'string' && !Validator::stringType()->notEmpty()->validate($value['value'])) {
                return ['errors' => "Data {$value['value']} is empty or not a {$value['type']}"];
            } elseif ($value['type'] == 'int' && !Validator::intVal()->notEmpty()->validate($value['value'])) {
                return ['errors' => "Data {$value['value']} is empty or not a {$value['type']}"];
            }
        }

        return ['success' => 'success'];
    }
}
