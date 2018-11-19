<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 */

/**
 * @brief Password Controller
 *
 * @author dev@maarch.org
 */

namespace SrcCore\controllers;

use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\PasswordModel;
use SrcCore\models\ValidatorModel;

class PasswordController
{
    public function get(Request $request, Response $response)
    {
        return $response->withJson(['rules' => PasswordModel::getRules()]);
    }

    public static function isPasswordValid(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['password']);
        ValidatorModel::stringType($aArgs, ['password']);

        $passwordRules = PasswordModel::getEnabledRules();

        if (!empty($passwordRules['minLength'])) {
            if (strlen($aArgs['password']) < $passwordRules['minLength']) {
                return false;
            }
        }
        if (!empty($passwordRules['complexityUpper'])) {
            if (!preg_match('/[A-Z]/', $aArgs['password'])) {
                return false;
            }
        }
        if (!empty($passwordRules['complexityNumber'])) {
            if (!preg_match('/[0-9]/', $aArgs['password'])) {
                return false;
            }
        }
        if (!empty($passwordRules['complexitySpecial'])) {
            if (!preg_match('/[^a-zA-Z0-9]/', $aArgs['password'])) {
                return false;
            }
        }

        return true;
    }
}
