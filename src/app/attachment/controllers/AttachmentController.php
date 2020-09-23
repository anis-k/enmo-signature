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
use Group\controllers\PrivilegeController;
use History\controllers\HistoryController;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;
use User\controllers\UserController;

class AttachmentController
{
    public function getById(Request $request, Response $response, array $args)
    {
        $attachment = AttachmentModel::getById(['select' => ['*'], 'id' => $args['id']]);
        if (empty($attachment)) {
            return $response->withStatus(400)->withJson(['errors' => 'Attachment does not exist']);
        }

        if (!DocumentController::hasRightById(['id' => $attachment['main_document_id'], 'userId' => $GLOBALS['id']]) && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
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

        $fingerprint = DocserverController::getFingerPrint(['path' => $pathToDocument]);
        if ($adr[0]['fingerprint'] != $fingerprint) {
            return $response->withStatus(404)->withJson(['errors' => 'Fingerprints do not match']);
        }

        $attachment['encodedDocument'] = base64_encode(file_get_contents($pathToDocument));

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'attachments',
            'objectId'      => $args['id'],
            'type'          => 'VIEW',
            'message'       => "{attachmentViewed} : {$attachment['title']}",
            'data'          => ['mainDocumentId' => $attachment['main_document_id']]
        ]);

        return $response->withJson(['attachment' => $attachment]);
    }

    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['encodedDocument', 'title', 'mainDocumentId']);
        ValidatorModel::stringType($args, ['encodedDocument', 'title', 'reference']);
        ValidatorModel::intVal($args, ['mainDocumentId']);

        $encodedDocument = DocumentController::getEncodedDocumentFromEncodedZip(['encodedZipDocument' => $args['encodedDocument']]);
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

        $id = AttachmentModel::create([
            'main_document_id'  => $args['mainDocumentId'],
            'title'             => $args['title'],
            'reference'         => empty($args['reference']) ? null : $args['reference'],
        ]);

        AdrModel::createAttachmentAdr([
            'attachmentId'   => $id,
            'type'           => 'ATTACH',
            'path'           => $storeInfos['path'],
            'filename'       => $storeInfos['filename'],
            'fingerprint'    => $storeInfos['fingerprint']
        ]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'attachments',
            'objectId'      => $id,
            'type'          => 'CREATION',
            'message'       => "{attachmentAdded} : {$args['title']}",
            'data'          => ['mainDocumentId' => $args['mainDocumentId']]
        ]);

        $configPath = CoreConfigModel::getConfigPath();
        exec("php src/app/convert/scripts/ThumbnailScript.php '{$configPath}' {$id} 'attachment' '{$GLOBALS['id']}' > /dev/null &");

        return ['id' => $id];
    }

    public function getThumbnailContent(Request $request, Response $response, array $args)
    {
        $attachment = AttachmentModel::getById(['select' => ['main_document_id'], 'id' => $args['id']]);
        if (empty($attachment)) {
            return $response->withStatus(400)->withJson(['errors' => 'Attachment does not exist']);
        }

        if (!DocumentController::hasRightById(['id' => $attachment['main_document_id'], 'userId' => $GLOBALS['id']]) && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $adr = AdrModel::getAttachmentsAdr([
            'select'  => ['path', 'filename'],
            'where'   => ['attachment_id = ?', 'type = ?'],
            'data'    => [$args['id'], 'TNL' . $args['page']]
        ]);

        $docserver = DocserverModel::getByType(['type' => 'ATTACH', 'select' => ['path']]);
        if (empty($docserver['path']) || !file_exists($docserver['path'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        $pathToThumbnail = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
        if (!is_file($pathToThumbnail) || !is_readable($pathToThumbnail)) {
            $configPath = CoreConfigModel::getConfigPath();
            exec("php src/app/convert/scripts/ThumbnailScript.php '{$configPath}' {$args['id']} 'attachment' '{$GLOBALS['id']}' > /dev/null");
            $adr = AdrModel::getAttachmentsAdr([
                'select'  => ['path', 'filename'],
                'where'   => ['attachment_id = ?', 'type = ?'],
                'data'    => [$args['id'], 'TNL' . $args['page']]
            ]);
            $pathToThumbnail = $docserver['path'] . $adr[0]['path'] . $adr[0]['filename'];
            if (!is_file($pathToThumbnail) || !is_readable($pathToThumbnail)) {
                return $response->withStatus(400)->withJson(['errors' => 'Attachment not found on docserver or not readable']);
            }
        }

        $fileContent = file_get_contents($pathToThumbnail);
        if ($fileContent === false) {
            return $response->withStatus(404)->withJson(['errors' => 'Thumbnail not found on docserver']);
        }

        $base64Content = base64_encode($fileContent);

        return $response->withJson(['fileContent' => $base64Content]);
    }
}
