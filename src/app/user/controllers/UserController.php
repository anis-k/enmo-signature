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
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\PasswordController;
use SrcCore\models\AuthenticationModel;
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

    public function getById(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id']) {
            return $response->withStatus(403)->withJson(['errors' => 'User out of perimeter']);
        }

        $user = UserModel::getById(['select' => ['id', 'email', 'firstname', 'lastname', 'picture'], 'id' => $args['id']]);

        if (empty($user['picture'])) {
            $user['picture'] = base64_encode(file_get_contents('src/frontend/assets/user_picture.png'));
            $user['picture'] = 'data:image/png;base64,' . $user['picture'];
        }

        return $response->withJson(['user' => $user]);
    }

    public function update(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id']) {
            return $response->withStatus(403)->withJson(['errors' => 'User out of perimeter']);
        }

        $data = $request->getParams();
        $check = Validator::stringType()->notEmpty()->validate($data['firstname']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['lastname']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        if (!empty($data['picture']) && !empty($data['pictureOrientation'])) {
            $infoContent = '';
            if (preg_match('/^data:image\/(\w+);base64,/', $data['picture'])) {
                $infoContent = substr($data['picture'], 0, strpos($data['picture'], ',') + 1);
                $data['picture'] = substr($data['picture'], strpos($data['picture'], ',') + 1);
            }
            $imagick = new \Imagick();
            $imagick->readImageBlob(base64_decode($data['picture']));
            $imagick->rotateImage(new \ImagickPixel(), $data['pictureOrientation']);
            $data['picture'] = $infoContent . base64_encode($imagick->getImageBlob());
        }
        /*if (!empty($data['picture'])) {
            $picture    = base64_decode($data['picture']);
            $finfo      = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType   = $finfo->buffer($picture);
            $type       = explode('/', $mimeType);

            if ($type[0] != 'image') {
                return $response->withStatus(400)->withJson(['errors' => 'Picture is not an image']);
            }
        }*/

        $data['id'] = $args['id'];
        UserModel::update($data);

        HistoryController::add([
            'tableName' => 'users',
            'recordId'  => $args['id'],
            'eventType' => 'MODIFICATION',
            'info'      => "userUpdated",
        ]);

        $user = UserModel::getById(['select' => ['firstname', 'lastname', 'picture'], 'id' => $args['id']]);

        return $response->withJson(['user' => $user]);
    }

    public function updatePassword(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id']) {
            return $response->withStatus(403)->withJson(['errors' => 'User out of perimeter']);
        }

        $data = $request->getParams();
        $check = Validator::stringType()->notEmpty()->validate($data['currentPassword']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['newPassword']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['passwordConfirmation']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $user = UserModel::getById(['select' => ['email'], 'id' => $args['id']]);
        if ($data['newPassword'] != $data['passwordConfirmation']) {
            return $response->withStatus(400)->withJson(['errors' => 'New password does not match password confirmation']);
        } elseif (!AuthenticationModel::authentication(['email' => $user['email'], 'password' => $data['currentPassword']])) {
            return $response->withStatus(401)->withJson(['errors' => 'Wrong Password']);
        } elseif (!PasswordController::isPasswordValid(['password' => $data['newPassword']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Password does not match security criteria']);
        }

        UserModel::updatePassword(['id' => $args['id'], 'password' => $data['newPassword']]);

        HistoryController::add([
            'tableName' => 'users',
            'recordId'  => $args['id'],
            'eventType' => 'MODIFICATION',
            'info'      => "passwordUpdated",
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function getSignatures(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id']) {
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
        if ($GLOBALS['id'] != $args['id']) {
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

        HistoryController::add([
            'tableName' => 'signatures',
            'recordId'  => $id,
            'eventType' => 'CREATION',
            'info'      => "signatureAdded",
        ]);

        return $response->withJson(['signatureId' => $id]);
    }

    public function deleteSignature(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id']) {
            return $response->withStatus(403)->withJson(['errors' => 'User out of perimeter']);
        }

        UserModel::deleteSignature(['where' => ['user_id = ?', 'id = ?'], 'data' => [$args['id'], $args['signatureId']]]);

        HistoryController::add([
            'tableName' => 'signatures',
            'recordId'  => $args['signatureId'],
            'eventType' => 'SUPPRESSION',
            'info'      => "signatureDeleted",
        ]);

        return $response->withJson(['success' => 'success']);
    }
}
