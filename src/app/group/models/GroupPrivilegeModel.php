<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Group Privilege Model
* @author dev@maarch.org
*/

namespace Group\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class GroupPrivilegeModel
{
    public static function delete(array $args)
    {
        ValidatorModel::notEmpty($args, ['where', 'data']);
        ValidatorModel::arrayType($args, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'groups_privileges',
            'where' => $args['where'],
            'data'  => $args['data']
        ]);

        return true;
    }

    public static function getPrivileges(array $aArgs)
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($aArgs, ['limit']);

        $groupsPrivileges = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['groups_privileges'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'orderBy'   => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $groupsPrivileges;
    }

    public static function getPrivilegesByGroupId(array $aArgs)
    {
        ValidatorModel::arrayType($aArgs, ['select']);
        ValidatorModel::intVal($aArgs, ['groupId']);

        $groupsPrivileges = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['groups_privileges'],
            'where'     => ['group_id = ?'],
            'data'      => [$aArgs['groupId']]
        ]);

        return $groupsPrivileges;
    }

    public static function addPrivilege(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'privilegeId']);
        ValidatorModel::intVal($aArgs, ['groupId']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'groups_privileges_id_seq']);
        DatabaseModel::insert([
            'table'     => 'groups_privileges',
            'columnsValues'  => [
                'id'         => $nextSequenceId,
                'group_id'   => $aArgs['groupId'],
                'privilege'  => $aArgs['privilegeId']
            ]
        ]);

        return $nextSequenceId;
    }

    public static function deletePrivilege(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'privilegeId']);
        ValidatorModel::intVal($aArgs, ['groupId']);

        GroupPrivilegeModel::delete([
            'where' => ['group_id = ?', 'privilege = ?'],
            'data'  => [$aArgs['groupId'], $aArgs['privilegeId']]
        ]);

        return true;
    }
}
