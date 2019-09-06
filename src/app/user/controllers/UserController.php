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

use Email\controllers\EmailController;
use Firebase\JWT\JWT;
use Group\controllers\PrivilegeController;
use Group\models\GroupModel;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\AuthenticationController;
use SrcCore\controllers\LanguageController;
use SrcCore\controllers\PasswordController;
use SrcCore\controllers\UrlController;
use SrcCore\models\AuthenticationModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\ValidatorModel;
use User\models\UserGroupModel;
use User\models\UserModel;
use Workflow\models\WorkflowModel;

class UserController
{
    public function get(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();

        $select = ['id', 'firstname', 'lastname', 'substitute'];
        $where = [];
        $queryData = [];
        if (empty($queryParams['mode'])) {
            $where = ['"isRest" = ?'];
            $queryData = ['false'];
        }
        if (PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            $select[] = 'login';
            $select[] = 'email';
        }

        $users = UserModel::get([
            'select'    => $select,
            'where'     => $where,
            'data'      => $queryData,
            'orderBy'   => ['lastname', 'firstname']
        ]);

        foreach ($users as $key => $user) {
            $users[$key]['substitute'] = !empty($user['substitute']);
        }

        return $response->withJson(['users' => $users]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id'] && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        $user = UserController::getUserInformationsById(['id' => $args['id']]);
        if (empty($user)) {
            return $response->withStatus(400)->withJson(['errors' => 'User does not exist']);
        }

        $user['groups'] = [];

        $userGroups = UserGroupModel::get(['select' => ['group_id'], 'where' => ['user_id = ?'], 'data' => [$args['id']]]);
        $groupsIds = array_column($userGroups, 'group_id');
        if (!empty($groupsIds)) {
            $groups = GroupModel::get(['select' => ['label'], 'where' => ['id in (?)'], 'data' => [$groupsIds]]);
            $user['groups'] = $groups;
        }

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'users',
            'objectId'      => $args['id'],
            'type'          => 'VIEW',
            'message'       => "{userViewed} : {$user['firstname']} {$user['lastname']}"
        ]);

