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

use Configuration\models\ConfigurationModel;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\AuthenticationModel;
use User\controllers\UserController;
use User\models\UserModel;

class AuthenticationController
{
    public static function authentication()
    {
        $id = null;
        if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
            if (AuthenticationModel::authentication(['login' => $_SERVER['PHP_AUTH_USER'], 'password' => $_SERVER['PHP_AUTH_PW']])) {
                $user = UserModel::getByLogin(['select' => ['id'], 'login' => $_SERVER['PHP_AUTH_USER']]);
                $id = $user['id'];
            }
        } else {
            $cookie = AuthenticationModel::getCookieAuth();
            if (!empty($cookie) && AuthenticationModel::cookieAuthentication($cookie)) {
                AuthenticationModel::setCookieAuth(['id' => $cookie['id']]);
                $id = $cookie['id'];
            }
        }

        return $id;
    }

    public static function log(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        $check = Validator::stringType()->notEmpty()->validate($body['login']);
        $check = $check && Validator::stringType()->notEmpty()->validate($body['password']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $connection = ConfigurationModel::getConnection();
        if ($connection == 'ldap') {
            $ldapConfigurations = ConfigurationModel::getByIdentifier(['identifier' => 'ldapServer', 'select' => ['value']]);
            if (empty($ldapConfigurations)) {
                return $response->withStatus(400)->withJson(['errors' => 'Ldap configuration is missing']);
            }
            $ldapConfigurations = json_decode($ldapConfigurations['value'], true);
            foreach ($ldapConfigurations as $ldapConfiguration) {
                $uri = ($ldapConfiguration['ssl'] === true ? "LDAPS://{$ldapConfiguration['uri']}" : $ldapConfiguration['uri']);
                $ldap = ldap_connect($uri);
                if ($ldap !== false) {
                    break;
                }
            }
            if (empty($ldap)) {
                return $response->withStatus(400)->withJson(['errors' => 'Ldap connection failed']);
            }
            ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
            ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
            $login = (!empty($ldapConfiguration['prefix']) ? $ldapConfiguration['prefix'] . '\\' . $body['login'] : $body['login']);
            $authenticated = @ldap_bind($ldap, $login, $body['password']);
        } else {
            $authenticated = AuthenticationModel::authentication(['login' => $body['login'], 'password' => $body['password']]);
        }

        if (!$authenticated) {
            return $response->withStatus(401)->withJson(['errors' => 'Authentication Failed']);
        }

        $user = UserModel::getByLogin(['login' => $body['login'], 'select' => ['id', 'mode']]);
        if ($user['mode'] != 'standard') {
            return $response->withStatus(403)->withJson(['errors' => 'Login unauthorized']);
        }

        AuthenticationModel::setCookieAuth(['id' => $user['id']]);

        $GLOBALS['id'] = $user['id'];
        UserModel::update(['set' => ['reset_token' => json_encode(['token' => null, 'until' => null])], 'where' => ['id = ?'], 'data' => [$user['id']]]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'users',
            'objectId'      => $user['id'],
            'type'          => 'LOGIN',
            'message'       => '{userLogIn}'
        ]);

        return $response->withJson(['user' => UserController::getUserInformationsById(['id' => $user['id']])]);
    }

    public static function logout(Request $request, Response $response)
    {
        AuthenticationModel::deleteCookieAuth();

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'users',
            'objectId'      => $GLOBALS['id'],
            'type'          => 'LOGOUT',
            'message'       => '{userLogOut}'
        ]);

        return $response->withJson(['success' => 'success']);
    }
}
