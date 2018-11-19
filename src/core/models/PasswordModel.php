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
}
