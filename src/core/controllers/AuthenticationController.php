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

use SrcCore\models\AuthenticationModel;

class AuthenticationController
{
    public static function authentication()
    {
        $userId = null;
        if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
            if (AuthenticationModel::authentication(['userId' => $_SERVER['PHP_AUTH_USER'], 'password' => $_SERVER['PHP_AUTH_PW']])) {
                $userId = $_SERVER['PHP_AUTH_USER'];
            }
        } else {
            $cookie = AuthenticationModel::getCookieAuth();
            if (!empty($cookie) && AuthenticationModel::cookieAuthentication($cookie)) {
                AuthenticationModel::setCookieAuth(['userId' => $cookie['userId']]);
                $userId = $cookie['userId'];
            }
        }

        return $userId;
    }
}
