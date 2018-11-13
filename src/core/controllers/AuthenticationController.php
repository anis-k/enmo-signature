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

use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\AuthenticationModel;

class AuthenticationController
{
    public static function authentication()
    {
        $login = null;
        if (!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])) {
            if (AuthenticationModel::authentication(['login' => $_SERVER['PHP_AUTH_USER'], 'password' => $_SERVER['PHP_AUTH_PW']])) {
                $login = $_SERVER['PHP_AUTH_USER'];
            }
        } else {
            $cookie = AuthenticationModel::getCookieAuth();
            if (!empty($cookie) && AuthenticationModel::cookieAuthentication($cookie)) {
                AuthenticationModel::setCookieAuth(['login' => $cookie['login']]);
                $login = $cookie['login'];
            }
        }

        return $login;
    }

    public static function log(Request $request, Response $response)
    {
        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['login']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['password']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        if (!AuthenticationModel::authentication(['login' => $data['login'], 'password' => $data['password']])) {
            return $response->withStatus(401)->withJson(['errors' => 'Authentication Failed']);
        }

        AuthenticationModel::setCookieAuth(['login' => $data['login']]);

        return $response->withJson(['success' => 'success']);
    }
}
