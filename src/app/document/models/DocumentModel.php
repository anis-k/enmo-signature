<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Document Model
* @author dev@maarch.org
*/

namespace Document\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;


class DocumentModel
{
    public static function get(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select']);
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data']);
        ValidatorModel::intType($aArgs, ['limit', 'offset']);

        $aDocuments = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['main_documents'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'offset'    => empty($aArgs['offset']) ? 0 : $aArgs['offset'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $aDocuments;
    }

    public static function getById(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select', 'id']);
        ValidatorModel::arrayType($aArgs, ['select']);

        $document = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['main_documents'],
            'where'     => ['id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        if (empty($document[0])) {
            return [];
        }

        return $document[0];
    }

    public static function getByUserId(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['select', 'userId']);
        ValidatorModel::intVal($aArgs, ['userId']);
        ValidatorModel::arrayType($aArgs, ['select']);
        ValidatorModel::intType($aArgs, ['limit', 'offset']);

        $aDocuments = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['main_documents'],
            'where'     => ['processing_user = ?'],
            'data'      => [$aArgs['userId']],
            'offset'    => empty($aArgs['offset']) ? 0 : $aArgs['offset'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit']
        ]);

        return $aDocuments;
    }

    public static function delete(array $aArgs)
    {
        ValidatorModel::notEmpty($aArgs, ['where', 'data']);
        ValidatorModel::arrayType($aArgs, ['where', 'data']);

        DatabaseModel::delete([
            'table' => 'main_documents',
            'where' => $aArgs['where'],
            'data'  => $aArgs['data']
        ]);

        return true;
    }
}