        return $response->withJson(['user' => $user]);
    }

    public function create(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $body = $request->getParsedBody();

        if (empty($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is not set or empty']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['login']) || !preg_match("/^[\w.@-]*$/", $body['login'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body login is empty, not a string or wrong formatted']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['firstname'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body firstname is empty or not a string']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['lastname'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body lastname is empty or not a string']);
        } elseif (empty($body['email']) || !filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body email is empty or not a valid email']);
        }

        $body['login'] = strtolower($body['login']);
        $existingUser = UserModel::getByLogin(['login' => $body['login'], 'select' => [1]]);
        if (!empty($existingUser)) {
            return $response->withStatus(400)->withJson(['errors' => 'Login already exists', 'lang' => 'userLoginAlreadyExists']);
        }

        if (!empty($body['isRest'])) {
            $body['"isRest"'] = true;
        }
        if (empty($body['picture'])) {
            $body['picture'] = base64_encode(file_get_contents('src/frontend/assets/user_picture.png'));
            $body['picture'] = 'data:image/png;base64,' . $body['picture'];
        }

        $id = UserModel::create($body);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'users',
            'objectId'      => $id,
            'type'          => 'CREATION',
            'message'       => "{userAdded} : {$body['firstname']} {$body['lastname']}"
        ]);

        return $response->withJson(['id' => $id]);
    }

    public function update(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id'] && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $body = $request->getParsedBody();

        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['firstname'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body firstname is empty or not a string']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['lastname'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body lastname is empty or not a string']);
        } elseif (empty($body['email']) || !filter_var($body['email'], FILTER_VALIDATE_EMAIL)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body email is empty or not a valid email']);
        }

        $user = UserModel::getById(['id' => $args['id'], 'select' => [1]]);
        if (empty($user)) {
            return $response->withStatus(400)->withJson(['errors' => 'User does not exist']);
        }

        $set = [
            'firstname'     => $body['firstname'],
            'lastname'      => $body['lastname'],
            'email'         => $body['email']
        ];

        UserModel::update([
            'set'   => $set,
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'users',
            'objectId'      => $args['id'],
            'type'          => 'MODIFICATION',
            'message'       => "{userUpdated} : {$body['firstname']} {$body['lastname']}"
        ]);

        return $response->withJson(['user' => UserController::getUserInformationsById(['id' => $args['id']])]);
    }

    public function updatePicture(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id']) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['picture'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body picture is empty']);
        }

        $user = UserModel::getById(['id' => $args['id'], 'select' => ['firstname', 'lastname']]);
        if (empty($user)) {
            return $response->withStatus(400)->withJson(['errors' => 'User does not exist']);
        }

        $infoContent = '';
        if (preg_match('/^data:image\/(\w+);base64,/', $body['picture'])) {
            $infoContent = substr($body['picture'], 0, strpos($body['picture'], ',') + 1);
            $body['picture'] = substr($body['picture'], strpos($body['picture'], ',') + 1);
        }
        $picture    = base64_decode($body['picture']);
        $finfo      = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType   = $finfo->buffer($picture);
        $type       = explode('/', $mimeType);

        if ($type[0] != 'image') {
            return $response->withStatus(400)->withJson(['errors' => 'Picture is not an image']);
        }

        if (!empty($body['pictureOrientation'])) {
            $imagick = new \Imagick();
            $imagick->readImageBlob(base64_decode($body['picture']));
            $imagick->rotateImage(new \ImagickPixel(), $body['pictureOrientation']);
            $body['picture'] = base64_encode($imagick->getImageBlob());
        }

        $set = [
            'picture'     => $infoContent . $body['picture']
        ];

        UserModel::update([
            'set'   => $set,
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'users',
            'objectId'      => $args['id'],
            'type'          => 'MODIFICATION',
            'message'       => "{userUpdated} : {$user['firstname']} {$user['lastname']}"
        ]);

        return $response->withStatus(204);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users']) || $GLOBALS['id'] == $args['id']) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        $user = UserModel::getById(['id' => $args['id'], 'select' => ['firstname', 'lastname']]);
        if (empty($user)) {
            return $response->withStatus(400)->withJson(['errors' => 'User does not exist']);
        }

        $substitutedUsers = UserModel::get(['select' => ['id'], 'where' => ['substitute = ?'], 'data' => [$args['id']]]);
        $allSubstitutedUsers = array_column($substitutedUsers, 'id');

        $workflowSelect = "SELECT id FROM workflows ws WHERE workflows.main_document_id = main_document_id AND process_date IS NULL AND status IS NULL ORDER BY \"order\" LIMIT 1";
        $workflows = WorkflowModel::get([
            'select'    => [1],
            'where'     => ['user_id in (?)', "(id) in ({$workflowSelect})"],
            'data'      => [array_merge([$args['id']], $allSubstitutedUsers)]
        ]);
        if (!empty($workflows)) {
            return $response->withStatus(400)->withJson(['errors' => 'User has current documents', 'lang' => 'userHasCurrentDocuments']);
        }

        //Workflows
        WorkflowModel::delete(['where' => ['process_date is null', 'status is null', 'user_id = ?'], 'data' => [$args['id']]]);

        //Substituted Users
        if (!empty($allSubstitutedUsers)) {
            UserModel::update(['set' => ['substitute' => null], 'where' => ['id in (?)'], 'data' => [$allSubstitutedUsers]]);
        }

        //Groups
        UserGroupModel::delete(['where' => ['user_id = ?'], 'data' => [$args['id']]]);

        UserModel::delete(['id' => $args['id']]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'users',
            'objectId'      => $args['id'],
            'type'          => 'SUPPRESSION',
            'message'       => "{userDeleted} : {$user['firstname']} {$user['lastname']}"
        ]);

        return $response->withStatus(204);
    }

    public function getPictureById(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        $user = UserModel::getById(['select' => ['picture'], 'id' => $args['id']]);
        if (empty($user)) {
            return $response->withStatus(400)->withJson(['errors' => 'User does not exist']);
        }

        return $response->withJson(['picture' => $user['picture']]);
    }

    public function getSubstituteById(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users']) && $GLOBALS['id'] != $args['id']) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $user = UserModel::getById(['select' => ['substitute'], 'id' => $args['id']]);
        if (empty($user)) {
            return $response->withStatus(400)->withJson(['errors' => 'User does not exist']);
        }

        return $response->withJson(['substitute' => $user['substitute']]);
    }

    public function updatePreferences(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id']) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $body = $request->getParsedBody();

        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['lang'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body lang is empty or not a string']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['writingMode'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body writingMode is empty or not a string']);
        } elseif (!Validator::intType()->notEmpty()->validate($body['writingSize'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body writingSize is empty or not an integer']);
        } elseif (!Validator::stringType()->notEmpty()->validate($body['writingColor'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body writingColor is empty or not a string']);
        } elseif (!Validator::boolType()->validate($body['notifications'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body notifications is empty or not a boolean']);
        }

        $user = UserModel::getById(['id' => $args['id'], 'select' => ['firstname', 'lastname']]);
        if (empty($user)) {
            return $response->withStatus(400)->withJson(['errors' => 'User does not exist']);
        }

        $preferences = json_encode([
            'lang'          => $body['lang'],
            'writingMode'   => $body['writingMode'],
            'writingSize'   => $body['writingSize'],
            'writingColor'  => $body['writingColor'],
            'notifications' => $body['notifications'],
        ]);
        if (!is_string($preferences)) {
            return $response->withStatus(400)->withJson(['errors' => 'Wrong format for user preferences data']);
        }

        UserModel::update([
            'set'   => ['preferences' => $preferences],
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'users',
            'objectId'      => $args['id'],
            'type'          => 'MODIFICATION',
            'message'       => "{userUpdated} : {$user['firstname']} {$user['lastname']}"
        ]);

        return $response->withStatus(204);
    }

    public function updateSubstitute(Request $request, Response $response, array $args)
    {
        if ($GLOBALS['id'] != $args['id']) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $body = $request->getParsedBody();

        $user = UserModel::getById(['id' => $args['id'], 'select' => ['firstname', 'lastname']]);
        if (empty($user)) {
            return $response->withStatus(400)->withJson(['errors' => 'User does not exist']);
        }

        $set = [
            'substitute' => null
        ];

        if (!empty($body['substitute']) && $args['id'] != $body['substitute']) {
            $existingUser = UserModel::getById(['id' => $body['substitute'], 'select' => ['substitute']]);
            if (empty($existingUser)) {
                return $response->withStatus(400)->withJson(['errors' => 'Substitute user does not exist']);
            } elseif (!empty($existingUser['substitute'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Substitute user has already substituted']);
            }

            $substitutedUsers = UserModel::get(['select' => ['id'], 'where' => ['substitute = ?'], 'data' => [$args['id']]]);
            foreach ($substitutedUsers as $user) {
                UserModel::update([
                    'set'   => ['substitute' => $body['substitute']],
                    'where' => ['id = ?'],
                    'data'  => [$user['id']]
                ]);
            }
            $set['substitute'] = $body['substitute'];
        }

        UserModel::update([
            'set'   => $set,
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'users',
            'objectId'      => $args['id'],
            'type'          => 'MODIFICATION',
            'message'       => "{userUpdated} : {$user['firstname']} {$user['lastname']}"
        ]);

        return $response->withStatus(204);
    }

    public function updatePassword(Request $request, Response $response, array $args)
    {
        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id is not an integer']);
        }

        if ($GLOBALS['id'] != $args['id']) {
            if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
                return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
            }
        }

        $body = $request->getParsedBody();
        if (!Validator::stringType()->notEmpty()->validate($body['newPassword'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body newPassword is empty or not a string']);
        } elseif ($body['newPassword'] != $body['passwordConfirmation']) {
            return $response->withStatus(400)->withJson(['errors' => 'Body newPassword and passwordConfirmation must be identical']);
        }

        $user = UserModel::getById(['select' => ['login', '"isRest"'], 'id' => $args['id']]);

        if ($user['isRest'] == false) {
            if (empty($body['currentPassword']) || !AuthenticationModel::authentication(['login' => $user['login'], 'password' => $body['currentPassword']])) {
                return $response->withStatus(401)->withJson(['errors' => 'Wrong Password']);
            }
        }
        if (!PasswordController::isPasswordValid(['password' => $body['newPassword']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Password does not match security criteria']);
        }

        UserModel::updatePassword(['id' => $args['id'], 'password' => $body['newPassword']]);

        $refreshToken = [];
        if ($GLOBALS['id'] == $args['id']) {
            $refreshJWT = AuthenticationController::getRefreshJWT();
            $refreshToken[] = $refreshJWT;
            $response = $response->withHeader('Token', AuthenticationController::getJWT());
            $response = $response->withHeader('Refresh-Token', $refreshJWT);
        }

        UserModel::update([
            'set'   => ['refresh_token' => json_encode($refreshToken)],
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'users',
            'objectId'      => $args['id'],
            'type'          => 'MODIFICATION',
            'message'       => '{userPasswordUpdated}'
        ]);

        return $response->withStatus(204);
    }

    public function forgotPassword(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        if (!Validator::stringType()->notEmpty()->validate($body['login'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad request']);
        }

        $user = UserModel::getByLogin(['select' => ['id', 'email', 'preferences'], 'login' => strtolower($body['login'])]);
        if (empty($user)) {
            return $response->withStatus(204);
        }

        $GLOBALS['id'] = $user['id'];

        $resetToken = AuthenticationController::getResetJWT();
        UserModel::update(['set' => ['reset_token' => $resetToken], 'where' => ['id = ?'], 'data' => [$user['id']]]);

        $user['preferences'] = json_decode($user['preferences'], true);
        $lang = LanguageController::get(['lang' => $user['preferences']['lang']]);

        $url = UrlController::getCoreUrl() . 'dist/update-password?token=' . $resetToken;
        EmailController::createEmail([
            'userId'    => $user['id'],
            'data'      => [
                'sender'        => 'Notification',
                'recipients'    => [$user['email']],
                'subject'       => $lang['notificationForgotPasswordSubject'],
                'body'          => $lang['notificationForgotPasswordBody'] . $url . $lang['notificationForgotPasswordFooter'],
                'isHtml'        => true
            ]
        ]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'users',
            'objectId'      => $user['id'],
            'type'          => 'MODIFICATION',
            'message'       => '{userPasswordForgotten}'
        ]);

        return $response->withStatus(204);
    }

    public static function updateForgottenPassword(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        $check = Validator::stringType()->notEmpty()->validate($body['token']);
        $check = $check && Validator::stringType()->notEmpty()->validate($body['password']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        try {
            $jwt = JWT::decode($body['token'], CoreConfigModel::getEncryptKey(), ['HS256']);
        } catch (\Exception $e) {
            return $response->withStatus(403)->withJson(['errors' => 'Invalid token', 'lang' => 'invalidToken']);
        }

        $user = UserModel::getById(['id' => $jwt->user->id, 'select' => ['id', 'reset_token']]);
        if (empty($user)) {
            return $response->withStatus(400)->withJson(['errors' => 'User does not exist']);
        }

        if ($body['token'] != $user['reset_token']) {
            return $response->withStatus(403)->withJson(['errors' => 'Invalid token', 'lang' => 'invalidToken']);
        }

        if (!PasswordController::isPasswordValid(['password' => $body['password']])) {
            return $response->withStatus(400)->withJson(['errors' => 'Password does not match security criteria']);
        }

        UserModel::update([
            'set' => [
                'password'                      => AuthenticationModel::getPasswordHash($body['password']),
                'password_modification_date'    => 'CURRENT_TIMESTAMP',
                'reset_token'                   => null,
                'refresh_token'                 => '[]'
            ],
            'where' => ['id = ?'],
            'data'  => [$user['id']]
        ]);

        $GLOBALS['id'] = $user['id'];
        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'users',
            'objectId'      => $user['id'],
            'type'          => 'MODIFICATION',
            'message'       => '{userForgottenPasswordUpdated}'
        ]);

        return $response->withStatus(204);
    }

    public static function getUserInformationsById(array $args)
    {
        ValidatorModel::notEmpty($args, ['id']);
        ValidatorModel::intVal($args, ['id']);

        $user = UserModel::getById(['select' => ['id', 'login', 'email', 'firstname', 'lastname', 'picture', 'preferences', 'substitute', '"isRest"'], 'id' => $args['id']]);
        if (empty($user)) {
            return [];
        }

        if (empty($user['picture'])) {
            $user['picture'] = base64_encode(file_get_contents('src/frontend/assets/user_picture.png'));
            $user['picture'] = 'data:image/png;base64,' . $user['picture'];
        }

        if ($GLOBALS['id'] == $args['id']) {
            $user['preferences']                = json_decode($user['preferences'], true);
            $user['availableLanguages']         = LanguageController::getAvailableLanguages();
            $user['administrativePrivileges']   = PrivilegeController::getAdministrativePrivilegesByUserId(['userId' => $args['id']]);
            if (!empty($user['substitute'])) {
                $user['substituteUser'] = UserModel::getLabelledUserById(['id' => $user['substitute']]);
            }
        }

        return $user;
    }
}
