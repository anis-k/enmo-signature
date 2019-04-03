<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Authentication Model
* @author dev@maarch.org
*/

namespace SrcCore\models;

use User\models\UserModel;

class AuthenticationModel
{
    public static function getPasswordHash($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function authentication(array $args)
    {
        ValidatorModel::notEmpty($args, ['login', 'password']);
        ValidatorModel::stringType($args, ['login', 'password']);

        $aReturn = DatabaseModel::select([
            'select'    => ['password'],
            'table'     => ['users'],
            'where'     => ['login = ?', 'enabled = ?'],
            'data'      => [$args['login'], 'true']
        ]);

        if (empty($aReturn[0])) {
            return false;
        }

        return password_verify($args['password'], $aReturn[0]['password']);
    }

    public static function getCookieAuth()
    {
        $rawCookie = $_COOKIE['maarchParapheurAuth'];
        if (empty($rawCookie)) {
            return [];
        }

        $cookieDecoded = base64_decode($rawCookie);
        $cookie = json_decode($cookieDecoded);

        return (array)$cookie;
    }

    public static function cookieAuthentication(array $args)
    {
        ValidatorModel::notEmpty($args, ['id', 'cookieKey']);
        ValidatorModel::stringType($args, ['cookieKey']);
        ValidatorModel::intVal($args, ['id']);

        $aReturn = DatabaseModel::select([
            'select'    => [1],
            'table'     => ['users'],
            'where'     => ['id = ?', 'cookie_key = ?', 'cookie_date > CURRENT_TIMESTAMP'],
            'data'      => [$args['id'], $args['cookieKey']]
        ]);

        if (empty($aReturn[0])) {
            return false;
        }

        return true;
    }

    public static function setCookieAuth(array $args)
    {
        ValidatorModel::notEmpty($args, ['id']);
        ValidatorModel::intVal($args, ['id']);

        $cookieTime = 1;

        $loadedXml = CoreConfigModel::getConfig();
        if ($loadedXml) {
            $cookieTime = (int)$loadedXml->config->CookieTime;
        }

        $user = UserModel::get([
            'select'    => ['cookie_key'],
            'where'     => ['id = ?', 'cookie_date > CURRENT_TIMESTAMP'],
            'data'      => [$args['id']]
        ]);
        if (empty($user[0]['cookie_key'])) {
            $cookieKey = AuthenticationModel::getPasswordHash($args['id']);
        } else {
            $cookieKey = $user[0]['cookie_key'];
        }

        $cookiePath = str_replace(['rest/index.php'], '', $_SERVER['SCRIPT_NAME']);
        $cookieTime = time() + 60 * $cookieTime;

        DatabaseModel::update([
            'table' => 'users',
            'set'   => [
                'cookie_key'    => $cookieKey,
                'cookie_date'   => date('Y-m-d H:i:s', $cookieTime),
            ],
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        $previousCookie = AuthenticationModel::getCookieAuth();
        $cookieData = json_encode([
            'id'        => $args['id'],
            'lang'      => empty($previousCookie['lang']) ? CoreConfigModel::getLanguage() : $previousCookie['lang'],
            'cookieKey' => $cookieKey
        ]);
        @setcookie('maarchParapheurAuth', base64_encode($cookieData), $cookieTime, $cookiePath, '', false, false);

        return true;
    }

    public static function revokeCookie(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId']);
        ValidatorModel::intVal($args, ['userId']);

        DatabaseModel::update([
            'table' => 'users',
            'set'   => [
                'cookie_key'    => null,
                'cookie_date'   => null,
            ],
            'where' => ['id = ?'],
            'data'  => [$args['userId']]
        ]);

        AuthenticationModel::setCookieAuth(['id' => $args['userId']]);

        return true;
    }

    public static function deleteCookieAuth()
    {
        $previousCookie = AuthenticationModel::getCookieAuth();

        if (!empty($previousCookie)) {
            $cookiePath = str_replace(['rest/index.php'], '', $_SERVER['SCRIPT_NAME']);
            @setcookie('maarchParapheurAuth', '', time() - 1, $cookiePath, '', false, true);
        }

        return true;
    }
}
