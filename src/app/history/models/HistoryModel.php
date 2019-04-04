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
    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['code', 'object_type', 'object_id', 'type', 'user_id', 'message', 'data', 'ip']);
        ValidatorModel::stringType($args, ['code', 'objectType', 'type', 'message', 'data', 'ip']);
        ValidatorModel::intVal($args, ['user_id']);

        DatabaseModel::insert([
            'table'         => 'history',
            'columnsValues' => [
                'code'          => $args['code'],
                'object_type'   => $args['object_type'],
                'object_id'     => $args['object_id'],
                'type'          => $args['type'],
                'user_id'       => $args['user_id'],
                'date'          => 'CURRENT_TIMESTAMP',
                'message'       => $args['message'],
                'data'          => $args['data'],
                'ip'            => $args['ip']
            ]
        ]);

        return true;
    }
}
