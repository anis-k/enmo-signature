<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
 * @brief Attachment Model
 * @author dev@maarch.org
 */

namespace Attachment\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class AttachmentModel
{
    public static function get(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data']);

        $attachments = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['attachments'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data']
        ]);

        return $attachments;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select', 'id']);
        ValidatorModel::arrayType($aArgs, ['select']);
        ValidatorModel::intVal($aArgs, ['id']);

        $attachment = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['attachments'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        if (empty($attachment[0])) {
            return [];
        }

        return $attachment[0];
    }

    public static function getByDocumentId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select', 'documentId']);
        ValidatorModel::arrayType($aArgs, ['select']);
        ValidatorModel::intVal($aArgs, ['documentId']);

        $attachments = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['attachments'],
            'where'     => ['main_document_id = ?'],
            'data'      => [$aArgs['documentId']]
        ]);

        return $attachments;
    }

    public static function create(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['main_document_id', 'subject']);
        ValidatorModel::stringType($aArgs, ['reference', 'subject']);
        ValidatorModel::intVal($aArgs, ['main_document_id']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'attachments_id_seq']);

        DatabaseModel::insert([
            'table'         => 'attachments',
            'columnsValues' => [
                'id'                => $nextSequenceId,
                'main_document_id'  => $aArgs['main_document_id'],
                'reference'         => empty($aArgs['reference']) ? null : $aArgs['reference'],
                'subject'           => $aArgs['subject']
            ]
        ]);

        return $nextSequenceId;
    }
}
