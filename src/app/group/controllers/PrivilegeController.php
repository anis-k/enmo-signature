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

use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\ValidatorModel;
use User\models\UserGroupModel;

class PrivilegeController
{
    const PRIVILEGES = [
        ['id' => 'manage_users',                'type' => 'admin', 'icon' => 'fa fa-user',          'route' => '/administration/users'],
        ['id' => 'manage_email_configuration',  'type' => 'admin', 'icon' => 'fa fa-paper-plane',   'route' => '/administration/configuration'],
        ['id' => 'manage_documents',            'type' => 'simple']
    ];

    public function getAdministrativePrivilegesByUser(Request $request, Response $response)
    {
        $groups = UserGroupModel::get(['select' => ['group_id'], 'where' => ['user_id = ?'], 'data' => [$GLOBALS['id']]]);

        $allGroups = array_column($groups, 'group_id');

        $administrativePrivileges = [];
        if (!empty($allGroups)) {
            $privileges = UserGroupModel::getPrivileges(['select' => ['privilege'], 'where' => ['group_id in (?)'], 'data' => [$allGroups]]);
            $privileges = array_column($privileges, 'privilege');

            if (!empty($privileges)) {
                foreach (PrivilegeController::PRIVILEGES as $value) {
                    if ($value['type'] == 'admin' && in_array($value['id'], $privileges)) {
                        $administrativePrivileges[] = $value;
                    }
                }
            }
        }

        return $response->withJson(['privileges' => $administrativePrivileges]);
    }

    public static function hasPrivilege(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId', 'privilege']);
        ValidatorModel::intVal($args, ['userId']);
        ValidatorModel::stringType($args, ['privilege']);

        $groups = UserGroupModel::get(['select' => ['group_id'], 'where' => ['user_id = ?'], 'data' => [$args['userId']]]);

        foreach ($groups as $group) {
            $privilege = UserGroupModel::getPrivileges(['select' => [1], 'where' => ['group_id = ?', 'privilege = ?'], 'data' => [$group['group_id'], $args['privilege']]]);
            if (!empty($privilege)) {
                return true;
            }
        }

        return false;
    }

    public static function hasAdmin(array $args)
    {
        ValidatorModel::notEmpty($args, ['userId']);
        ValidatorModel::intVal($args, ['userId']);

        $groups = UserGroupModel::get(['select' => ['group_id'], 'where' => ['user_id = ?'], 'data' => [$args['userId']]]);

        $allGroups = array_column($groups, 'group_id');

        if (!empty($allGroups)) {
            $privileges = UserGroupModel::getPrivileges(['select' => ['privilege'], 'where' => ['group_id in (?)'], 'data' => [$allGroups]]);
            $privileges = array_column($privileges, 'privilege');

            if (!empty($privileges)) {
                foreach (PrivilegeController::PRIVILEGES as $value) {
                    if ($value['type'] == 'admin' && in_array($value['id'], $privileges)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}