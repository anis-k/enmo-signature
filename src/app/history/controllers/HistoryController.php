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

use Document\controllers\DocumentController;
use Document\models\DocumentModel;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\LangController;
use SrcCore\models\ValidatorModel;
use History\models\HistoryModel;
use User\controllers\UserController;
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
            'message'       => $args['message'],
            'data'          => empty($args['data']) ? '{}' : json_encode($args['data']),
            'ip'            => empty($_SERVER['REMOTE_ADDR']) ? 'script' : $_SERVER['REMOTE_ADDR']
        ]);

        return true;
    }

    public function getByDocumentId(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'userId' => $GLOBALS['id']]) && !UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $document = DocumentModel::getById(['select' => [1], 'id' => $args['id']]);
        if (empty($document)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
        }

        $history = HistoryModel::get([
            'select'    => ['code', 'type', 'user_id', 'date', 'message', 'data'],
            'where'     => ["(object_type = ? AND object_id = ?) OR (data->>'mainDocumentId' = ?)"],
            'data'      => ['main_documents', $args['id'], $args['id']],
            'orderBy'   => ['date']
        ]);

        $formattedHistory = [];

        $lang = LangController::get();
        $langKeys = [];
        $langValues = [];
        foreach ($lang as $key => $value) {
            $langKeys[] = "/{{$key}}/";
            $langValues[] = $value;
        }

        foreach ($history as $value) {
            $user = UserModel::getById(['select' => ['login'], 'id' => $value['user_id']]);
            $date = new \DateTime($value['date']);

            $formattedHistory[] = [
                'code'          => $value['code'],
                'type'          => $value['type'],
                'userLogin'     => $user['login'],
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
            'message'       => '{documentHistoryViewed}',
            'data'          => ['objectType' => 'main_documents']
        ]);

        return $response->withJson(['history' => $formattedHistory]);
    }
}
