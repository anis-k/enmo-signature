<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Group Model
* @author dev@maarch.org
*/

namespace Group\models;

use Group\controllers\GroupController;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;

abstract class GroupModelAbstract
{
    public static function get(array $aArgs = [])
    {
        $aGroups = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['usergroups'],
            'where'     => ['enabled = ?'],
            'data'      => ['Y'],
            'order_by'  => ['group_desc']
        ]);

        return $aGroups;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        $aGroups = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['usergroups'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return $aGroups[0];
    }

    public static function getByGroupId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId']);
        ValidatorModel::stringType($aArgs, ['groupId']);

        $aGroups = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['usergroups'],
            'where'     => ['group_id = ?'],
            'data'      => [$aArgs['groupId']]
        ]);

        return $aGroups[0];
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'description', 'clause']);
        ValidatorModel::stringType($aArgs, ['groupId', 'description', 'clause', 'comment']);

        DatabaseModel::insert([
            'table'     => 'usergroups',
            'columnsValues'     => [
                'group_id'      => $aArgs['groupId'],
                'group_desc'    => $aArgs['description']
            ]
        ]);

        DatabaseModel::insert([
            'table'     => 'security',
            'columnsValues'         => [
                'group_id'          => $aArgs['groupId'],
                'coll_id'           => 'letterbox_coll',
                'where_clause'      => $aArgs['clause'],
                'maarch_comment'    => $aArgs['comment'],
            ]
        ]);

        return true;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'description', 'clause']);
        ValidatorModel::stringType($aArgs, ['id', 'description', 'clause', 'comment']);

        DatabaseModel::update([
            'table'     => 'usergroups',
            'set'       => [
                'group_desc'    => $aArgs['description']
            ],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        $group = GroupModel::getById(['id' => $aArgs['id'], 'select' => ['group_id']]);

        DatabaseModel::update([
            'table'     => 'security',
            'set'       => [
                'where_clause'      => $aArgs['clause'],
                'maarch_comment'    => $aArgs['comment'],
            ],
            'where'     => ['group_id = ?'],
            'data'      => [$group['group_id']]
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['id']);

        $group = GroupModel::getById(['id' => $aArgs['id'], 'select' => ['group_id']]);

        DatabaseModel::delete([
            'table'     => 'usergroups',
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table'     => 'usergroup_content',
            'where'     => ['group_id = ?'],
            'data'      => [$group['group_id']]
        ]);
        DatabaseModel::delete([
            'table'     => 'usergroups_reports',
            'where'     => ['group_id = ?'],
            'data'      => [$group['group_id']]
        ]);
        DatabaseModel::delete([
            'table'     => 'usergroups_services',
            'where'     => ['group_id = ?'],
            'data'      => [$group['group_id']]
        ]);
        DatabaseModel::delete([
            'table'     => 'security',
            'where'     => ['group_id = ?'],
            'data'      => [$group['group_id']]
        ]);
        DatabaseModel::delete([
            'table'     => 'groupbasket',
            'where'     => ['group_id = ?'],
            'data'      => [$group['group_id']]
        ]);
        DatabaseModel::delete([
            'table'     => 'groupbasket_redirect',
            'where'     => ['group_id = ?'],
            'data'      => [$group['group_id']]
        ]);
        DatabaseModel::delete([
            'table'     => 'groupbasket_status',
            'where'     => ['group_id = ?'],
            'data'      => [$group['group_id']]
        ]);
        DatabaseModel::delete([
            'table' => 'users_baskets_preferences',
            'where' => ['group_serial_id = ?'],
            'data'  => [$aArgs['id']]
        ]);

        return true;
    }

    public static function getUsersByGroupId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId']);
        ValidatorModel::stringType($aArgs, ['groupId']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aUsers = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['usergroup_content, users'],
            'where'     => ['group_id = ?', 'usergroup_content.user_id = users.user_id', 'users.status != ?'],
            'data'      => [$aArgs['groupId'], 'DEL']
        ]);

        return $aUsers;
    }

    public static function getAvailableGroupsByUserId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $rawUserGroups = UserModel::getGroupsByUserId(['userId' => $aArgs['userId']]);

        $userGroups = [];
        foreach ($rawUserGroups as $value) {
            $userGroups[] = $value['group_id'];
        }

        $allGroups = GroupModel::get(['select' => ['group_id', 'group_desc']]);

        foreach ($allGroups as $key => $value) {
            if (in_array($value['group_id'], $userGroups)) {
                $allGroups[$key]['disabled'] = true;
            } else {
                $allGroups[$key]['disabled'] = false;
            }
        }

        return $allGroups;
    }

    public static function getSecurityByGroupId(array $aArgs = [])
    {
        ValidatorModel::notEmpty($aArgs, ['groupId']);
        ValidatorModel::stringType($aArgs, ['groupId']);

        $aData = DatabaseModel::select([
            'select'    => ['where_clause', 'maarch_comment'],
            'table'     => ['security'],
            'where'     => ['group_id = ?'],
            'data'      => [$aArgs['groupId']]
        ]);

        return $aData[0];
    }

    public static function getServiceById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'serviceId']);
        ValidatorModel::stringType($aArgs, ['groupId', 'serviceId']);

        $service = DatabaseModel::select([
            'select'    => ['group_id', 'service_id'],
            'table'     => ['usergroups_services'],
            'where'     => ['group_id = ?', 'service_id = ?'],
            'data'      => [$aArgs['groupId'], $aArgs['serviceId']]
        ]);

        return $service;
    }

    public static function getServicesById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId']);
        ValidatorModel::stringType($aArgs, ['groupId']);

        $aServices = DatabaseModel::select([
            'select'    => ['service_id'],
            'table'     => ['usergroups_services'],
            'where'     => ['group_id = ?'],
            'data'      => [$aArgs['groupId']]
        ]);

        return $aServices;
    }

    public static function getAllServicesByGroupId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId']);
        ValidatorModel::stringType($aArgs, ['groupId']);

        $rawCheckedServices = GroupModel::getServicesById(['groupId' => $aArgs['groupId']]);
        $checkedServices = [];
        foreach ($rawCheckedServices as $value) {
            $checkedServices[] = $value['service_id'];
        }

        $allServices = ServiceModel::getServicesByXML();

        $services = [];
        foreach ($allServices as $key => $value) {
            $menu = [];
            $use = [];
            foreach ($value as $value2) {
                if (!$value2['system_service']) {
                    if (in_array($value2['id'], $checkedServices)) {
                        $value2['checked'] = true;
                    } else {
                        $value2['checked'] = false;
                    }
                    $value2['location'] = $key;
                    if ($value2['servicetype'] == 'menu') {
                        $menu[] = $value2;
                    } elseif ($value2['servicetype'] == 'admin') {
                        $services['administration'][] = $value2;
                    } elseif ($value2['servicetype'] == 'use') {
                        $use[] = $value2;
                    }
                }
            }
            if (!empty($menu)) {
                $services['menu'][] = $menu;
            }
            if (!empty($use)) {
                $services['use'][] = $use;
            }
        }

        foreach ($services['menu'] as $key => $menu) {
            $services['menu'][$key] = GroupController::arraySort(['data' => $menu, 'on' => 'name']);
        }
        foreach ($services['use'] as $key => $use) {
            $services['use'][$key] = GroupController::arraySort(['data' => $use, 'on' => 'name']);
        }
        $services['administration'] = GroupController::arraySort(['data' => $services['administration'], 'on' => 'name']);

        return $services;
    }

    public static function updateServiceById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'serviceId']);
        ValidatorModel::stringType($aArgs, ['groupId', 'serviceId']);
        ValidatorModel::boolType($aArgs, ['checked']);

        if ($aArgs['checked']) {
            DatabaseModel::insert([
                'table'         => 'usergroups_services',
                'columnsValues' => [
                    'group_id'      => $aArgs['groupId'],
                    'service_id'    => $aArgs['serviceId']
                ]
            ]);
        } else {
            DatabaseModel::delete([
                'table'     => 'usergroups_services',
                'where'     => ['group_id = ?', 'service_id = ?'],
                'data'      => [$aArgs['groupId'], $aArgs['serviceId']]
            ]);
        }

        return true;
    }

    public static function reassignUsers(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['groupId', 'newGroupId']);
        ValidatorModel::stringType($aArgs, ['groupId', 'newGroupId']);
        ValidatorModel::arrayType($aArgs, ['ignoredUsers']);

        $where = ['group_id = ?'];
        $data = [$aArgs['groupId']];
        if (!empty($aArgs['ignoredUsers'])) {
            $where[] = 'user_id NOT IN (?)';
            $data[] = $aArgs['ignoredUsers'];
        }

        DatabaseModel::update([
            'table'     => 'usergroup_content',
            'set'       => [
                'group_id'  => $aArgs['newGroupId']
            ],
            'where'     => $where,
            'data'      => $data
        ]);

        return true;
    }
}
