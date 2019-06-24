<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Configuration Model
* @author dev@maarch.org
*/

namespace Configuration\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class ConfigurationModel
{
    public static function getById(array $args)
    {
        ValidatorModel::notEmpty($args, ['id']);
        ValidatorModel::intVal($args, ['id']);
        ValidatorModel::arrayType($args, ['select']);

        $configuration = DatabaseModel::select([
            'select'    => empty($args['select']) ? ['*'] : $args['select'],
            'table'     => ['configurations'],
            'where'     => ['id = ?'],
            'data'      => [$args['id']],
        ]);

        if (empty($configuration[0])) {
            return [];
        }

        return $configuration[0];
    }

    public static function getByIdentifier(array $args)
    {
        ValidatorModel::notEmpty($args, ['identifier']);
        ValidatorModel::stringType($args, ['identifier']);
        ValidatorModel::arrayType($args, ['select']);

        $configurations = DatabaseModel::select([
            'select'    => empty($args['select']) ? ['*'] : $args['select'],
            'table'     => ['configurations'],
            'where'     => ['identifier = ?'],
            'data'      => [$args['identifier']],
        ]);

        return $configurations;
    }

    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['identifier', 'label', 'value']);
        ValidatorModel::stringType($args, ['identifier', 'label', 'value']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'users_id_seq']);

        DatabaseModel::insert([
            'table'         => 'configurations',
            'columnsValues' => [
                'id'            => $nextSequenceId,
                'identifier'    => $args['identifier'],
                'label'         => $args['label'],
                'value'         => $args['value']
            ]
        ]);

        return $nextSequenceId;
    }

    public static function update(array $args)
    {
        ValidatorModel::notEmpty($args, ['set', 'where', 'data']);
        ValidatorModel::arrayType($args, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'configurations',
            'set'   => $args['set'],
            'where' => $args['where'],
            'data'  => $args['data']
        ]);

        return true;
    }

    public static function delete(array $args)
    {
        ValidatorModel::notEmpty($args, ['id']);
        ValidatorModel::intVal($args, ['id']);

        DatabaseModel::delete([
            'table' => 'configurations',
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);

        return true;
    }

    public static function getConnection()
    {
        $configuration = DatabaseModel::select([
            'select'    => ['value'],
            'table'     => ['configurations'],
            'where'     => ['identifier = ?'],
            'data'      => ['connection']
        ]);

        if (empty($configuration[0])) {
            return 'default';
        }

        $connection = json_decode($configuration[0]['value']);

        return $connection;
    }
}
