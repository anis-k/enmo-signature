<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief User Group Model
* @author dev@maarch.org
*/

namespace User\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class UserGroupModel
{
    public static function get(array $aArgs)
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($aArgs, ['limit']);

        $usersGroups = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users_groups'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'orderBy'   => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $usersGroups;
    }

    public static function getByGroupId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select', 'orderBy']);
        ValidatorModel::intType($aArgs, ['id']);

        $usersGroups = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users_groups', 'users'],
            'where'     => ['group_id = ?'],
            'data'      => [$aArgs['id']],
            'left_join' => ['users.id = users_groups.user_id'],
            'orderBy'   => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $usersGroups;
    }

    public static function delete(array $args)
    {
        ValidatorModel::notEmpty($args, ['where', 'data']);
        ValidatorModel::arrayType($args, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'users_groups',
            'where' => $args['where'],
            'data'  => $args['data']
        ]);

        return true;
    }

    public static function hasGroup(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'userId']);
        ValidatorModel::intVal($aArgs, ['groupId', 'userId']);

        $usersGroups = DatabaseModel::select([
            'select' => [1],
            'table'  => ['users_groups'],
            'where'  => ['group_id = ?', 'user_id = ?'],
            'data'   => [$aArgs['groupId'], $aArgs['userId']]
        ]);

        if (!empty($usersGroups)) {
            return true;
        } else {
            return false;
        }
    }

    public static function addUser(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId', 'groupId']);
        ValidatorModel::intVal($aArgs, ['userId', 'groupId']);

        DatabaseModel::insert([
            'table'         => 'users_groups',
            'columnsValues' => [
                'user_id'  => $aArgs['userId'],
                'group_id' => $aArgs['groupId']
            ]
        ]);

        return true;
    }

    public static function removeUser(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId', 'groupId']);
        ValidatorModel::intVal($aArgs, ['userId', 'groupId']);

        DatabaseModel::delete([
            'table' => 'users_groups',
            'where' => ['user_id = ?', 'group_id = ?'],
            'data'  => [$aArgs['userId'], $aArgs['groupId']]
        ]);

        return true;
    }
}
