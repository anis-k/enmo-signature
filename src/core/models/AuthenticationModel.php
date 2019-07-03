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
            'where'     => ['login = ?'],
            'data'      => [$args['login']]
        ]);

        if (empty($aReturn[0])) {
            return false;
        }

        return password_verify($args['password'], $aReturn[0]['password']);
    }

    public static function encrypt(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['password']);
        ValidatorModel::stringType($aArgs, ['password']);

        $encryptKey = CoreConfigModel::getEncryptKey();

        $cipher_method      = 'AES-128-CTR';
        $enc_iv             = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher_method));
        $cryptedPassword    = openssl_encrypt($aArgs['password'], $cipher_method, $encryptKey, 0, $enc_iv) . "::" . bin2hex($enc_iv);

        return $cryptedPassword;
    }

    public static function decrypt(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['cryptedPassword']);
        ValidatorModel::stringType($aArgs, ['cryptedPassword']);

        $encryptKey = CoreConfigModel::getEncryptKey();

        $cipher_method = 'AES-128-CTR';

        list($crypted_token, $enc_iv) = explode("::", $aArgs['cryptedPassword']);
        $password = openssl_decrypt($crypted_token, $cipher_method, $encryptKey, 0, hex2bin($enc_iv));

        return $password;
    }
}
