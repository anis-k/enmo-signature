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
        ValidatorModel::notEmpty($args, ['email', 'password']);
        ValidatorModel::stringType($args, ['email', 'password']);

        $aReturn = DatabaseModel::select([
            'select'    => ['password'],
            'table'     => ['users'],
            'where'     => ['email = ?', 'enabled = ?'],
            'data'      => [$args['email'], 'true']
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
        ValidatorModel::notEmpty($args, ['email', 'cookieKey']);
        ValidatorModel::stringType($args, ['email', 'cookieKey']);

        $aReturn = DatabaseModel::select([
            'select'    => [1],
            'table'     => ['users'],
            'where'     => ['email = ?', 'cookie_key = ?', 'cookie_date > CURRENT_TIMESTAMP'],
            'data'      => [$args['email'], $args['cookieKey']]
        ]);

        if (empty($aReturn[0])) {
            return false;
        }

        return true;
    }

    public static function setCookieAuth(array $args)
    {
        ValidatorModel::notEmpty($args, ['email']);
        ValidatorModel::stringType($args, ['email']);

        $cookieTime = 1;

        $loadedXml = CoreConfigModel::getConfig();
        if ($loadedXml) {
            $cookieTime = (int)$loadedXml->config->CookieTime;
        }

        $user = UserModel::get([
            'select'    => ['cookie_key'],
            'where'     => ['email = ?', 'cookie_date > CURRENT_TIMESTAMP'],
            'data'      => [$args['email']]
        ]);
        if (empty($user[0]['cookie_key'])) {
            $cookieKey = AuthenticationModel::getPasswordHash($args['email']);
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
            'where' => ['email = ?'],
            'data'  => [$args['email']]
        ]);

        $cookieData = json_encode(['email' => $args['email'], 'cookieKey' => $cookieKey]);
        setcookie('maarchParapheurAuth', base64_encode($cookieData), $cookieTime, $cookiePath, '', false, true);

        return true;
    }

    public static function deleteCookieAuth()
    {
        $previousCookie = AuthenticationModel::getCookieAuth();

        if (!empty($previousCookie)) {
            $cookiePath = str_replace(['rest/index.php'], '', $_SERVER['SCRIPT_NAME']);
            setcookie('maarchParapheurAuth', '', time() - 1, $cookiePath, '', false, true);
        }

        return true;
    }
}
