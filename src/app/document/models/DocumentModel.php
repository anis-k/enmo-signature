<?php

/**
* Copyright Maarch since 2008 under license.
* See LICENSE.txt file at the root folder for more details.
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
        ValidatorModel::arrayType($aArgs, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($aArgs, ['limit', 'offset']);

        $aDocuments = DatabaseModel::select([
            'select'    => $aArgs['select'],
            'table'     => ['main_documents'],
            'where'     => empty($aArgs['where']) ? [] : $aArgs['where'],
            'data'      => empty($aArgs['data']) ? [] : $aArgs['data'],
            'orderBy'   => empty($aArgs['orderBy']) ? [] : $aArgs['orderBy'],
            'offset'    => empty($aArgs['offset']) ? 0 : $aArgs['offset'],
            'limit'     => empty($aArgs['limit']) ? 0 : $aArgs['limit'],
        ]);

        return $aDocuments;
    }

    public static function getById(array $args)
    {
        ValidatorModel::notEmpty($args, ['select', 'id']);
        ValidatorModel::arrayType($args, ['select']);

        $document = DatabaseModel::select([
            'select' => $args['select'],
            'table'  => ['main_documents'],
            'where'  => ['id = ?'],
            'data'   => [$args['id']]
        ]);

        if (empty($document[0])) {
            return [];
        }

        return $document[0];
    }

    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['title', 'sender', 'metadata', 'typist']);
        ValidatorModel::stringType($args, ['title', 'reference', 'description', 'sender', 'deadline', 'notes', 'link_id', 'metadata', 'mailing_id']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'main_documents_id_seq']);

        DatabaseModel::insert([
            'table'         => 'main_documents',
            'columnsValues' => [
                'id'            => $nextSequenceId,
                'title'         => $args['title'],
                'reference'     => $args['reference'],
                'description'   => $args['description'],
                'sender'        => $args['sender'],
                'deadline'      => $args['deadline'],
                'notes'         => $args['notes'],
                'link_id'       => $args['link_id'],
                'metadata'      => $args['metadata'],
                'mailing_id'    => $args['mailing_id'],
                'typist'        => $args['typist']
            ]
        ]);

        return $nextSequenceId;
    }

    public static function update(array $args)
    {
        ValidatorModel::notEmpty($args, ['set', 'where', 'data']);
        ValidatorModel::arrayType($args, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'main_documents',
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
            'table' => 'main_documents',
            'where' => $args['where'],
            'data'  => $args['data']
        ]);

        return true;
    }
}
