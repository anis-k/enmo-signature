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

use Group\controllers\PrivilegeController;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
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

    public function updateRules(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['privilege' => 'manage_password_rules', 'userId' => $GLOBALS['id']])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParsedBody();
        if (!Validator::arrayType()->notEmpty()->validate($data['rules'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body rules is empty or not an array']);
        }

        foreach ($data['rules'] as $rule) {
            $check = Validator::intVal()->validate($rule['value']);
            $check = $check && Validator::stringType()->validate($rule['label']);
            $check = $check && Validator::boolType()->validate($rule['enabled']);
            if (!$check) {
                continue;
            }

            $existingRule = PasswordModel::getRuleById(['id' => $rule['id'], 'select' => ['label']]);
            if (empty($existingRule) || $existingRule['label'] != $rule['label']) {
                continue;
            }

            $rule['enabled'] = empty($rule['enabled']) ? 'false' : 'true';
            PasswordModel::updateRuleById($rule);

            HistoryController::add([
                'code'          => 'OK',
                'objectType'    => 'password_rules',
                'objectId'      => $rule['id'],
                'type'          => 'MODIFICATION',
                'message'       => "{passwordRuleUpdated} : {$rule['label']}"
            ]);
        }

        return $response->withStatus(204);
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
