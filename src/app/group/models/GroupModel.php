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

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class GroupModel
{
    public static function get(array $aArgs = [])
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($aArgs, ['limit']);

        $groups = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['groups'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'orderBy'   => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $groups;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $group = GroupModel::get([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        if (!empty($group)) {
            return $group[0];
        }

        return [];
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['label']);
        ValidatorModel::stringType($aArgs, ['label']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'groups_id_seq']);
        DatabaseModel::insert([
            'table'         => 'groups',
            'columnsValues' => [
                'id'    => $nextSequenceId,
                'label' => $aArgs['label']
            ]
        ]);

        return $nextSequenceId;
    }

    public static function update(array $args)
    {
        ValidatorModel::notEmpty($args, ['set', 'where', 'data']);
        ValidatorModel::arrayType($args, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'groups',
            'set'   => $args['set'],
            'where' => $args['where'],
            'data'  => $args['data']
        ]);

        return true;
    }

    public static function delete(array $args)
    {
        ValidatorModel::notEmpty($args, ['where', 'data']);
        ValidatorModel::arrayType($args, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'groups',
            'where' => $args['where'],
            'data'  => $args['data']
        ]);

        return true;
    }
}
