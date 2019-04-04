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
use SrcCore\models\ValidatorModel;
use User\models\UserGroupModel;
use User\models\UserModel;

class UserController
{
    public function get(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();

        if (!empty($queryParams['mode']) && $queryParams['mode'] == 'rest') {
            if (!UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_rest_users'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }
            $queryData = ['rest'];
        } else {
            if (!UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }
            $queryData = ['standard'];
        }

        $users = UserModel::get([
            'select'    => ['id', 'login', 'firstname', 'lastname'],
            'where'     => ['mode = ?'],
            'data'      => $queryData,
            'orderBy'   => ['lastname', 'firstname']
        ]);

        return $response->withJson(['users' => $users]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id'] && !UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        return $response->withJson(['user' => UserController::getUserInformationsById(['id' => $args['id']])]);
    }

    public function create(Request $request, Response $response)
    {
        if (!UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $data = $request->getParams();

        if (!Validator::stringType()->notEmpty()->validate($data['firstname'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Le prénom est vide']);
        }
        if (!Validator::stringType()->notEmpty()->validate($data['lastname'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Le nom est vide']);
        }
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return $response->withStatus(400)->withJson(['errors' => 'L\'email est vide ou le format n\'est pas correct']);
        }

        $existingUser = UserModel::getByEmail(['email' => $data['email'], 'select' => ['id']]);
        if (!empty($existingUser)) {
            return $response->withStatus(400)->withJson(['errors' => 'L\'email existe déjà']);
        }

        $logingModes = ['standard', 'rest'];
        if (!in_array($data['mode'], $logingModes)) {
            $data['mode'] = 'standard';
        }

        UserModel::create(['user' => $data]);

        $newUser = UserModel::getByEmail(['email' => $data['email']]);
        if (!Validator::intType()->notEmpty()->validate($newUser['id'])) {
            return $response->withStatus(500)->withJson(['errors' => 'User Creation Error']);
        }

        HistoryController::add([
            'tableName' => 'users',
            'recordId'  => $newUser['id'],
            'eventType' => 'ADD',
            'info'      => "userCreation",
        ]);

        return $response->withJson(['user' => $newUser]);
    }

    public function update(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id'] && !UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $data = $request->getParams();
        $check = Validator::stringType()->notEmpty()->validate($data['firstname']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['lastname']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $check = Validator::arrayType()->notEmpty()->validate($data['preferences']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['preferences']['writingMode']);
        $check = $check && Validator::intType()->notEmpty()->validate($data['preferences']['writingSize']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['preferences']['writingColor']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Missing parameter in user preferences data']);
        }

        $data['preferences'] = json_encode($data['preferences']);
        if (!is_string($data['preferences'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Wrong format for user preferences data']);
        }

        if (!empty($data['picture'])) {
            $infoContent = '';
            if (preg_match('/^data:image\/(\w+);base64,/', $data['picture'])) {
                $infoContent = substr($data['picture'], 0, strpos($data['picture'], ',') + 1);
                $data['picture'] = substr($data['picture'], strpos($data['picture'], ',') + 1);
            }
            $picture    = base64_decode($data['picture']);
            $finfo      = new \finfo(FILEINFO_MIME_TYPE);
            $mimeType   = $finfo->buffer($picture);
            $type       = explode('/', $mimeType);

            if ($type[0] != 'image') {
                return $response->withStatus(400)->withJson(['errors' => 'Picture is not an image']);
            }

            if (!empty($data['pictureOrientation'])) {
                $imagick = new \Imagick();
                $imagick->readImageBlob(base64_decode($data['picture']));
                $imagick->rotateImage(new \ImagickPixel(), $data['pictureOrientation']);
                $data['picture'] = base64_encode($imagick->getImageBlob());
            }
            $data['picture'] = $infoContent . $data['picture'];
        }

        $data['id'] = $args['id'];
        UserModel::update($data);

        HistoryController::add([
            'tableName' => 'users',
            'recordId'  => $args['id'],
            'eventType' => 'MODIFICATION',
            'info'      => "userUpdated",
        ]);

        return $response->withJson(['user' => UserController::getUserInformationsById(['id' => $args['id']])]);
    }

    public function updatePassword(Request $request, Response $response, array $args)
    {
        $data = $request->getParams();
        if (!Validator::stringType()->notEmpty()->validate($data['newPassword'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $user = UserModel::getById(['select' => ['login', 'mode'], 'id' => $args['id']]);
        if ($GLOBALS['id'] != $args['id']) {
            if ($user['mode'] == 'rest' && !UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_rest_users'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            } elseif ($user['mode'] == 'standard' && !UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }
        }

        if ($user['mode'] == 'standard') {
            if ($data['newPassword'] != $data['passwordConfirmation']) {
                return $response->withStatus(400)->withJson(['errors' => 'New password does not match password confirmation']);
            } elseif (empty($data['currentPassword']) || !AuthenticationModel::authentication(['login' => $user['login'], 'password' => $data['currentPassword']])) {
                return $response->withStatus(401)->withJson(['errors' => 'Wrong Password']);
            }
        }
        if (!PasswordController::isPasswordValid(['password' => $data['newPassword']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Password does not match security criteria']);
        }

        UserModel::updatePassword(['id' => $args['id'], 'password' => $data['newPassword']]);

        if ($user['mode'] == 'standard') {
            AuthenticationModel::revokeCookie(['userId' => $args['id']]);
        }

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
        if ($GLOBALS['id'] != $args['id'] && !UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
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
            'orderBy'   => ['id DESC'],
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
        if ($GLOBALS['id'] != $args['id'] && !UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
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
        if ($GLOBALS['id'] != $args['id'] && !UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
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

    public static function getUserInformationsById(array $args)
    {
        ValidatorModel::notEmpty($args, ['id']);
        ValidatorModel::intVal($args, ['id']);

        $user = UserModel::getById(['select' => ['id', 'email', 'firstname', 'lastname', 'picture', 'preferences'], 'id' => $args['id']]);

        if (empty($user['picture'])) {
            $user['picture'] = base64_encode(file_get_contents('src/frontend/assets/user_picture.png'));
            $user['picture'] = 'data:image/png;base64,' . $user['picture'];
        }

        $user['preferences'] = (array)json_decode($user['preferences']);
        $user['canManageRestUsers'] = UserController::hasPrivilege(['userId' => $args['id'], 'privilege' => 'manage_rest_users']);

        return $user;
    }

    public static function hasPrivilege(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId', 'privilege']);
        ValidatorModel::intVal($args, ['userId']);
        ValidatorModel::stringType($args, ['privilege']);

        $groups = UserGroupModel::get(['select' => ['group_id'], 'where' => ['user_id = ?'], 'data' => [$args['userId']]]);

        foreach ($groups as $group) {
            $privilege = UserGroupModel::getPrivileges(['select' => [1], 'where' => ['group_id = ?', 'privilege = ?'], 'data' => [$group['group_id'], $args['privilege']]]);
            if (!empty($privilege)) {
                return true;
            }
        }

        return false;
    }
}
