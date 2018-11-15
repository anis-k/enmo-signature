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

use SrcCore\models\CoreConfigModel;
use Attachment\models\AttachmentModel;
use Convert\models\AdrModel;
use Docserver\controllers\DocserverController;
use Docserver\models\DocserverModel;
use Document\models\DocumentModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\ValidatorModel;
use Status\models\StatusModel;
use User\models\UserModel;
use History\controllers\HistoryController;
use setasign\Fpdi\TcpdfFpdi;
use Action\models\ActionModel;

class DocumentController
{
    public function get(Request $request, Response $response)
    {
        $data = $request->getQueryParams();
        $data['limit'] = (int)$data['limit'];
        $data['offset'] = (int)$data['offset'];

        if (empty($data['offset'])) {
            $data['offset'] = 0;
        }
        if (empty($data['limit'])) {
            $data['limit'] = 0;
        }

        $user = UserModel::getByEmail(['email' => $GLOBALS['email'], 'select' => ['id']]);

        $fullCount = 0;
        $documents = DocumentModel::getByUserId(['select' => ['id', 'reference', 'subject', 'status', 'count(1) OVER()'], 'userId' => $user['id'], 'limit' => $data['limit'], 'offset' => $data['offset']]);
        foreach ($documents as $key => $document) {
            $status = StatusModel::getById(['select' => ['label'], 'id' => $document['status']]);
            $documents[$key]['statusDisplay'] = $status['label'];
            $fullCount = $document['count'];
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

        $status = StatusModel::getById(['select' => ['label'], 'id' => $document['status']]);
        $document['statusDisplay'] = $status['label'];

        $actions = ActionModel::get(['select' => ['id', 'label', 'color', 'logo', 'event'], 'where' => ['previous_status_id = ?'], 'data' => [$document['status']]]);
        $document['actionsAllowed'] = $actions;

        $document['processingUserDisplay'] = UserModel::getLabelledUserById(['id' => $document['processing_user']]);

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
        $document['attachments'] = AttachmentModel::getByDocumentId(['select' => ['id'], 'documentId' => $args['id']]);

        return $response->withJson(['document' => $document]);
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

        $pdf     = new TcpdfFpdi('P');
        $nbPages = $pdf->setSourceFile($tmpFilename);
        $pdf->setPrintHeader(false);


        for ($i = 1; $i <= $nbPages; $i++) {
            $page = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($page);
            $pdf->AddPage($size['orientation'], $size);
            $pdf->useImportedPage($page);
            $pdf->SetAutoPageBreak(false, 0);
            $pdf->SetMargins(0, 0, 0);
            if (!empty($data['signatures'])) {
                foreach ($data['signatures'] as $signature) {
                    if ($signature['page'] == $i) {
                        if (preg_match('/^data:image\/(\w+);base64,/', $signature['fullPath'], $extension)) {
                            $data      = substr($signature['fullPath'], strpos($signature['fullPath'], ',') + 1);
                            $extension = strtolower($extension[1]);
        
                            if ($extension != 'png') {
                                return $response->withStatus(400)->withJson(['errors' => 'Invalid image type']);
                            }
                        } else {
                            $data = $signature['fullPath'];
                        }
                        $image = base64_decode($data);
        
                        if ($image === false) {
                            return $response->withStatus(400)->withJson(['errors' => 'base64_decode failed']);
                        }
                        
                        $imageTmpPath = $tmpPath . $GLOBALS['email'] . '_' . rand() . '_writing.png';
                        file_put_contents($imageTmpPath, $image);

                        // $pdf->Image($imageTmpPath, $signature['positionX'], $signature['positionY']);
                        
                        $pdf->SetY(0);
                        $html = '<img src="'.$signature['fullPath'].'"/>';
                        $pdf->writeHTML($html, true, false, true, false, '');
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
            'set' => ['status' => $action['next_status_id']],
            'where' => ['id = ?'],
            'data' => [$args['id']]
        ]);

        HistoryController::add([
            'tableName' => 'main_documents',
            'recordId'  => $args['id'],
            'eventType' => 'UP',
            'info'      => _ACTION_DONE . ' : ' . $action['label'],
            'moduleId'  => 'document',
            'eventId'   => 'documentup',
        ]);

        return $response->withJson(['success' => 'success']);
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
}
