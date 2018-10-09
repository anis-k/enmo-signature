<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.

* @brief   ActionController
* @author  dev <dev@maarch.org>
* @ingroup core
*/

namespace Action\controllers;

use History\controllers\HistoryController;
use Resource\models\ResModel;
use Respect\Validation\Validator;
use Action\models\ActionModel;
use SrcCore\models\CoreConfigModel;
use Status\models\StatusModel;
use Group\models\ServiceModel;
use Slim\Http\Request;
use Slim\Http\Response;

class ActionController
{
    public function get(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_actions', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        return $response->withJson(['actions' => ActionModel::get()]);
    }

    public function getById(Request $request, Response $response, $aArgs)
    {
        if (!Validator::intVal()->validate($aArgs['id'])) {
            return $response->withStatus(500)->withJson(['errors' => 'Id is not a numeric']);
        }

        $obj['action'] = ActionModel::getById(['id' => $aArgs['id']]);

        if (!empty($obj['action'])) {
            $obj['action']['history'] = ($obj['action']['history'] == 'Y');
            $obj['action']['is_system'] = ($obj['action']['is_system'] == 'Y');
            $obj['action']['create_id'] = ($obj['action']['create_id'] == 'Y');

            $actionCategories = [];
            foreach ($obj['action']['actionCategories'] as $key => $category) {
                $actionCategories[] = $category['category_id'];
            }
            $obj['action']['actionCategories'] = $actionCategories;

            $obj['categoriesList'] = ResModel::getCategories();
            if (empty($obj['action']['actionCategories'])) {
                $categoriesList = [];
                foreach ($obj['categoriesList'] as $key => $category) {
                    $categoriesList[] = $category['id'];
                }
                $obj['action']['actionCategories'] = $categoriesList;
            }


            $obj['statuses'] = StatusModel::get();
            array_unshift($obj['statuses'], ['id'=>'_NOSTATUS_', 'label_status'=> _UNCHANGED]);
            $obj['action_pagesList'] = ActionModel::getActionPages();
            array_unshift($obj['action_pagesList']['actionsPageList'], ['id' => '', 'label' => _NO_PAGE, 'name' => '', 'origin' => '']);
            $obj['keywordsList'] = ActionModel::getKeywords();
        }
  
        return $response->withJson($obj);
    }

    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'admin_actions', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $data = $this->manageValue($data);
        
        $errors = $this->control($data, 'create');
        if (!empty($errors)) {
            return $response->withStatus(500)->withJson(['errors' => $errors]);
        }
    
        $id = ActionModel::create($data);

        HistoryController::add([
            'tableName' => 'actions',
            'recordId'  => $id,
            'eventType' => 'ADD',
            'eventId'   => 'actionadd',
            'info'      => _ACTION_ADDED . ' : ' . $data['label_action']
        ]);

        return $response->withJson(['actionId' => $id]);
    }

    public function update(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_actions', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();
        $data['id'] = $aArgs['id'];

        $data    = $this->manageValue($data);
        $errors = $this->control($data, 'update');
      
        if (!empty($errors)) {
            return $response->withStatus(500)->withJson(['errors' => $errors]);
        }

        ActionModel::update($data);

        HistoryController::add([
            'tableName' => 'actions',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'eventId'   => 'actionup',
            'info'      => _ACTION_UPDATED. ' : ' . $data['label_action']
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function delete(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'admin_actions', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        if (!Validator::intVal()->validate($aArgs['id'])) {
            return $response->withStatus(500)->withJson(['errors' => 'Id is not a numeric']);
        }

        $action = ActionModel::getById(['id' => $aArgs['id']]);
        ActionModel::delete(['id' => $aArgs['id']]);

        HistoryController::add([
            'tableName' => 'actions',
            'recordId'  => $aArgs['id'],
            'eventType' => 'DEL',
            'eventId'   => 'actiondel',
            'info'      => _ACTION_DELETED. ' : ' . $action['label_action']
        ]);

        return $response->withJson(['actions' => ActionModel::get()]);
    }

    protected function control($aArgs, $mode)
    {
        $errors = [];
      
        $objs = StatusModel::get();

        foreach ($objs as $obj) {
            $status[] = $obj['id'];
        }
        array_unshift($status, '_NOSTATUS_');

        if (!(in_array($aArgs['id_status'], $status))) {
            $errors[]= 'Invalid Status';
        }

        if ($mode == 'update') {
            if (!Validator::intVal()->validate($aArgs['id'])) {
                $errors[] = 'Id is not a numeric';
            } else {
                $obj = ActionModel::getById(['id' => $aArgs['id']]);
            }
           
            if (empty($obj)) {
                $errors[] = 'Id ' .$aArgs['id']. ' does not exists';
            }
        }
           
        if (!Validator::notEmpty()->validate($aArgs['label_action']) ||
            !Validator::length(1, 255)->validate($aArgs['label_action'])) {
            $errors[] = 'Invalid label action';
        }

        if (!Validator::notEmpty()->validate($aArgs['id_status'])) {
            $errors[] = 'id_status is empty';
        }

        if (!Validator::notEmpty()->validate($aArgs['history']) || ($aArgs['history'] != 'Y' && $aArgs['history'] != 'N')) {
            $errors[]= 'Invalid history value';
        }

        return $errors;
    }

    public function initAction(Request $request, Response $response)
    {
        //default data
        $obj['action']['history']          = true;
        $obj['action']['keyword']          = '';
        $obj['action']['action_page']      = '';
        $obj['action']['id_status']        = '_NOSTATUS_';
        $obj['categoriesList']             = ResModel::getCategories();

        foreach ($obj['categoriesList'] as $key => $value) {
            $obj['categoriesList'][$key]['selected'] = true;
        }

        $obj['statuses'] = StatusModel::get();
        array_unshift($obj['statuses'], ['id'=>'_NOSTATUS_','label_status'=> _UNCHANGED]);
        $obj['action_pagesList'] = ActionModel::getActionPages();
        array_unshift($obj['action_pagesList']['actionsPageList'], ['id'=>'','label'=> _NO_PAGE, 'name'=>'', 'origin'=>'']);
        $obj['keywordsList'] = ActionModel::getKeywords();
        
        return $response->withJson($obj);
    }

    protected function manageValue($request)
    {
        foreach ($request  as $key => $value) {
            if (in_array($key, ['history'])) {
                if (empty($value)) {
                    $request[$key] = 'N';
                } else {
                    $request[$key] = 'Y';
                }
            }
        }
        return $request;
    }
}
