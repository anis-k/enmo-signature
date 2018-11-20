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
use Docserver\controllers\DocserverController;
use Docserver\models\AdrModel;
use Docserver\models\DocserverModel;
use Document\controllers\DocumentController;
use History\controllers\HistoryController;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\ValidatorModel;

class AttachmentController
{
    public function getById(Request $request, Response $response, array $args)
    {
        $attachment = AttachmentModel::getById(['select' => ['*'], 'id' => $args['id']]);
        if (empty($attachment)) {
            return $response->withStatus(400)->withJson(['errors' => 'Attachment does not exist']);
        }

        if (!DocumentController::hasRightById(['id' => $attachment['main_document_id'], 'email' => $GLOBALS['email']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $adr = AdrModel::getAttachmentsAdr([
            'select'    => ['path', 'filename', 'fingerprint'],
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

        // TODO Commenté pour tests
//        $fingerprint = DocserverController::getFingerPrint(['path' => $pathToDocument]);
//        if ($adr[0]['fingerprint'] != $fingerprint) {
//            return $response->withStatus(404)->withJson(['errors' => 'Fingerprints do not match']);
//        }

        $attachment['encodedDocument'] = base64_encode(file_get_contents($pathToDocument));

        return $response->withJson(['attachment' => $attachment]);
    }

    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['encodedZipDocument', 'subject', 'main_document_id']);
        ValidatorModel::stringType($args, ['encodedZipDocument', 'subject']);
        ValidatorModel::intVal($args, ['main_document_id']);

        $encodedDocument = DocumentController::getEncodedDocumentFromEncodedZip(['encodedZipDocument' => $args['encodedZipDocument']]);
        if (!empty($encodedDocument['errors'])) {
            return ['errors' => $encodedDocument['errors']];
        }

        $storeInfos = DocserverController::storeResourceOnDocServer([
            'encodedFile'       => $encodedDocument['encodedDocument'],
            'format'            => 'pdf',
            'docserverType'     => 'ATTACH'
        ]);
        if (!empty($storeInfos['errors'])) {
            return ['errors' => $storeInfos['errors']];
        }

        $id = AttachmentModel::create($args);

        AdrModel::createAttachmentAdr([
            'attachmentId'   => $id,
            'type'           => 'ATTACH',
            'path'           => $storeInfos['path'],
            'filename'       => $storeInfos['filename'],
            'fingerprint'    => $storeInfos['fingerprint']
        ]);

        HistoryController::add([
            'tableName' => 'attachments',
            'recordId'  => $id,
            'eventType' => 'CREATION',
            'info'      => "attachmentAdded {$args['subject']}",
        ]);

        return ['attachmentId' => $id];
    }
}
