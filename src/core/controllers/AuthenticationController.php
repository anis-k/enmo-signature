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
use Firebase\JWT\JWT;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\AuthenticationModel;
use SrcCore\models\CoreConfigModel;
use User\controllers\UserController;
use User\models\UserModel;

class AuthenticationController
{
    const MAX_DURATION_TOKEN = 30; //Minutes
    const ROUTES_WITHOUT_AUTHENTICATION = [
        'GET/authenticationInformations', 'POST/authenticate', 'GET/authenticate/token',
        'POST/password', 'PUT/password', 'GET/passwordRules', 'GET/languages/{lang}'
    ];


    public static function getInformations(Request $request, Response $response)
    {
        $connection = ConfigurationModel::getConnection();
        $encryptKey = CoreConfigModel::getEncryptKey();

        return $response->withJson(['connection' => $connection, 'changeKey' => $encryptKey == 'Security Key Maarch Parapheur #2008']);
    }

    public static function authentication($authorizationHeaders = [])
    {
        $id = null;
        if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
            if (AuthenticationModel::authentication(['login' => $_SERVER['PHP_AUTH_USER'], 'password' => $_SERVER['PHP_AUTH_PW']])) {
                $user = UserModel::getByLogin(['select' => ['id'], 'login' => $_SERVER['PHP_AUTH_USER']]);
                $id = $user['id'];
            }
        } else {
            if (!empty($authorizationHeaders)) {
                $token = null;
                foreach ($authorizationHeaders as $authorizationHeader) {
                    if (strpos($authorizationHeader, 'Bearer') === 0) {
                        $token = str_replace('Bearer ', '', $authorizationHeader);
                    }
                }
                if (!empty($token)) {
                    try {
                        $jwt = (array)JWT::decode($token, CoreConfigModel::getEncryptKey(), ['HS256']);
                    } catch (\Exception $e) {
                        return null;
                    }
                    $jwt['user'] = (array)$jwt['user'];
                    if (!empty($jwt) && !empty($jwt['user']['id'])) {
                        $id = $jwt['user']['id'];
                    }
                }
            }
        }

