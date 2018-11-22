<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Authentication Controller
 *
 * @author dev@maarch.org
 */

namespace SrcCore\controllers;

use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\AuthenticationModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\LangModel;
use User\models\UserModel;

class AuthenticationController
{
    public static function authentication()
    {
        $id = null;
        if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
            if (AuthenticationModel::authentication(['email' => $_SERVER['PHP_AUTH_USER'], 'password' => $_SERVER['PHP_AUTH_PW']])) {
                $user = UserModel::getByEmail(['select' => ['id'], 'email' => $_SERVER['PHP_AUTH_USER']]);
                $id = $user['id'];
                new LangModel(['language' => CoreConfigModel::getLanguage()]);
            }
        } else {
            $cookie = AuthenticationModel::getCookieAuth();
            if (!empty($cookie) && AuthenticationModel::cookieAuthentication($cookie)) {
                AuthenticationModel::setCookieAuth(['id' => $cookie['id']]);
                $id = $cookie['id'];
                new LangModel(['language' => $cookie['lang']]);
            }
        }

        return $id;
    }

    public static function log(Request $request, Response $response)
    {
        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['email']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['password']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        if (!AuthenticationModel::authentication(['email' => $data['email'], 'password' => $data['password']])) {
            return $response->withStatus(401)->withJson(['errors' => 'Authentication Failed']);
        }

        $user = UserModel::getByEmail(['email' => $data['email'], 'select' => ['id', 'firstname', 'lastname', 'email', 'picture', 'mode']]);
        if ($user['mode'] != 'standard') {
            return $response->withStatus(403)->withJson(['errors' => 'Login unauthorized']);
        }

        AuthenticationModel::setCookieAuth(['id' => $user['id']]);

        $GLOBALS['id'] = $user['id'];
        HistoryController::add([
            'tableName' => 'users',
            'recordId'  => $user['id'],
            'eventType' => 'AUTHENTICATION',
            'info'      => "userLogin"
        ]);

        return $response->withJson(['user' => $user]);
    }

    public static function logout(Request $request, Response $response)
    {
        AuthenticationModel::deleteCookieAuth();

        HistoryController::add([
            'tableName' => 'users',
            'recordId'  => $GLOBALS['id'],
            'eventType' => 'AUTHENTICATION',
            'info'      => "userLogout"
        ]);

        return $response->withJson(['success' => 'success']);
    }
}
