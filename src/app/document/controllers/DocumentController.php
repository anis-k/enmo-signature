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

use Convert\models\AdrModel;
use Docserver\models\DocserverModel;
use Document\models\DocumentModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\ValidatorModel;
use Status\models\StatusModel;
use User\models\UserModel;

class DocumentController
{
    public function get(Request $request, Response $response)
    {
        $data = $request->getQueryParams();

        if (empty($data['offset']) || !is_numeric($data['offset'])) {
            $data['offset'] = 0;
        }
        if (empty($data['limit']) || !is_numeric($data['limit'])) {
            $data['limit'] = 0;
        }

        $user = UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);

        $documents = DocumentModel::getByUserId(['select' => ['id', 'reference', 'subject', 'status'], 'userId' => $user['id']]);
        foreach ($documents as $key => $document) {
            $status = StatusModel::getById(['select' => ['label'], 'id' => $document['status']]);
            $documents[$key]['statusDisplay'] = $status['label'];
        }

        return $response->withJson(['documents' => $documents]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'login' => $GLOBALS['login']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $document = DocumentModel::getById(['select' => ['*'], 'id' => $args['id']]);
        if (empty($document)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
        }

        $status = StatusModel::getById(['select' => ['label'], 'id' => $document['status']]);
        $document['statusDisplay'] = $status['label'];
        $document['processingUserDisplay'] = UserModel::getLabelledUserById(['id' => $document['processing_user']]);


        $documentAdr = AdrModel::getDocumentsAdr([
            'select'    => ['path', 'filename'],
            'where'     => ['main_document_id = ?', 'type = ?'],
            'data'      => [$args['id'], 'DOC']
        ]);

        $docserver = DocserverModel::getByType(['type' => 'DOC', 'select' => ['path']]);
        if (empty($docserver['path']) || !file_exists($docserver['path'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        $pathToDocument = $docserver['path'] . $documentAdr[0]['path'] . $documentAdr[0]['filename'];
        if (!file_exists($pathToDocument)) {
            return $response->withStatus(404)->withJson(['errors' => 'Document not found on docserver']);
        }

        $document['document'] = base64_encode(file_get_contents($pathToDocument));

        return $response->withJson(['document' => $document]);
    }

    public static function hasRightById(array $args)
    {
        ValidatorModel::notEmpty($args, ['id', 'login']);
        ValidatorModel::intVal($args, ['id']);
        ValidatorModel::stringType($args, ['login']);

        $user = UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);

        $document = DocumentModel::get(['select' => [1], 'where' => ['processing_user = ?', 'id = ?'], 'data' => [$user['id'], $args['id']]]);
        if (empty($document)) {
            return false;
        }

        return true;
    }
}
