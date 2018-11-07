<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Attachment Controller
* @author dev@maarch.org
*/

namespace Attachment\controllers;

use Attachment\models\AttachmentModel;
use Convert\models\AdrModel;
use Docserver\models\DocserverModel;
use Document\controllers\DocumentController;
use Slim\Http\Request;
use Slim\Http\Response;

class AttachmentController
{
    public function getById(Request $request, Response $response, array $args)
    {
        $attachment = AttachmentModel::getById(['select' => ['*'], 'id' => $args['id']]);
        if (empty($document)) {
            return $response->withStatus(400)->withJson(['errors' => 'Attachment does not exist']);
        }

        if (!DocumentController::hasRightById(['id' => $attachment['main_document_id'], 'login' => $GLOBALS['login']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $adr = AdrModel::getAttachmentsAdr([
            'select'    => ['path', 'filename'],
            'where'     => ['attachment_id = ?', 'type = ?'],
            'data'      => [$args['id'], 'ATTACH']
        ]);

        $docserver = DocserverModel::getByType(['type' => 'ATTACH', 'select' => ['path']]);
        if (empty($docserver['path']) || !file_exists($docserver['path'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        $pathToDocument = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
        if (!file_exists($pathToDocument)) {
            return $response->withStatus(404)->withJson(['errors' => 'Attachment not found on docserver']);
        }

        $attachment['document'] = base64_encode(file_get_contents($pathToDocument));

        return $response->withJson(['attachment' => $attachment]);
    }
}
