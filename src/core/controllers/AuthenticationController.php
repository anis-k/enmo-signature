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
use Email\controllers\EmailController;
use Firebase\JWT\JWT;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\controllers\LanguageController;
use SrcCore\controllers\UrlController;
use SrcCore\models\AuthenticationModel;
use SrcCore\models\CoreConfigModel;
use SrcCore\models\PasswordModel;
use SrcCore\models\ValidatorModel;
use User\controllers\UserController;
use User\models\UserModel;

class AuthenticationController
{
    const MAX_DURATION_TOKEN = 30; //Minutes
    const ROUTES_WITHOUT_AUTHENTICATION = [
        'GET/authenticationInformations', 'POST/authenticate', 'GET/authenticate/token',
        'POST/password', 'PUT/password', 'GET/passwordRules', 'GET/languages/{lang}', 'GET/commitInformation'
    ];


    public function getInformations(Request $request, Response $response)
    {
        $connection = ConfigurationModel::getConnection();
        $encryptKey = CoreConfigModel::getEncryptKey();
        $path = CoreConfigModel::getConfigPath();
        $hashedPath = md5($path);

        return $response->withJson([
            'connection'     => $connection,
            'changeKey'      => $encryptKey == 'Security Key Maarch Parapheur #2008',
            'instanceId'     => $hashedPath
        ]);
    }

