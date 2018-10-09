<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ParametersController
* @author  dev <dev@maarch.org>
* @ingroup core
*/

/**
 * @brief Parameter Controller
 * @author dev@maarch.org
 */

namespace Parameter\controllers;

use Group\models\ServiceModel;
use History\controllers\HistoryController;
use Parameter\models\ParameterModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;

class ParameterController
{
    public function get(Request $request, Response $response)
    {
        $parameters = ParameterModel::get();

        foreach ($parameters as $key => $parameter) {
            if (!empty($parameter['param_value_string'])) {
                $parameters[$key]['value'] = $parameter['param_value_string'];
            } elseif (is_int($parameter['param_value_int'])) {
                $parameters[$key]['value'] = $parameter['param_value_int'];
            } elseif (!empty($parameter['param_value_date'])) {
                $parameters[$key]['value'] = $parameter['param_value_date'];
            }
        }

        return $response->withJson(['parameters' => $parameters]);
    }

    public function getById(Request $request, Response $response, array $aArgs)
    {
        $parameter = ParameterModel::getById(['id' => $aArgs['id']]);

        if (empty($parameter)) {
            return $response->withStatus(400)->withJson(['errors' => 'Parameter not found']);
        }

        return $response->withJson(['parameter' => $parameter]);
    }

    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_parameters', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['id']) && preg_match("/^[\w-]*$/", $data['id']);
        $check = $check && (empty($data['param_value_int']) || Validator::intVal()->validate($data['param_value_int']));
        $check = $check && (empty($data['param_value_string']) || Validator::stringType()->validate($data['param_value_string']));
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $parameter = ParameterModel::getById(['id' => $data['id']]);
        if (!empty($parameter)) {
            return $response->withStatus(400)->withJson(['errors' => _PARAMETER_ID_ALREADY_EXISTS]);
        }

        ParameterModel::create($data);
        HistoryController::add([
            'tableName' => 'parameters',
            'recordId'  => $data['id'],
            'eventType' => 'ADD',
            'info'      => _PARAMETER_CREATION . " : {$data['id']}",
            'moduleId'  => 'parameter',
            'eventId'   => 'parameterCreation',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function update(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_parameters', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $parameter = ParameterModel::getById(['id' => $aArgs['id']]);
        if (empty($parameter)) {
            return $response->withStatus(400)->withJson(['errors' => 'Parameter not found']);
        }

        $data = $request->getParams();

        $check = (empty($data['param_value_int']) || Validator::intVal()->validate($data['param_value_int']));
        $check = $check && (empty($data['param_value_string']) || Validator::stringType()->validate($data['param_value_string']));
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $data['id'] = $aArgs['id'];
        ParameterModel::update($data);
        HistoryController::add([
            'tableName' => 'parameters',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _PARAMETER_MODIFICATION . " : {$aArgs['id']}",
            'moduleId'  => 'parameter',
            'eventId'   => 'parameterModification',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function delete(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_parameters', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        ParameterModel::delete(['id' => $aArgs['id']]);
        HistoryController::add([
            'tableName' => 'parameters',
            'recordId'  => $aArgs['id'],
            'eventType' => 'DEL',
            'info'      => _PARAMETER_SUPPRESSION . " : {$aArgs['id']}",
            'moduleId'  => 'parameter',
            'eventId'   => 'parameterSuppression',
        ]);

        $parameters = ParameterModel::get();
        foreach ($parameters as $key => $parameter) {
            if (!empty($parameter['param_value_string'])) {
                $parameters[$key]['value'] = $parameter['param_value_string'];
            } elseif (!empty($parameter['param_value_int'])) {
                $parameters[$key]['value'] = $parameter['param_value_int'];
            } elseif (!empty($parameter['param_value_date'])) {
                $parameters[$key]['value'] = $parameter['param_value_date'];
            }
        }

        return $response->withJson(['parameters' => $parameters]);
    }
}
