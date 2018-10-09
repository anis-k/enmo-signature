<?php
/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Contact Group Abstract Model
 * @author dev@maarch.org
 */

namespace Contact\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

abstract class ContactGroupModelAbstract
{
    public static function get(array $aArgs = [])
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);

        $aReturn = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['contacts_groups'],
            'where'     => $aArgs['where'],
            'data'      => $aArgs['data'],
            'order_by'  => $aArgs['orderBy']
        ]);

        return $aReturn;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aContactGroup = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['contacts_groups'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return $aContactGroup[0];
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['label', 'description', 'public', 'owner', 'entity_owner']);
        ValidatorModel::stringType($aArgs, ['label', 'description', 'public', 'entity_owner']);
        ValidatorModel::intVal($aArgs, ['owner']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'contacts_groups_id_seq']);
        DatabaseModel::insert([
            'table'         => 'contacts_groups',
            'columnsValues' => [
                'id'            => $nextSequenceId,
                'label'         => $aArgs['label'],
                'description'   => $aArgs['description'],
                'public'        => $aArgs['public'],
                'owner'         => $aArgs['owner'],
                'entity_owner'  => $aArgs['entity_owner'],
            ]
        ]);

        return $nextSequenceId;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'label', 'description', 'public']);
        ValidatorModel::stringType($aArgs, ['label', 'description', 'public']);
        ValidatorModel::intVal($aArgs, ['id']);

        DatabaseModel::update([
            'table'     => 'contacts_groups',
            'set'       => [
                'label'         => $aArgs['label'],
                'description'   => $aArgs['description'],
                'public'        => $aArgs['public']
            ],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);

        DatabaseModel::delete([
            'table' => 'contacts_groups',
            'where' => ['id = ?'],
            'data'  => [$aArgs['id']]
        ]);
        DatabaseModel::delete([
            'table' => 'contacts_groups_lists',
            'where' => ['contacts_groups_id = ?'],
            'data'  => [$aArgs['id']]
        ]);

        return true;
    }

    public static function getListById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $aList = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['contacts_groups_lists'],
            'where'     => ['contacts_groups_id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        return $aList;
    }

    public static function addContact(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'addressId']);
        ValidatorModel::intVal($aArgs, ['id', 'addressId']);

        DatabaseModel::insert([
            'table'         => 'contacts_groups_lists',
            'columnsValues' => [
                'contacts_groups_id'    => $aArgs['id'],
                'contact_addresses_id'  => $aArgs['addressId']
            ]
        ]);

        return true;
    }

    public static function deleteContact(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id', 'addressId']);
        ValidatorModel::intVal($aArgs, ['id', 'addressId']);

        DatabaseModel::delete([
            'table' => 'contacts_groups_lists',
            'where' => ['contacts_groups_id = ?', 'contact_addresses_id = ?'],
            'data'  => [$aArgs['id'], $aArgs['addressId']]
        ]);

        return true;
    }
}