        return $id;
    }

    public static function authenticate(Request $request, Response $response)
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
                $ldap = @ldap_connect($uri);
                if ($ldap === false) {
                    $error = 'Ldap connect failed : uri is maybe wrong';
                    continue;
                }
                ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
                ldap_set_option($ldap, LDAP_OPT_NETWORK_TIMEOUT, 10);
                $login = (!empty($ldapConfiguration['prefix']) ? $ldapConfiguration['prefix'] . '\\' . $body['login'] : $body['login']);
                $login = (!empty($ldapConfiguration['suffix']) ? $login . $ldapConfiguration['suffix'] : $login);
                if (!empty($ldapConfiguration['baseDN'])) { //OpenLDAP
                    $search = @ldap_search($ldap, $ldapConfiguration['baseDN'], "(uid={$login})", ['dn']);
                    if ($search === false) {
                        $error = ldap_error($ldap);
                        continue;
                    }
                    $entries = ldap_get_entries($ldap, $search);
                    $login = $entries[0]['dn'];
                }
                $authenticated = @ldap_bind($ldap, $login, $body['password']);
                if (!$authenticated) {
                    $error = ldap_error($ldap);
                }
            }
            if (empty($authenticated) && !empty($error) && $error != 'Invalid credentials') {
                return $response->withStatus(400)->withJson(['errors' => $error]);
            }
        } else {
            $authenticated = AuthenticationModel::authentication(['login' => $body['login'], 'password' => $body['password']]);
        }
        if (empty($authenticated)) {
            return $response->withStatus(401)->withJson(['errors' => 'Authentication Failed']);
        }

        $user = UserModel::getByLogin(['login' => $body['login'], 'select' => ['id', 'mode', 'refresh_token']]);
        if (empty($user) || $user['mode'] != 'standard') {
            return $response->withStatus(403)->withJson(['errors' => 'Authentication unauthorized']);
        }

        $GLOBALS['id'] = $user['id'];

        $user['refresh_token'] = json_decode($user['refresh_token'], true);
        foreach ($user['refresh_token'] as $key => $refreshToken) {
            try {
                JWT::decode($refreshToken, CoreConfigModel::getEncryptKey(), ['HS256']);
            } catch (\Exception $e) {
                unset($user['refresh_token'][$key]);
            }
        }
        $user['refresh_token'] = array_values($user['refresh_token']);
        if (count($user['refresh_token']) > 10) {
            array_shift($user['refresh_token']);
        }

        $refreshToken = AuthenticationController::getRefreshJWT();
        $user['refresh_token'][] = $refreshToken;
        UserModel::update([
            'set'   => ['reset_token' => null, 'refresh_token' => json_encode($user['refresh_token'])],
            'where' => ['id = ?'],
            'data'  => [$user['id']]
        ]);
        $response = $response->withHeader('Token', AuthenticationController::getJWT());
        $response = $response->withHeader('Refresh-Token', $refreshToken);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'users',
            'objectId'      => $user['id'],
            'type'          => 'LOGIN',
            'message'       => '{userLogIn}'
        ]);

        return $response->withStatus(204);
    }

    public static function getRefreshedToken(Request $request, Response $response)
    {
        $queryParams = $request->getQueryParams();

        if (!Validator::stringType()->notEmpty()->validate($queryParams['refreshToken'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Refresh Token is empty']);
        }

        try {
            $jwt = JWT::decode($queryParams['refreshToken'], CoreConfigModel::getEncryptKey(), ['HS256']);
        } catch (\Exception $e) {
            return $response->withStatus(401)->withJson(['errors' => 'Authentication Failed']);
        }

        $user = UserModel::getById(['select' => ['id', 'refresh_token'], 'id' => $jwt->user->id]);
        if (empty($user['refresh_token'])) {
            return $response->withStatus(401)->withJson(['errors' => 'Authentication Failed']);
        }

        $user['refresh_token'] = json_decode($user['refresh_token'], true);
        if (!in_array($queryParams['refreshToken'], $user['refresh_token'])) {
            return $response->withStatus(401)->withJson(['errors' => 'Authentication Failed']);
        }

        $GLOBALS['id'] = $user['id'];

        return $response->withJson(['token' => AuthenticationController::getJWT()]);
    }

    public static function getJWT()
    {
        $sessionTime = AuthenticationController::MAX_DURATION_TOKEN;

        $loadedXml = CoreConfigModel::getConfig();
        if ($loadedXml) {
            if (!empty($loadedXml->config->sessionTime)) {
                if ($sessionTime > (int)$loadedXml->config->sessionTime) {
                    $sessionTime = (int)$loadedXml->config->sessionTime;
                }
            }
        }

        $user = UserController::getUserInformationsById(['id' => $GLOBALS['id']]);
        unset($user['picture']);

        $token = [
            'exp'   => time() + 60 * $sessionTime,
            'user'  => $user
        ];

        $jwt = JWT::encode($token, CoreConfigModel::getEncryptKey());

        return $jwt;
    }

    public static function getRefreshJWT()
    {
        $sessionTime = AuthenticationController::MAX_DURATION_TOKEN;

        $loadedXml = CoreConfigModel::getConfig();
        if ($loadedXml) {
            if (!empty($loadedXml->config->sessionTime)) {
                $sessionTime = (int)$loadedXml->config->sessionTime;
            }
        }

        $token = [
            'exp'   => time() + 60 * $sessionTime,
            'user'  => [
                'id' => $GLOBALS['id']
            ]
        ];

        $jwt = JWT::encode($token, CoreConfigModel::getEncryptKey());

        return $jwt;
    }

    public static function getResetJWT()
    {
        $token = [
            'exp'   => time() + 3600,
            'user'  => [
                'id' => $GLOBALS['id']
            ]
        ];

        $jwt = JWT::encode($token, CoreConfigModel::getEncryptKey());

        return $jwt;
    }
}
