<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Workflow Template Model
 * @author dev@maarch.org
 */

namespace Workflow\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class WorkflowTemplateModel
{
    public static function get(array $args = [])
    {
        ValidatorModel::arrayType($args, ['select', 'where', 'data', 'orderBy']);

        $workflowTemplates = DatabaseModel::select([
            'select'    => $args['select'] ?? [1],
            'table'     => ['workflow_templates'],
            'where'     => $args['where'] ?? [],
            'data'      => $args['data'] ?? [],
            'order_by'  => $args['orderBy'] ?? []
        ]);

        return $workflowTemplates;
    }

    public static function getById(array $args)
    {
        ValidatorModel::notEmpty($args, ['id']);
        ValidatorModel::intVal($args, ['id']);
        ValidatorModel::arrayType($args, ['select']);

        $workflowTemplate = DatabaseModel::select([
            'select'    => $args['select'] ?? [1],
            'table'     => ['workflow_templates'],
            'where'     => ['id = ?'],
            'data'      => [$args['id']]
        ]);

        if (empty($workflowTemplate[0])) {
            return [];
        }

        return $workflowTemplate[0];
    }

    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['title', 'owner']);
        ValidatorModel::stringType($args, ['title']);
        ValidatorModel::intVal($args, ['owner']);

        $nextSequenceId = DatabaseModel::getNextSequenceValue(['sequenceId' => 'workflow_templates_id_seq']);

        DatabaseModel::insert([
            'table'         => 'workflow_templates',
            'columnsValues' => [
                'id'          => $nextSequenceId,
                'title'       => $args['title'],
                'owner'       => $args['owner']
            ]
        ]);

        return $nextSequenceId;
    }

    public static function update(array $args)
    {
        ValidatorModel::notEmpty($args, ['set', 'where', 'data']);
        ValidatorModel::arrayType($args, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'workflow_templates',
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
            'table' => 'workflow_templates',
            'where' => $args['where'],
            'data'  => $args['data']
        ]);

        return true;
    }
}
