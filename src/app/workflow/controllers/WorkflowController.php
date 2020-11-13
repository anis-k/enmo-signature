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
use Document\models\DocumentModel;
use Group\controllers\PrivilegeController;
use Slim\Http\Request;
use Slim\Http\Response;
use User\models\UserModel;
use Workflow\models\WorkflowModel;

class WorkflowController
{
    public function getByDocumentId(Request $request, Response $response, array $args)
    {
        if (!DocumentController::hasRightById(['id' => $args['id'], 'userId' => $GLOBALS['id']]) && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $rawWorkflow = WorkflowModel::getByDocumentId(['select' => ['*'], 'documentId' => $args['id'], 'orderBy' => ['"order"']]);

        $workflow = [];
        foreach ($rawWorkflow as $value) {
            if (!empty($value['process_date'])) {
                $date = new \DateTime($value['process_date']);
                $value['process_date'] = $date->format('d-m-Y H:i');
            }

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

    public function suspend(Request $request, Response $response, array $args)
    {
        $document = DocumentModel::getById(['select' => ['typist'], 'id' => $args['id']]);
        if (empty($document)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
        } elseif ($document['typist'] != $GLOBALS['id'] && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $workflows = WorkflowModel::get([
            'select'    => ['id'],
            'where'     => ['main_document_id = ?', 'status is null'],
            'data'      => [$args['id']]
        ]);
        if (empty($workflows)) {
            return $response->withStatus(400)->withJson(['errors' => 'Workflow is over or already suspended']);
        }

        $workflowsId = array_column($workflows, 'id');
        WorkflowModel::update([
            'set'   => ['status' => 'STOP', 'process_date' => 'CURRENT_TIMESTAMP'],
            'where' => ['id in (?)'],
            'data'  => [$workflowsId]
        ]);

        return $response->withStatus(204);
    }

    public function reactivate(Request $request, Response $response, array $args)
    {
        $document = DocumentModel::getById(['select' => ['typist'], 'id' => $args['id']]);
        if (empty($document)) {
            return $response->withStatus(400)->withJson(['errors' => 'Document does not exist']);
        } elseif ($document['typist'] != $GLOBALS['id'] && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_documents'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Document out of perimeter']);
        }

        $workflows = WorkflowModel::get([
            'select'    => ['id'],
            'where'     => ['main_document_id = ?', 'status = ?'],
            'data'      => [$args['id'], 'STOP']
        ]);
        if (empty($workflows)) {
            return $response->withStatus(400)->withJson(['errors' => 'Workflow is not suspended']);
        }

        $workflowsId = array_column($workflows, 'id');
        WorkflowModel::update([
            'set'   => ['status' => null, 'process_date' => null],
            'where' => ['id in (?)'],
            'data'  => [$workflowsId]
        ]);

        return $response->withStatus(204);
    }
}
