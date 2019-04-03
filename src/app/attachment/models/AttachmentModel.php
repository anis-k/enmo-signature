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

    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['main_document_id', 'title']);
        ValidatorModel::stringType($args, ['title', 'reference']);
        ValidatorModel::intVal($args, ['main_document_id']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'attachments_id_seq']);

        DatabaseModel::insert([
            'table'         => 'attachments',
            'columnsValues' => [
                'id'                => $nextSequenceId,
                'main_document_id'  => $args['main_document_id'],
                'title'             => $args['title'],
                'reference'         => $args['reference']
            ]
        ]);

        return $nextSequenceId;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['where', 'data']);
        ValidatorModel::arrayType($aArgs, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'attachments',
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }
}
