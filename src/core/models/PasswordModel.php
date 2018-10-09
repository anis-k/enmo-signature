<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Password Model
* @author dev@maarch.org
*/

namespace SrcCore\models;

class PasswordModel
{
    public static function getRules(array $aArgs = [])
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data']);

        $aRules = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['password_rules'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data'],
        ]);

        return $aRules;
    }

    public static function getEnabledRules()
    {
        $aRules = DatabaseModel::select([
            'select'    => ['label', 'value'],
            'table'     => ['password_rules'],
            'where'     => ['enabled = ?'],
            'data'      => [true],
        ]);

        $formattedRules = [];
        foreach ($aRules as $rule) {
            if (strpos($rule['label'], 'complexity') === false) {
                $formattedRules[$rule['label']] = $rule['value'];
            } else {
                $formattedRules[$rule['label']] = true;
            }
        }

        return $formattedRules;
    }

    public static function getRuleById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $rules = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['password_rules'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        if (empty($rules[0])) {
            return [];
        }

        return $rules[0];
    }

    public static function updateRuleById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id', 'value']);
        ValidatorModel::stringType($aArgs, ['enabled']);
        
        DatabaseModel::update([
            'table'     => 'password_rules',
            'set'       => [
                '"value"'   => $aArgs['value'],
                'enabled'   => $aArgs['enabled'],
            ],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return true;
    }

    public static function isPasswordHistoryValid(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['password', 'userSerialId']);
        ValidatorModel::stringType($aArgs, ['password']);
        ValidatorModel::intVal($aArgs, ['userSerialId']);

        $passwordRules = PasswordModel::getEnabledRules();

        if (!empty($passwordRules['historyLastUse'])) {
            $passwordHistory = DatabaseModel::select([
                'select'    => ['password'],
                'table'     => ['password_history'],
                'where'     => ['user_serial_id = ?'],
                'data'      => [$aArgs['userSerialId']],
                'order_by'  => ['id DESC'],
                'limit'     => $passwordRules['historyLastUse']
            ]);

            foreach ($passwordHistory as $value) {
                if (password_verify($aArgs['password'], $value['password'])) {
                    return false;
                }
            }
        }

        return true;
    }

    public static function setHistoryPassword(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['password', 'userSerialId']);
        ValidatorModel::stringType($aArgs, ['password']);
        ValidatorModel::intVal($aArgs, ['userSerialId']);

        $passwordHistory = DatabaseModel::select([
            'select'    => ['id'],
            'table'     => ['password_history'],
            'where'     => ['user_serial_id = ?'],
            'data'      => [$aArgs['userSerialId']],
            'order_by'  => ['id DESC']
        ]);
        
        if (count($passwordHistory) >= 10) {
            DatabaseModel::delete([
                'table'     => 'password_history',
                'where'     => ['id < ?', 'user_serial_id = ?'],
                'data'      => [$passwordHistory[8]['id'], $aArgs['userSerialId']]
            ]);
        }

        DatabaseModel::insert([
            'table'     => 'password_history',
            'columnsValues'     => [
                'user_serial_id'    => $aArgs['userSerialId'],
                'password'          => AuthenticationModel::getPasswordHash($aArgs['password'])
            ],
        ]);

        return true;
    }
}
