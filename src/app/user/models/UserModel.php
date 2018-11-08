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

class UserModel
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

    public static function getByLogin(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['login']);
        ValidatorModel::stringType($aArgs, ['login']);

        $aUser = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users'],
            'where'     => ['login = ?'],
            'data'      => [$aArgs['login']]
        ]);

        if (empty($aUser)) {
            return [];
        }

        return $aUser[0];
    }

    public static function getLabelledUserById(array $aArgs)
    {
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['login']);

        if (!empty($aArgs['id'])) {
            $rawUser = UserModel::getById(['id' => $aArgs['id'], 'select' => ['firstname', 'lastname']]);
        } elseif (!empty($aArgs['login'])) {
            $rawUser = UserModel::getByLogin(['login' => $aArgs['login'], 'select' => ['firstname', 'lastname']]);
        }

        $labelledUser = '';
        if (!empty($rawUser)) {
            $labelledUser = $rawUser['firstname']. ' ' .$rawUser['lastname'];
        }

        return $labelledUser;
    }

    public static function getSignatures(array $aArgs)
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($aArgs, ['limit']);

        $signatures = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['signatures'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'order_by'  => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $signatures;
    }

    public static function createSignature(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId', 'path', 'filename', 'fingerprint']);
        ValidatorModel::stringType($aArgs, ['path', 'filename', 'fingerprint']);
        ValidatorModel::intVal($aArgs, ['userId']);

        DatabaseModel::insert([
            'table'         => 'signatures',
            'columnsValues' => [
                'user_id'       => $aArgs['userId'],
                'path'          => $aArgs['path'],
                'filename'      => $aArgs['filename'],
                'fingerprint'   => $aArgs['fingerprint']
            ]
        ]);

        return true;
    }
}
