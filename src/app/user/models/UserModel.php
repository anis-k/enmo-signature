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

use SrcCore\models\AuthenticationModel;
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
            'orderBy'   => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $aUsers;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

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
        ValidatorModel::arrayType($aArgs, ['select']);

        $user = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users'],
            'where'     => ['login = ?'],
            'data'      => [$aArgs['login']]
        ]);

        if (empty($user)) {
            return [];
        }

        return $user[0];
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['user']);
        ValidatorModel::notEmpty($aArgs['user'], ['email', 'firstname', 'lastname']);
        ValidatorModel::stringType($aArgs['user'], ['email', 'firstname', 'lastname', 'picture', 'mode']);

        DatabaseModel::insert([
            'table'         => 'users',
            'columnsValues' => [
                'email'                               => $aArgs['user']['email'],
                'password'                            => AuthenticationModel::getPasswordHash('maarch'),
                'firstname'                           => $aArgs['user']['firstname'],
                'lastname'                            => $aArgs['user']['lastname'],
                'picture'                             => $aArgs['user']['picture'],
                'mode'                                => $aArgs['user']['mode'],
                'password_modification_date'          => 'CURRENT_TIMESTAMP'
            ]
        ]);

        return true;
    }

    public static function getByEmail(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['email']);
        ValidatorModel::stringType($aArgs, ['email']);

        $aUser = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users'],
            'where'     => ['email = ?'],
            'data'      => [$aArgs['email']]
        ]);

        if (empty($aUser)) {
            return [];
        }

        return $aUser[0];
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'firstname', 'lastname', 'preferences']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['firstname', 'lastname', 'picture', 'preferences']);

        $set = [
            'firstname'     => $aArgs['firstname'],
            'lastname'      => $aArgs['lastname'],
            'preferences'   => $aArgs['preferences']
        ];
        if (!empty($aArgs['picture'])) {
            $set['picture'] = $aArgs['picture'];
        }
        DatabaseModel::update([
            'table'     => 'users',
            'set'       => $set,
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return true;
    }

    public static function updatePassword(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'password']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['password']);

        DatabaseModel::update([
            'table'     => 'users',
            'set'       => [
                'password'                      => AuthenticationModel::getPasswordHash($aArgs['password']),
                'password_modification_date'    => 'CURRENT_TIMESTAMP'
            ],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return true;
    }

    public static function getLabelledUserById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $rawUser = UserModel::getById(['id' => $aArgs['id'], 'select' => ['firstname', 'lastname']]);

        $labelledUser = '';
        if (!empty($rawUser)) {
            $labelledUser = $rawUser['firstname']. ' ' .$rawUser['lastname'];
        }

        return $labelledUser;
    }

    public static function getSignatures(array $aArgs)
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intVal($aArgs, ['limit', 'offset']);

        $signatures = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['signatures'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'orderBy'   => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'offset'    => empty($aArgs['offset']) ? 0 : $aArgs['offset'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $signatures;
    }

    public static function createSignature(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId', 'path', 'filename', 'fingerprint']);
        ValidatorModel::stringType($aArgs, ['path', 'filename', 'fingerprint']);
        ValidatorModel::intVal($aArgs, ['userId']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'signatures_id_seq']);

        DatabaseModel::insert([
            'table'         => 'signatures',
            'columnsValues' => [
                'id'            => $nextSequenceId,
                'user_id'       => $aArgs['userId'],
                'path'          => $aArgs['path'],
                'filename'      => $aArgs['filename'],
                'fingerprint'   => $aArgs['fingerprint']
            ]
        ]);

        return $nextSequenceId;
    }

    public static function deleteSignature(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['where', 'data']);
        ValidatorModel::arrayType($aArgs, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'signatures',
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }
}
