<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief User Model
 * @author dev@maarch.org
 */

namespace User\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;


abstract class UserModelAbstract
{
    public static function get(array $aArgs)
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($aArgs, ['limit']);

        $aUsers = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'order_by'  => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $aUsers;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $aUser = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        if (empty($aUser)) {
            return [];
        }

        return $aUser[0];
    }

    public static function getByUserId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $aUser = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users'],
            'where'     => ['user_id = ?'],
            'data'      => [$aArgs['userId']]
        ]);

        if (empty($aUser)) {
            return [];
        }

        return $aUser[0];
    }

    public static function getLabelledUserById(array $aArgs)
    {
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['userId']);

        if (!empty($aArgs['id'])) {
            $rawUser = UserModel::getById(['id' => $aArgs['id'], 'select' => ['firstname', 'lastname']]);
        } elseif (!empty($aArgs['userId'])) {
            $rawUser = UserModel::getByUserId(['userId' => $aArgs['userId'], 'select' => ['firstname', 'lastname']]);
        }

        $labelledUser = '';
        if (!empty($rawUser)) {
            $labelledUser = $rawUser['firstname']. ' ' .$rawUser['lastname'];
        }

        return $labelledUser;
    }
}
