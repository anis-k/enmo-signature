<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Signature Model
* @author dev@maarch.org
*/

namespace User\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class SignatureModel
{
    public static function get(array $aArgs)
    {
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($aArgs, ['limit', 'offset']);

        $aUsers = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['signatures'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'orderBy'   => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'offset'    => empty($aArgs['offset']) ? 0 : $aArgs['offset'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $aUsers;
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['userId', 'path', 'filename', 'fingerprint']);
        ValidatorModel::stringType($aArgs, ['path', 'filename', 'fingerprint', 'external_application']);
        ValidatorModel::intVal($aArgs, ['userId']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'signatures_id_seq']);

        DatabaseModel::insert([
            'table'         => 'signatures',
            'columnsValues' => [
                'id'                    => $nextSequenceId,
                'user_id'               => $aArgs['userId'],
                'path'                  => $aArgs['path'],
                'filename'              => $aArgs['filename'],
                'fingerprint'           => $aArgs['fingerprint'],
                'external_application'  => $aArgs['external_application']
            ]
        ]);

        return $nextSequenceId;
    }

    public static function update(array $args)
    {
        ValidatorModel::notEmpty($args, ['set', 'where', 'data']);
        ValidatorModel::arrayType($args, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'signatures',
            'set'   => $args['set'],
            'where' => $args['where'],
            'data'  => $args['data']
        ]);

        return true;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['where', 'data']);
        ValidatorModel::arrayType($aArgs, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'signatures',
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }
}
