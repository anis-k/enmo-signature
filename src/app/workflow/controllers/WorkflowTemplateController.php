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

use Document\controllers\DocumentController;
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

        $rawWorkflowTemplatesUsers = WorkflowTemplateItemModel::get(['select' => ['*'], 'where' => ['workflow_template_id = ?'], 'data' => [$args['id']], 'orderBy' => ['order']]);
        $workflowTemplatesUsers = [];
        foreach ($rawWorkflowTemplatesUsers as $value) {
            $user['substituteUser'] = UserModel::getLabelledUserById(['id' => $value['user_id']]);

            $workflowTemplatesUsers[] = [
                'userId'        => $value['user_id'],
                'userLabel'     => UserModel::getLabelledUserById(['id' => $value['user_id']]),
                'mode'          => $value['mode'],
                'signatureMode' => $value['signature_mode'],
                'order'         => $value['order']
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

        $i = 1;
        foreach ($body['items'] as $item) {
            WorkflowTemplateItemModel::create([
                'workflow_template_id'  => $workflowTemplateId,
                'user_id'               => $item['userId'],
                'mode'                  => $item['mode'],
                'signature_mode'        => $item['signatureMode'],
                'order'                 => $i
            ]);
            ++$i;
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

        foreach ($args['items'] as $key => $item) {
            if (empty($item['userId'])) {
                return ['errors' => "Item[{$key}] userId is empty"];
            } elseif (empty($item['mode'])) {
                return ['errors' => "Item[{$key}] mode is empty"];
            } elseif (empty($item['signatureMode'])) {
                return ['errors' => "Item[{$key}] signatureMode is empty"];
            }

            if (!in_array($item['mode'], DocumentController::MODES)) {
                return ['errors' => "Item[{$key}] mode is not valid"];
            }
            $user = UserModel::getById(['id' => $item['userId'], 'select' => ['signature_modes']]);
            if (empty($user)) {
                return ['errors' => 'User is not valid'];
            }
            $user['signature_modes'] = json_decode($user['signature_modes'], true);
            if (!in_array($item['signatureMode'], $user['signature_modes'])) {
                return ['errors' => "Item[{$key}] signatureMode is not valid"];
            }
        }

        return true;
    }
}
