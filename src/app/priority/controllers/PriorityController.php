<?php

/**
 * Copyright Maarch since 2008 under licence GPLv3.
 * See LICENCE.txt file at the root folder for more details.
 * This file is part of Maarch software.
 *
 */

/**
 * @brief Priority Controller
 * @author dev@maarch.org
 */

namespace Priority\controllers;

use Group\models\ServiceModel;
use History\controllers\HistoryController;
use Priority\models\PriorityModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;

class PriorityController
{
    public function get(Request $request, Response $response)
    {
        return $response->withJson(['priorities' => PriorityModel::get()]);
    }

    public function getById(Request $request, Response $response, array $aArgs)
    {
        $priotity = PriorityModel::getById(['id' => $aArgs['id']]);

        if (empty($priotity)) {
            return $response->withStatus(400)->withJson(['errors' => 'Priority not found']);
        }

        return $response->withJson(['priority'  => $priotity]);
    }

    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_priorities', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $check = Validator::stringType()->notEmpty()->validate($data['label']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['color']);
        $check = $check && (Validator::intVal()->notEmpty()->validate($data['delays']) || $data['delays'] == null || $data['delays'] == 0);
        $check = $check && Validator::boolType()->validate($data['working_days']);
        $check = $check && Validator::boolType()->validate($data['default_priority']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        if ($data['default_priority']) {
            PriorityModel::resetDefaultPriority();
        }
        $data['working_days'] = $data['working_days'] ? 'true' : 'false';
        $data['default_priority'] = $data['default_priority'] ? 'true' : 'false';

        $id = PriorityModel::create($data);
        HistoryController::add([
            'tableName' => 'priorities',
            'recordId'  => $id,
            'eventType' => 'ADD',
            'info'      => _PRIORITY_CREATION . " : {$data['label']}",
            'moduleId'  => 'priority',
            'eventId'   => 'priorityCreation',
        ]);

        return $response->withJson(['priority'  => $id]);
    }

    public function update(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_priorities', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $check = Validator::stringType()->notEmpty()->validate($data['label']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['color']);
        $check = $check && (Validator::intVal()->notEmpty()->validate($data['delays']) || $data['delays'] == null);
        $check = $check && Validator::boolType()->validate($data['working_days']);
        $check = $check && Validator::boolType()->validate($data['default_priority']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        if ($data['default_priority']) {
            PriorityModel::resetDefaultPriority();
        }
        $data['id'] = $aArgs['id'];
        $data['working_days'] = empty($data['working_days']) ? 'false' : 'true';
        $data['default_priority'] = empty($data['default_priority']) ? 'false' : 'true';

        PriorityModel::update($data);
        HistoryController::add([
            'tableName' => 'priorities',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _PRIORITY_MODIFICATION . " : {$data['label']}",
            'moduleId'  => 'priority',
            'eventId'   => 'priorityModification',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function delete(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_priorities', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        PriorityModel::delete(['id' => $aArgs['id']]);
        HistoryController::add([
            'tableName' => 'priorities',
            'recordId'  => $aArgs['id'],
            'eventType' => 'DEL',
            'info'      => _PRIORITY_SUPPRESSION . " : {$aArgs['id']}",
            'moduleId'  => 'priority',
            'eventId'   => 'prioritySuppression',
        ]);

        return $response->withJson(['priorities' => PriorityModel::get()]);
    }

    public function getSorted(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_priorities', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $priorities = PriorityModel::get([
            'select'    => ['id', 'label', '"order"'],
            'orderBy'   => ['"order" NULLS LAST']
        ]);

        return $response->withJson(['priotities' => $priorities]);
    }

    public function updateSort(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_priorities', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        foreach ($data as $key => $priorityToUpdate) {
            if ($key != $priorityToUpdate['order']) {
                PriorityModel::updateOrder(['id' => $priorityToUpdate['id'], 'order' => $key]);
            }
        }

        HistoryController::add([
            'tableName' => 'priorities',
            'recordId'  => $GLOBALS['userId'],
            'eventType' => 'UP',
            'info'      => _PRIORITY_SORT_MODIFICATION,
            'moduleId'  => 'priority',
            'eventId'   => 'priorityModification',
        ]);

        $priorities = PriorityModel::get([
            'select'    => ['id', 'label', '"order"'],
            'orderBy'   => ['"order" NULLS LAST']
        ]);

        return $response->withJson(['priorities' => $priorities]);
    }
}
