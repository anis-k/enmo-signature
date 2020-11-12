<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Workflow Template Controller
 * @author dev@maarch.org
 */

namespace Workflow\controllers;

use Group\controllers\PrivilegeController;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use SrcCore\models\ValidatorModel;
use User\models\UserModel;
use Workflow\models\WorkflowTemplateItemModel;
use Workflow\models\WorkflowTemplateModel;

class WorkflowTemplateController
{
    public function get(Request $request, Response $response)
    {
        $workflowTemplates = WorkflowTemplateModel::get([
            'select'    => ['id', 'title'],
            'where'     => ['owner = ?'],
            'data'      => [$GLOBALS['id']]
        ]);

        return $response->withJson(['workflowTemplates' => $workflowTemplates]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        $workflowTemplate = WorkflowTemplateModel::getById(['id' => $args['id'], 'select' => ['title', 'owner']]);
        if (empty($workflowTemplate)) {
            return $response->withStatus(400)->withJson(['errors' => 'Workflow template does not exist']);
        }

        if ($workflowTemplate['owner'] != $GLOBALS['id']) {
            return $response->withStatus(403)->withJson(['errors' => 'Workflow template out of perimeter']);
        }

        $rawWorkflowTemplatesUsers = WorkflowTemplateItemModel::get(['select' => ['*'], 'where' => ['workflow_template_id = ?'], 'data' => [$args['id']], 'orderBy' => ['sequence']]);
        $workflowTemplatesUsers = [];
        foreach ($rawWorkflowTemplatesUsers as $value) {
            $user['substituteUser'] = UserModel::getLabelledUserById(['id' => $value['user_id']]);

            $workflowTemplatesUsers[] = [
                'userId'        => $value['user_id'],
                'userLabel'     => UserModel::getLabelledUserById(['id' => $value['user_id']]),
                'mode'          => $value['mode'],
                'signatureMode' => $value['signature_mode'],
                'sequence'      => $value['sequence']
            ];
        }

        $workflowTemplate = [
            'title' => $workflowTemplate['title'],
            'items' => $workflowTemplatesUsers
        ];

        return $response->withJson(['workflowTemplate' => $workflowTemplate]);
    }

    public function create(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'indexation'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $body = $request->getParsedBody();
        if (!Validator::stringType()->notEmpty()->validate($body['title'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body title is empty or not a string']);
        } elseif (!Validator::arrayType()->notEmpty()->validate($body['items'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body items is empty or not an array']);
        }

        $control = WorkflowTemplateController::controlItems(['items' => $body['items']]);
        if (!empty($control['errors'])) {
            return $response->withStatus(400)->withJson(['errors' => $control['errors']]);
        }

        $workflowTemplateId = WorkflowTemplateModel::create([
            'title' => $body['title'],
            'owner' => $GLOBALS['id']
        ]);

        foreach ($body['items'] as $key => $item) {
            WorkflowTemplateItemModel::create([
                'workflow_template_id'  => $workflowTemplateId,
                'user_id'               => $item['userId'],
                'mode'                  => $item['mode'],
                'signature_mode'        => $item['signatureMode'],
                'sequence'              => $key
            ]);
        }

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'workflowTemplates',
            'objectId'      => $workflowTemplateId,
            'type'          => 'CREATION',
            'message'       => "{workflowTemplateAdded} : {$body['title']}"
        ]);

        return $response->withJson(['id' => $workflowTemplateId]);
    }

    public function delete(Request $request, Response $response, array $args)
    {
        $workflowTemplate = WorkflowTemplateModel::getById(['id' => $args['id'], 'select' => ['owner', 'title']]);
        if (empty($workflowTemplate)) {
            return $response->withStatus(400)->withJson(['errors' => 'Workflow template does not exist']);
        } elseif ($workflowTemplate['owner'] != $GLOBALS['id']) {
            return $response->withStatus(403)->withJson(['errors' => 'Workflow template out of perimeter']);
        }

        WorkflowTemplateModel::delete([
            'where' => ['id = ?'],
            'data'  => [$args['id']]
        ]);
        WorkflowTemplateItemModel::delete(['where' => ['workflow_template_id = ?'], 'data' => [$args['id']]]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'workflowTemplates',
            'objectId'      => $args['id'],
            'type'          => 'SUPPRESSION',
            'message'       => "{workflowTemplateDeleted} : {$workflowTemplate['title']}"
        ]);

        return $response->withStatus(204);
    }

    private static function controlItems(array $args)
    {
        ValidatorModel::notEmpty($args, ['items']);
        ValidatorModel::arrayType($args, ['items']);

        //TODO check mode + signature mode
        foreach ($args['items'] as $key => $item) {
            if (empty($item['userId'])) {
                return ['errors' => "Item[{$key}] userId is empty"];
            } elseif (empty($item['mode'])) {
                return ['errors' => "Item[{$key}] mode is empty"];
            } elseif (empty($item['signatureMode'])) {
                return ['errors' => "Item[{$key}] signatureMode is empty"];
            }

            $user = UserModel::getById(['id' => $item['userId'], 'select' => [1]]);
            if (empty($user)) {
                return ['errors' => 'User is not valid'];
            }
        }

        return true;
    }
}
