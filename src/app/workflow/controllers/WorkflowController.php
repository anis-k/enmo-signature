<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Workflow Controller
* @author dev@maarch.org
*/

namespace Workflow\controllers;

use Document\controllers\DocumentController;
use Slim\Http\Request;
use Slim\Http\Response;
use User\controllers\UserController;
use User\models\UserModel;
use Workflow\models\WorkflowModel;

class WorkflowController
{
    public function getByDocumentId(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'userId' => $GLOBALS['id']]) && !UserController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $rawWorkflow = WorkflowModel::getByDocumentId(['select' => ['*'], 'documentId' => $args['id'], 'orderBy' => ['"order"']]);

        $workflow = [];
        foreach ($rawWorkflow as $value) {
            $workflow[] = [
                'userId'        => $value['user_id'],
                'userDisplay'   => UserModel::getLabelledUserById(['id' => $value['user_id']]),
                'mode'          => $value['mode'],
                'order'         => $value['order'],
                'status'        => $value['status'],
                'note'          => $value['note'],
                'processDate'   => $value['process_date'],
            ];
        }

        return $response->withJson(['workflow' => $workflow]);
    }
}
