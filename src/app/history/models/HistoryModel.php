<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief History Model
* @author dev@maarch.org
*/

namespace History\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class HistoryModel
{
    public static function get(array $args)
    {
        ValidatorModel::notEmpty($args, ['select']);
        ValidatorModel::arrayType($args, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($args, ['limit', 'offset']);

        $history = DatabaseModel::select([
            'select'    => $args['select'],
            'table'     => ['history'],
            'where'     => empty($args['where']) ? [] : $args['where'],
            'data'      => empty($args['data']) ? [] : $args['data'],
            'orderBy'   => empty($args['orderBy']) ? [] : $args['orderBy'],
            'offset'    => empty($args['offset']) ? 0 : $args['offset'],
            'limit'     => empty($args['limit']) ? 0 : $args['limit'],
        ]);

        return $history;
    }

    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['code', 'object_type', 'object_id', 'type', 'user_id', 'user', 'message', 'data', 'ip']);
        ValidatorModel::stringType($args, ['code', 'object_type', 'type', 'user', 'message', 'data', 'ip']);
        ValidatorModel::intVal($args, ['user_id']);

        DatabaseModel::insert([
            'table'         => 'history',
            'columnsValues' => [
                'code'          => $args['code'],
                'object_type'   => $args['object_type'],
                'object_id'     => $args['object_id'],
                'type'          => $args['type'],
                'user_id'       => $args['user_id'],
                '"user"'        => $args['user'],
                'date'          => 'CURRENT_TIMESTAMP',
                'message'       => $args['message'],
                'data'          => $args['data'],
                'ip'            => $args['ip']
            ]
        ]);

        return true;
    }
}