    public static function authentication($authorizationHeaders = [])
    {
        $id = null;
        if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
            $login = strtolower($_SERVER['PHP_AUTH_USER']);
            if (AuthenticationModel::authentication(['login' => $login, 'password' => $_SERVER['PHP_AUTH_PW']])) {
                $user = UserModel::getByLogin(['select' => ['id'], 'login' => $login]);
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

    public function authenticate(Request $request, Response $response)
    {
        $body = $request->getParsedBody();

        $connection = ConfigurationModel::getConnection();
        if (in_array($connection, ['default', 'ldap'])) {
            if (!Validator::stringType()->notEmpty()->validate($body['login']) || !Validator::stringType()->notEmpty()->validate($body['password'])) {
                return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
            }
        }

        $login = strtolower($body['login']);
        
        if ($connection == 'ldap') {
            $ldapConfigurations = ConfigurationModel::getByIdentifier(['identifier' => 'ldapServer', 'select' => ['value']]);
            if (empty($ldapConfigurations)) {
                return $response->withStatus(400)->withJson(['errors' => 'Ldap configuration is missing']);
            }
            foreach ($ldapConfigurations as $ldapConfiguration) {
                $ldapConfiguration = json_decode($ldapConfiguration['value'], true);
                $uri = ($ldapConfiguration['ssl'] === true ? "LDAPS://{$ldapConfiguration['uri']}" : $ldapConfiguration['uri']);
                $ldap = @ldap_connect($uri);
                if ($ldap === false) {
                    $error = 'Ldap connect failed : uri is maybe wrong';
                    continue;
                }
                ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);
                ldap_set_option($ldap, LDAP_OPT_REFERRALS, 0);
                ldap_set_option($ldap, LDAP_OPT_NETWORK_TIMEOUT, 10);
                $ldapLogin = (!empty($ldapConfiguration['prefix']) ? $ldapConfiguration['prefix'] . '\\' . $body['login'] : $body['login']);
                $ldapLogin = (!empty($ldapConfiguration['suffix']) ? $ldapLogin . $ldapConfiguration['suffix'] : $ldapLogin);
                if (!empty($ldapConfiguration['baseDN'])) { //OpenLDAP
                    $search = @ldap_search($ldap, $ldapConfiguration['baseDN'], "(uid={$ldapLogin})", ['dn']);
                    if ($search === false) {
                        $error = 'Ldap search failed : baseDN is maybe wrong => ' . ldap_error($ldap);
                        continue;
                    }
                    $entries = ldap_get_entries($ldap, $search);
                    $ldapLogin = $entries[0]['dn'];
                }
                $authenticated = @ldap_bind($ldap, $ldapLogin, $body['password']);
                if (!$authenticated) {
                    $error = ldap_error($ldap);
                }
            }
            if (empty($authenticated) && !empty($error) && $error != 'Invalid credentials') {
                return $response->withStatus(400)->withJson(['errors' => $error]);
            }
        } elseif ($connection == 'x509') {
            if (!empty($_SERVER['SSL_CLIENT_CERT'])) {
                $x509Fingerprint = openssl_x509_fingerprint($_SERVER["SSL_CLIENT_CERT"]);
                $user = UserModel::get(['select' => ['login'], 'where' => ['x509_fingerprint = ?'], 'data' => [strtoupper(wordwrap($x509Fingerprint, 2, " ", true))]]);
                if (empty($user)) {
                    return $response->withStatus(401)->withJson(['errors' => 'Authentication unauthorized']);
                }
                $login = $user[0]['login'];
            } else {
                return $response->withStatus(401)->withJson(['errors' => 'No certificate detected']);
            }
            $authenticated = true;
        } elseif ($connection == 'kerberos') {
            if (!empty($_SERVER['REMOTE_USER']) && $_SERVER['AUTH_TYPE'] == 'Negotiate') {
                $login = strtolower($_SERVER['REMOTE_USER']);
            } else {
                return $response->withStatus(401)->withJson(['errors' => 'No identifier detected for kerberos']);
            }
            $authenticated = true;
        } else {
            $authenticated = AuthenticationModel::authentication(['login' => $login, 'password' => $body['password']]);
        }
        if (empty($authenticated)) {
            $user = UserModel::getByLogin(['login' => $login, 'select' => ['id']]);

            if (!empty($user)) {
                $handle = AuthenticationController::handleFailedAuthentication(['userId' => $user['id']]);
                if (!empty($handle['accountLocked'])) {
                    return $response->withStatus(401)->withJson(['errors' => 'Account Locked', 'date' => $handle['lockedDate']]);
                }
            }
            return $response->withStatus(401)->withJson(['errors' => 'Authentication Failed']);
        }

        $user = UserModel::getByLogin(['login' => $login, 'select' => ['id', '"isRest"', 'refresh_token']]);
        if (empty($user) || $user['isRest']) {
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

    public function getRefreshedToken(Request $request, Response $response)
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

    public function logout(Request $request, Response $response)
    {
        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'users',
            'objectId'      => $GLOBALS['id'],
            'type'          => 'LOGOUT',
            'message'       => '{userLogOut}'
        ]);

        $response->withStatus(204);
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
            'exp'        => time() + 60 * $sessionTime,
            'user'       => $user,
            'connection' => ConfigurationModel::getConnection()
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

    public static function getResetJWT($args = [])
    {
        $token = [
            'exp'   => time() + $args['expirationTime'],
            'user'  => [
                'id' => $args['id']
            ],
            'connection' => ConfigurationModel::getConnection()
        ];

        $jwt = JWT::encode($token, CoreConfigModel::getEncryptKey());

        return $jwt;
    }

    public static function sendAccountActivationNotification(array $args)
    {
        $resetToken = AuthenticationController::getResetJWT(['id' => $args['userId'], 'expirationTime' => 1209600]); // 14 days
        UserModel::update(['set' => ['reset_token' => $resetToken], 'where' => ['id = ?'], 'data' => [$args['userId']]]);

        $user = UserModel::getById(['select' => ['login'], 'id' => $args['userId']]);
        $lang = LanguageController::get(['lang' => 'fr']);

        $url = UrlController::getCoreUrl() . 'dist/update-password?token=' . $resetToken;
        EmailController::createEmail([
            'userId' => $args['userId'],
            'data'   => [
                'sender'     => 'Notification',
                'recipients' => [$args['userEmail']],
                'subject'    => $lang['notificationNewAccountSubject'],
                'body'       => $lang['notificationNewAccountBody'] . '<a href="' . $url . '">'.$url.'</a>' . $lang['notificationNewAccountId'] . ' ' . $user['login'] . $lang['notificationNewAccountFooter'],
                'isHtml'     => true
            ]
        ]);

        return true;
    }

    public static function isRouteAvailable(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId', 'currentRoute']);
        ValidatorModel::intVal($args, ['userId']);
        ValidatorModel::stringType($args, ['currentRoute']);

        $user = UserModel::getById(['select' => ['password_modification_date', '"isRest"'], 'id' => $args['userId']]);

        if (!in_array($args['currentRoute'], ['/passwordRules', '/users/{id}/password']) && empty($user['isRest'])) {
            $connectionConfiguration = ConfigurationModel::getByIdentifier(['identifier' => 'connection', 'select' => ['value']]);

            if ($connectionConfiguration[0]['value'] == '"default"') {
                $passwordRules = PasswordModel::getEnabledRules();
                if (!empty($passwordRules['renewal'])) {
                    $currentDate = new \DateTime();
                    $lastModificationDate = new \DateTime($user['password_modification_date']);
                    $lastModificationDate->add(new \DateInterval("P{$passwordRules['renewal']}D"));

                    if ($currentDate > $lastModificationDate) {
                        return ['isRouteAvailable' => false, 'errors' => 'Password expired : User must change his password'];
                    }
                }
            }
        }

        return ['isRouteAvailable' => true];
    }

    public static function handleFailedAuthentication(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId']);
        ValidatorModel::intVal($args, ['userId']);

        $passwordRules = PasswordModel::getEnabledRules();

        if (!empty($passwordRules['lockAttempts'])) {
            $user = UserModel::getById(['select' => ['failed_authentication', 'locked_until'], 'id' => $args['userId']]);
            $set = [];
            if (!empty($user['locked_until'])) {
                $currentDate = new \DateTime();
                $lockedUntil = new \DateTime($user['locked_until']);
                if ($lockedUntil < $currentDate) {
                    $set['locked_until'] = null;
                    $user['failed_authentication'] = 0;
                } else {
                    return ['accountLocked' => true, 'lockedDate' => $user['locked_until']];
                }
            }

            $set['failed_authentication'] = $user['failed_authentication'] + 1;
            UserModel::update([
                'set'       => $set,
                'where'     => ['id = ?'],
                'data'      => [$args['userId']]
            ]);

            if (!empty($user['failed_authentication']) && ($user['failed_authentication'] + 1) >= $passwordRules['lockAttempts'] && !empty($passwordRules['lockTime'])) {
                $lockedUntil = time() + 60 * $passwordRules['lockTime'];
                UserModel::update([
                    'set'       => ['locked_until'  => date('Y-m-d H:i:s', $lockedUntil)],
                    'where'     => ['id = ?'],
                    'data'      => [$args['userId']]
                ]);
                return ['accountLocked' => true, 'lockedDate' => date('Y-m-d H:i:s', $lockedUntil)];
            }
        }

        return true;
    }

    public function getGitCommitInformation(Request $request, Response $response)
    {
        if (!file_exists('.git/HEAD')) {
            return $response->withJson(['hash' => null]);
        }

        $head = file_get_contents('.git/HEAD');

        if ($head === false) {
            return $response->withJson(['hash' => null]);
        }
        preg_match('#^ref:(.+)$#', $head, $matches);
        $currentHead = trim($matches[1]);

        if (empty($currentHead) || !is_file('.git/' . $currentHead)) {
            return $response->withJson(['hash' => null]);
        }

        $hash = file_get_contents('.git/' . $currentHead);
        if ($hash === false) {
            return $response->withJson(['hash' => null]);
        }

        $hash = explode("\n", $hash)[0];

        return $response->withJson(['hash' => $hash]);
    }
}
