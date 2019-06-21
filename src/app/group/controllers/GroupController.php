<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Group Controller
* @author dev@maarch.org
*/

namespace Group\controllers;

use Group\controllers\PrivilegeController;
use Group\models\GroupModel;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use User\models\UserGroupModel;
use Group\models\GroupPrivilegeModel;

class GroupController
{
    public function get(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_groups'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $groups = GroupModel::get([]);

        return $response->withJson(['groups' => $groups]);
    }

    public function create(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_groups'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $body = $request->getParsedBody();

        if (empty($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is not set or empty']);
        } elseif (!Validator::stringType()->notEmpty()->length(1, 128)->validate($body['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string or longer than 128 caracteres']);
        }

        $id = GroupModel::create(['label' => $body['label']]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'groups',
            'objectId'      => $id,
            'type'          => 'CREATION',
            'message'       => "{groupAdded} : {$body['label']}"
        ]);

        return $response->withJson(['id' => $id]);
    }

    public function update(Request $request, Response $response, $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_groups'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $body = $request->getParsedBody();

        $group = GroupModel::getById(['id' => $aArgs['id']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        if (empty($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is not set or empty']);
        } elseif (!Validator::stringType()->notEmpty()->length(1, 128)->validate($body['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string or longer than 128 caracteres']);
        }

        GroupModel::update([
            'set' => ['label' => $body['label']],
            'where' => ['id = ?'],
            'data'  => [$aArgs['id']]
        ]);

        HistoryController::add([
            'code'       => 'OK',
            'objectType' => 'groups',
            'objectId'   => $aArgs['id'],
            'type'       => 'UPDATE',
            'message'    => "{groupUpdated} : {$body['label']}"
        ]);

        return $response->withStatus(204);
    }

    public function updateGroupPrivilege(Request $request, Response $response, $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_groups'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $body = $request->getParsedBody();

        if (empty($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is not set or empty']);
        } elseif (!Validator::stringType()->notEmpty()->validate($aArgs['privilegeId'])) {
            return $response->withStatus(400)->withJson(['errors' => 'privilegeId is empty']);
        } elseif (!Validator::boolType()->validate($body['checked'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body checked is empty']);
        } elseif (!Validator::intVal()->notEmpty()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Id must be an integer']);
        }

        $group = GroupModel::getById(['id' => $aArgs['id']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        if ($body['checked'] === true && !empty(GroupPrivilegeModel::getPrivileges(['select' => [1], 'where' => ['privilege = ?', 'group_id = ?'], 'data' => [$aArgs['privilegeId'], $aArgs['id']]]))) {
            return $response->withStatus(400)->withJson(['errors' => 'Privilege is already linked to this group']);
        }

        if ($body['checked']) {
            GroupPrivilegeModel::addPrivilege(['groupId' => $aArgs['id'], 'privilegeId' => $aArgs['privilegeId']]);
        } else {
            GroupPrivilegeModel::deletePrivilege(['groupId' => $aArgs['id'], 'privilegeId' => $aArgs['privilegeId']]);
        }

        HistoryController::add([
            'code'       => 'OK',
            'objectType' => 'groups',
            'objectId'   => $aArgs['id'],
            'type'       => 'UPDATE',
            'message'    => "{privilegeUpdated} : {$aArgs['privilegeId']}"
        ]);

        return $response->withStatus(204);
    }

    public function delete(Request $request, Response $response, $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_groups'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $group = GroupModel::getById(['id' => $aArgs['id']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        UserGroupModel::delete(['where' => ['group_id = ?'], 'data' => [$aArgs['id']]]);
        GroupPrivilegeModel::delete(['where' => ['group_id = ?'], 'data' => [$aArgs['id']]]);
        GroupModel::delete(['where' => ['id = ?'], 'data' => [$aArgs['id']]]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'groups',
            'objectId'      => $aArgs['id'],
            'type'          => 'DELETE',
            'message'       => "{groupdeleted} : {$group['label']}"
        ]);

        return $response->withStatus(204);
    }

    public function getById(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_groups'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $group = GroupModel::getById(['id' => $aArgs['id']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        $group['users'] = UserGroupModel::getByGroupId([
            'id'     => $group['id'],
            'select' => ['users.id', 'users.firstname', 'users.lastname']
        ]);

        return $response->withJson(['group' => $group]);
    }
}
