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

use Docserver\models\AdrModel;
use Docserver\models\DocserverModel;
use Document\controllers\DocumentController;
use Document\models\DocumentModel;
use Group\controllers\PrivilegeController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\LanguageController;
use SrcCore\models\ValidatorModel;
use History\models\HistoryModel;
use User\models\UserModel;

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

        $adr = AdrModel::getDocumentsAdr([
            'select'    => ['filename', 'fingerprint', 'path'],
            'where'     => ['main_document_id = ?', 'type = ?'],
            'data'      => [$args['id'], 'DOC']
        ]);
        $adr = $adr[0];

        $docserver = DocserverModel::getByType(['type' => 'DOC', 'select' => ['path']]);
        if (empty($docserver['path']) || !file_exists($docserver['path'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
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

        $lang = LanguageController::get();
        $langKeys = [];
        $langValues = [];
        foreach ($lang as $key => $value) {
            $langKeys[] = "/{{$key}}/";
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
                'date'     => $date->format('d-m-Y H:i'),
                'message'  => preg_replace($langKeys, $langValues, $value['message']),
                'data'     => $data
            ];

            if ($value['type'] == 'ACTION' && $data['actionId'] == 1 && $data['mode'] == 'sign') {
                $formatted['document'] = $adr;
            }

            $formattedHistory[] = $formatted;
        }

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'history',
            'objectId'      => $args['id'],
            'type'          => 'VIEW',
            'message'       => '{documentHistoryViewed}',
            'data'          => ['objectType' => 'main_documents']
        ]);

        return $response->withJson(['history' => $formattedHistory]);
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
            'objectType'    => 'history',
            'objectId'      => $args['id'],
            'type'          => 'VIEW',
            'message'       => '{userHistoryViewed}',
            'data'          => ['objectType' => 'users']
        ]);

        return $response->withJson(['history' => $formattedHistory]);
    }
}
