<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Status Model
* @author dev@maarch.org
*/

namespace Status\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class StatusModel
{
    public static function get(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data']);

        $statuses = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['status'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data']
        ]);

        return $statuses;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intType($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $status = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['status'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        if (empty($status[0])) {
            return [];
        }

        return $status[0];
    }

    public static function getByReference(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['reference']);
        ValidatorModel::stringType($aArgs, ['reference']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $status = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['status'],
            'where'     => ['reference = ?'],
            'data'      => [$aArgs['reference']]
        ]);

        if (empty($status[0])) {
            return [];
        }

        return $status[0];
    }
}
