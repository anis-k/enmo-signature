<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief User Entity Model Abstract
 * @author dev@maarch.org
 */

namespace User\models;

use Entity\models\EntityModel;
use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

abstract class UserEntityModelAbstract
{
    public static function get(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select', 'where', 'data']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data']);

        $users = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users_entities'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data']
        ]);

        return $users;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['set', 'where', 'data']);
        ValidatorModel::arrayType($aArgs, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'users_entities',
            'set'   => $aArgs['set'],
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['where', 'data']);
        ValidatorModel::arrayType($aArgs, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'users_entities',
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }

    public static function getUsersWithoutEntities(array $aArgs)
    {
        ValidatorModel::arrayType($aArgs, ['select']);

        $excludedUsers = ['superadmin'];
        $aUsersEntities = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users', 'users_entities'],
            'left_join' => ['users.user_id = users_entities.user_id'],
            'where'     => ['users_entities IS NULL', 'users.user_id not in (?)', 'status != ?'],
            'data'      => [$excludedUsers, 'DEL']
        ]);

        return $aUsersEntities;
    }

    public static function addUserEntity(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'entityId', 'primaryEntity']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['entityId', 'role', 'primaryEntity']);

        $user = UserModel::getById(['id' => $aArgs['id'], 'select' => ['user_id']]);
        DatabaseModel::insert([
            'table'         => 'users_entities',
            'columnsValues' => [
                'user_id'           => $user['user_id'],
                'entity_id'         => $aArgs['entityId'],
                'user_role'         => $aArgs['role'],
                'primary_entity'    => $aArgs['primaryEntity']
            ]
        ]);

        return true;
    }

    public static function updateUserEntity(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'entityId']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['entityId', 'role']);

        $user = UserModel::getById(['id' => $aArgs['id'], 'select' => ['user_id']]);
        DatabaseModel::update([
            'table'     => 'users_entities',
            'set'       => [
                'user_role' => $aArgs['role']
            ],
            'where'     => ['user_id = ?', 'entity_id = ?'],
            'data'      => [$user['user_id'], $aArgs['entityId']]
        ]);

        return true;
    }

    public static function updateUserPrimaryEntity(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'entityId']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['entityId']);

        $user = UserModel::getById(['id' => $aArgs['id'], 'select' => ['user_id']]);
        $entities = EntityModel::getByUserId(['userId' => $user['user_id']]);
        foreach ($entities as $entity) {
            if ($entity['primary_entity'] == 'Y') {
                DatabaseModel::update([
                    'table'     => 'users_entities',
                    'set'       => [
                        'primary_entity'    => 'N'
                    ],
                    'where'     => ['user_id = ?', 'entity_id = ?'],
                    'data'      => [$user['user_id'], $entity['entity_id']]
                ]);
            }
        }

        DatabaseModel::update([
            'table'     => 'users_entities',
            'set'       => [
                'primary_entity'    => 'Y'
            ],
            'where'     => ['user_id = ?', 'entity_id = ?'],
            'data'      => [$user['user_id'], $aArgs['entityId']]
        ]);

        return true;
    }

    public static function reassignUserPrimaryEntity(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId']);
        ValidatorModel::stringType($aArgs, ['userId']);

        $entities = EntityModel::getByUserId(['userId' => $aArgs['userId']]);
        if (!empty($entities[0])) {
            DatabaseModel::update([
                'table'     => 'users_entities',
                'set'       => [
                    'primary_entity'    => 'Y'
                ],
                'where'     => ['user_id = ?', 'entity_id = ?'],
                'data'      => [$aArgs['userId'], $entities[0]['entity_id']]
            ]);
        }

        return true;
    }

    public static function deleteUserEntity(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'entityId']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::stringType($aArgs, ['entityId']);

        $user = UserModel::getById(['id' => $aArgs['id'], 'select' => ['user_id']]);
        DatabaseModel::delete([
            'table'     => 'users_entities',
            'where'     => ['entity_id = ?', 'user_id = ?'],
            'data'      => [$aArgs['entityId'], $user['user_id']]
        ]);

        return true;
    }

    public static function getUsersByEntities(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['entities']);
        ValidatorModel::arrayType($aArgs, ['entities', 'select']);

        $aUsers = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['users, users_entities'],
            'where'     => ['users.user_id = users_entities.user_id', 'users_entities.entity_id in (?)', 'status != ?'],
            'data'      => [$aArgs['entities'], 'DEL']
        ]);

        return $aUsers;
    }
}
