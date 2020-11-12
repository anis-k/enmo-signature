<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Workflow Template Item Model
 * @author dev@maarch.org
 */

namespace Workflow\models;

use SrcCore\models\DatabaseModel;
use SrcCore\models\ValidatorModel;

class WorkflowTemplateItemModel
{
    public static function get(array $args = [])
    {
        ValidatorModel::arrayType($args, ['select', 'where', 'data', 'orderBy']);

        $templatesUsers = DatabaseModel::select([
            'select'    => $args['select'] ?? [1],
            'table'     => ['workflow_templates_items'],
            'where'     => $args['where'] ?? [],
            'data'      => $args['data'] ?? [],
            'order_by'  => $args['orderBy'] ?? []
        ]);

        return $templatesUsers;
    }

    public static function create(array $args)
    {
        ValidatorModel::notEmpty($args, ['workflow_template_id', 'user_id', 'mode', 'signature_mode']);
        ValidatorModel::stringType($args, ['mode', 'signature_mode']);
        ValidatorModel::intVal($args, ['workflow_template_id', 'user_id', 'sequence']);

        DatabaseModel::insert([
            'table'         => 'workflow_templates_items',
            'columnsValues' => [
                'workflow_template_id'  => $args['workflow_template_id'],
                'user_id'               => $args['user_id'],
                'mode'                  => $args['mode'],
                'signature_mode'        => $args['signature_mode'],
                'sequence'              => $args['sequence']
            ]
        ]);

        return true;
    }

    public static function update(array $args)
    {
        ValidatorModel::notEmpty($args, ['set', 'where', 'data']);
        ValidatorModel::arrayType($args, ['set', 'where', 'data']);

        DatabaseModel::update([
            'table' => 'workflow_templates_items',
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
            'table' => 'workflow_templates_items',
            'where' => $args['where'],
            'data'  => $args['data']
        ]);

        return true;
    }
}
