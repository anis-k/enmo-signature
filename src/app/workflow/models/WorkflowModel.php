<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Workflow Model
* @author dev@maarch.org
*/

namespace Workflow\models;

use SrcCore\models\ValidatorModel;
use SrcCore\models\DatabaseModel;

class WorkflowModel
{
    public static function get(array $args)
    {
        ValidatorModel::notEmpty($args, ['select']);
        ValidatorModel::arrayType($args, ['select', 'where', 'data', 'orderBy']);
        ValidatorModel::intType($args, ['limit', 'offset']);

        $workflows = DatabaseModel::select([
            'select'    => $args['select'],
            'table'     => ['workflows'],
            'where'     => empty($args['where']) ? [] : $args['where'],
            'data'      => empty($args['data']) ? [] : $args['data'],
            'orderBy'   => empty($args['orderBy']) ? [] : $args['orderBy'],
            'offset'    => empty($args['offset']) ? 0 : $args['offset'],
            'limit'     => empty($args['limit']) ? 0 : $args['limit'],
        ]);

        return $workflows;
    }

    public static function getByDocumentId(array $args)
    {
        ValidatorModel::notEmpty($args, ['select', 'documentId']);
        ValidatorModel::intVal($args, ['documentId']);
        ValidatorModel::arrayType($args, ['select', 'orderBy']);

        $workflow = DatabaseModel::select([
            'select'    => $args['select'],
            'table'     => ['workflows'],
            'where'     => ['main_document_id = ?'],
            'data'      => [$args['documentId']],
            'orderBy'   => empty($args['orderBy']) ? [] : $args['orderBy'],
        ]);

        if (empty($workflow)) {
            return [];
        }

        return $workflow;
    }

    public static function getCurrentStep(array $args)
    {
        ValidatorModel::notEmpty($args, ['select', 'documentId']);
        ValidatorModel::intVal($args, ['documentId']);
        ValidatorModel::arrayType($args, ['select']);

        $currentStep = DatabaseModel::select([
            'select'    => $args['select'],
            'table'     => ['workflows'],
            'where'     => ['main_document_id = ?', 'process_date is null'],
            'data'      => [$args['documentId']],
            'orderBy'   => ['"order"'],
            'limit'     => 1,
        ]);

        if (empty($currentStep[0])) {
            return [];
        }

        return $currentStep[0];
    }

    public static function update(array $args)
    {
        ValidatorModel::notEmpty($args, ['set', 'where', 'data']);
        ValidatorModel::arrayType($args, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'workflows',
            'set'   => $args['set'],
            'where' => $args['where'],
            'data'  => $args['data']
        ]);

        return true;
    }
}
