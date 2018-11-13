<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief User Controller
* @author dev@maarch.org
*/

namespace User\controllers;

use Docserver\controllers\DocserverController;
use Docserver\models\DocserverModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use User\models\UserModel;

class UserController
{
    public function get(Request $request, Response $response)
    {
        $users = UserModel::get([
            'select'    => ['id', 'firstname', 'lastname'],
            'where'     => ['mode = ?'],
            'data'      => ['standard']
        ]);

        return $response->withJson(['users' => $users]);
    }

    public function getSignatures(Request $request, Response $response, array $args)
    {
        $user = UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);
        if ($user['id'] != $args['id']) {
            return $response->withStatus(403)->withJson(['errors' => 'User out of perimeter']);
        }

        $data = $request->getQueryParams();
        if (empty($data['offset']) || !is_numeric($data['offset'])) {
            $data['offset'] = 0;
        }
        if (empty($data['limit']) || !is_numeric($data['limit'])) {
            $data['limit'] = 0;
        }

        $rawSignatures = UserModel::getSignatures([
            'select'    => ['id', 'path', 'filename', 'fingerprint'],
            'where'     => ['user_id = ?'],
            'data'      => [$args['id']],
            'offset'    => (int)$data['offset'],
            'limit'     => (int)$data['limit']
        ]);
        $docserver = DocserverModel::getByType(['type' => 'SIGNATURE', 'select' => ['path']]);
        if (empty($docserver['path']) || !file_exists($docserver['path'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Docserver does not exist']);
        }

        $signatures = [];
        foreach ($rawSignatures as $signature) {
            $pathToSignature = $docserver['path'] . $signature['path'] . $signature['filename'];
            if (file_exists($pathToSignature)) {
                $fingerprint = DocserverController::getFingerPrint(['path' => $pathToSignature]);
                if ($signature['fingerprint'] == $fingerprint) {
                    $signatures[] = [
                        'id'                => $signature['id'],
                        'encodedSignature'  => base64_encode(file_get_contents($pathToSignature))
                    ];
                } else {
                    //TODO LOG
                }
            } else {
                //TODO LOG
            }
        }

        return $response->withJson(['signatures' => $signatures]);
    }

    public function createSignature(Request $request, Response $response, array $args)
    {
        $user = UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);
        if ($user['id'] != $args['id']) {
            return $response->withStatus(403)->withJson(['errors' => 'User out of perimeter']);
        }

        $data = $request->getParams();

        $check = Validator::notEmpty()->validate($data['encodedSignature']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['format']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $storeInfos = DocserverController::storeResourceOnDocServer([
            'encodedFile'       => $data['encodedSignature'],
            'format'            => $data['format'],
            'docserverType'     => 'SIGNATURE'
        ]);

        if (!empty($storeInfos['errors'])) {
            return $response->withStatus(500)->withJson(['errors' => $storeInfos['errors']]);
        }

        $id = UserModel::createSignature([
            'userId'        => $args['id'],
            'path'          => $storeInfos['path'],
            'filename'      => $storeInfos['filename'],
            'fingerprint'   => $storeInfos['fingerprint'],
        ]);

        return $response->withJson(['signatureId' => $id]);
    }

    public function deleteSignature(Request $request, Response $response, array $args)
    {
        $user = UserModel::getByLogin(['login' => $GLOBALS['login'], 'select' => ['id']]);
        if ($user['id'] != $args['id']) {
            return $response->withStatus(403)->withJson(['errors' => 'User out of perimeter']);
        }

        UserModel::deleteSignature(['where' => ['user_id = ?', 'id = ?'], 'data' => [$args['id'], $args['signatureId']]]);

        return $response->withJson(['success' => 'success']);
    }
}
