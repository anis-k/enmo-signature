<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Privilege Controller
* @author dev@maarch.org
*/

namespace Group\controllers;

use SrcCore\models\ValidatorModel;
use User\models\UserGroupModel;
use Group\models\GroupPrivilegeModel;

class PrivilegeController
{
    const PRIVILEGES = [
        ['id' => 'manage_users',                'type' => 'admin', 'icon' => 'person-sharp',  'route' => '/administration/users'],
        ['id' => 'manage_groups',               'type' => 'admin', 'icon' => 'people-sharp',  'route' => '/administration/groups'],
        ['id' => 'manage_connections',          'type' => 'admin', 'icon' => 'server-sharp',  'route' => '/administration/connections'],
        ['id' => 'manage_email_configuration',  'type' => 'admin', 'icon' => 'paper-plane',   'route' => '/administration/emailConfiguration'],
        ['id' => 'manage_password_rules',       'type' => 'admin', 'icon' => 'lock-closed',    'route' => '/administration/passwordRules'],
        ['id' => 'manage_documents',            'type' => 'simple'],
        ['id' => 'indexation',                  'type' => 'simple']
    ];

    public static function getPrivilegesByUserId(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId', 'type']);
        ValidatorModel::intVal($args, ['userId']);
        ValidatorModel::stringType($args, ['type']);

        $groups = UserGroupModel::get(['select' => ['group_id'], 'where' => ['user_id = ?'], 'data' => [$args['userId']]]);

        $allGroups = array_column($groups, 'group_id');

        $administrativePrivileges = [];
        if (!empty($allGroups)) {
            $privileges = GroupPrivilegeModel::getPrivileges(['select' => ['privilege'], 'where' => ['group_id in (?)'], 'data' => [$allGroups]]);
            $privileges = array_column($privileges, 'privilege');

            if (!empty($privileges)) {
                foreach (PrivilegeController::PRIVILEGES as $value) {
                    if ($value['type'] == $args['type'] && in_array($value['id'], $privileges)) {
                        $administrativePrivileges[] = $value;
                    }
                }
            }
        }

        return $administrativePrivileges;
    }

    public static function hasPrivilege(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId', 'privilege']);
        ValidatorModel::intVal($args, ['userId']);
        ValidatorModel::stringType($args, ['privilege']);

        $groups = UserGroupModel::get(['select' => ['group_id'], 'where' => ['user_id = ?'], 'data' => [$args['userId']]]);

        foreach ($groups as $group) {
            $privilege = GroupPrivilegeModel::getPrivileges(['select' => [1], 'where' => ['group_id = ?', 'privilege = ?'], 'data' => [$group['group_id'], $args['privilege']]]);
            if (!empty($privilege)) {
                return true;
            }
        }

        return false;
    }
}
