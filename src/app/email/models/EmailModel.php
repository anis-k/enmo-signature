<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Email Model
* @author dev@maarch.org
*/

namespace Email\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class EmailModel
{
    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['id']);
        ValidatorModel::intVal($aArgs, ['id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $email = DatabaseModel::select([
            'select'    => empty($aArgs['select']) ? ['*'] : $aArgs['select'],
            'table'     => ['emails'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']],
        ]);

        if (empty($email[0])) {
            return [];
        }

        return $email[0];
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['user_id', 'sender', 'recipients', 'cc', 'cci', 'isHtml', 'status']);
        ValidatorModel::intVal($aArgs, ['user_id']);
        ValidatorModel::stringType($aArgs, ['sender', 'recipients', 'cc', 'cci', 'subject', 'body', 'document', 'isHtml', 'status']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'emails_id_seq']);

        DatabaseModel::insert([
            'table'         => 'emails',
            'columnsValues' => [
                'id'                        => $nextSequenceId,
                'user_id'                   => $aArgs['user_id'],
                'sender'                    => $aArgs['sender'],
                'recipients'                => $aArgs['recipients'],
                'cc'                        => $aArgs['cc'],
                'cci'                       => $aArgs['cci'],
                'subject'                   => $aArgs['subject'],
                'body'                      => $aArgs['body'],
                'document'                  => $aArgs['document'],
                'is_html'                   => $aArgs['isHtml'],
                'status'                    => $aArgs['status'],
                'creation_date'             => 'CURRENT_TIMESTAMP'
            ]
        ]);

        return $nextSequenceId;
    }

    public static function update(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['set', 'where', 'data']);
        ValidatorModel::arrayType($aArgs, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'emails',
            'set'   => $aArgs['set'],
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }
}
