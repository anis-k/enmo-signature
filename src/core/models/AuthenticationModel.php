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
            'where'     => ['login = ?', 'status != ?'],
            'data'      => [$args['login'], 'DEL']
        ]);

        if (empty($aReturn[0])) {
            return false;
        }

        return password_verify($args['password'], $aReturn[0]['password']);
    }

    public static function getCookieAuth()
    {
        $rawCookie = $_COOKIE['maarchCourrierAuth'];
        if (empty($rawCookie)) {
            return [];
        }

        $cookieDecoded = base64_decode($rawCookie);
        $cookie = json_decode($cookieDecoded);

        return (array)$cookie;
    }

    public static function cookieAuthentication(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId', 'cookieKey']);
        ValidatorModel::stringType($args, ['userId', 'cookieKey']);

        $aReturn = DatabaseModel::select([
            'select'    => [1],
            'table'     => ['users'],
            'where'     => ['user_id = ?', 'cookie_key = ?', 'cookie_date > CURRENT_TIMESTAMP'],
            'data'      => [$args['userId'], $args['cookieKey']]
        ]);

        if (empty($aReturn[0])) {
            return false;
        }

        return true;
    }

    public static function setCookieAuth(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId']);
        ValidatorModel::stringType($args, ['userId']);

        $cookieTime = 0;

        $loadedXml = CoreConfigModel::getXmlLoaded(['path' => 'apps/maarch_entreprise/xml/config.xml']);
        if ($loadedXml) {
            $cookieTime = (string)$loadedXml->CONFIG->CookieTime;
        }

        $user = DatabaseModel::select([
            'select'    => ['cookie_key'],
            'table'     => ['users'],
            'where'     => ['user_id = ?', 'cookie_date > CURRENT_TIMESTAMP'],
            'data'      => [$args['userId']]
        ]);
        if (empty($user[0]['cookie_key'])) {
            $cookieKey = AuthenticationModel::getPasswordHash($args['userId']);
        } else {
            $cookieKey = $user[0]['cookie_key'];
        }

        $cookiePath = str_replace(['apps/maarch_entreprise/index.php', 'apps/maarch_entreprise/log.php', 'rest/index.php'], '', $_SERVER['SCRIPT_NAME']);
        $cookieTime = time() + 60 * $cookieTime;

        DatabaseModel::update([
            'table' => 'users',
            'set'   => [
                'cookie_key'    => $cookieKey,
                'cookie_date'   => date('Y-m-d H:i:s', $cookieTime),
            ],
            'where' => ['user_id = ?'],
            'data'  => [$args['userId']]
        ]);

        $cookieData = json_encode(['userId' => $args['userId'], 'cookieKey' => $cookieKey]);
        setcookie('maarchCourrierAuth', base64_encode($cookieData), $cookieTime, $cookiePath, '', false, true);

        return true;
    }

    public static function deleteCookieAuth()
    {
        $previousCookie = AuthenticationModel::getCookieAuth();

        if (!empty($previousCookie)) {
            $cookiePath = str_replace(['apps/maarch_entreprise/index.php', 'rest/index.php'], '', $_SERVER['SCRIPT_NAME']);
            setcookie('maarchCourrierAuth', '', time() - 1, $cookiePath, '', false, true);
        }

        return true;
    }
}
