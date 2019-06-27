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

use Group\models\GroupModel;
use Group\models\GroupPrivilegeModel;
use History\controllers\HistoryController;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use User\models\UserGroupModel;
use User\models\UserModel;

class GroupController
{
    public function get(Request $request, Response $response)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_groups'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $groups = GroupModel::get();

        return $response->withJson(['groups' => $groups]);
    }

    public function getById(Request $request, Response $response, array $args)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_groups'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        if (!Validator::intVal()->notEmpty()->validate($args['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Route id must be an integer']);
        }

        $group = GroupModel::getById(['id' => $args['id']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        $group['users'] = UserGroupModel::getByGroupId([
            'id'     => $group['id'],
            'select' => ['users.id', 'users.firstname', 'users.lastname']
        ]);

        $groupPrivileges = GroupPrivilegeModel::getPrivilegesByGroupId(['groupId' => $args['id']]);
        $groupPrivileges = array_column($groupPrivileges, 'privilege');

        $aPrivileges = PrivilegeController::PRIVILEGES;
        foreach ($aPrivileges as $key => $value) {
            if (in_array($value['id'], $groupPrivileges)) {
                $aPrivileges[$key]['checked'] = true;
            } else {
                $aPrivileges[$key]['checked'] = false;
            }
        }
        $group['privileges'] = $aPrivileges;

        return $response->withJson(['group' => $group]);
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

    public function update(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_groups'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $body = $request->getParsedBody();

        if (empty($body)) {
            return $response->withStatus(400)->withJson(['errors' => 'Body is not set or empty']);
        } elseif (!Validator::stringType()->notEmpty()->length(1, 128)->validate($body['label'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Body label is empty or not a string or longer than 128 caracteres']);
        } elseif (!Validator::intVal()->notEmpty()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Id must be an integer']);
        }

        $group = GroupModel::getById(['id' => $aArgs['id']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
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
            'type'       => 'MODIFICATION',
            'message'    => "{groupUpdated} : {$body['label']}"
        ]);

        return $response->withStatus(204);
    }

    public function delete(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_groups'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        if (!Validator::intVal()->notEmpty()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Id must be an integer']);
        }

        $group = GroupModel::getById(['id' => $aArgs['id']]);
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        }

        $groupCount = GroupModel::get(['select' => ['count(*) as nb']]);
        if ($groupCount[0]['nb'] == 0) {
            return $response->withStatus(400)->withJson(['errors' => 'This is the last group']);
        }

        UserGroupModel::delete(['where' => ['group_id = ?'], 'data' => [$aArgs['id']]]);
        GroupPrivilegeModel::delete(['where' => ['group_id = ?'], 'data' => [$aArgs['id']]]);
        GroupModel::delete(['where' => ['id = ?'], 'data' => [$aArgs['id']]]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'groups',
            'objectId'      => $aArgs['id'],
            'type'          => 'SUPPRESSION',
            'message'       => "{groupdeleted} : {$group['label']}"
        ]);

        return $response->withStatus(204);
    }

    public function updateGroupPrivilege(Request $request, Response $response, array $aArgs)
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
            'type'       => 'MODIFICATION',
            'message'    => "{privilegeUpdated} : {$aArgs['privilegeId']}"
        ]);

        return $response->withStatus(204);
    }

    public function addUser(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_groups']) && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        $body = $request->getParsedBody();
        if (!Validator::intVal()->notEmpty()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Id must be an integer']);
        } elseif (!Validator::intVal()->notEmpty()->validate($body['userId'])) {
            return $response->withStatus(400)->withJson(['errors' => 'userId must be an integer']);
        }

        $group = GroupModel::getById(['id' => $aArgs['id']]);
        $user  = UserModel::getById(['id' => $body['userId'], 'select' => ['firstname', 'lastname']]);
        
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        } elseif (empty($user)) {
            return $response->withStatus(400)->withJson(['errors' => 'User not found']);
        } elseif (UserGroupModel::hasGroup(['groupId' => $aArgs['id'], 'userId' => $body['userId']])) {
            return $response->withStatus(400)->withJson(['errors' => 'This user already has this group']);
        }

        UserGroupModel::addUser([
            'groupId' => $aArgs['id'],
            'userId'  => $body['userId']
        ]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'groups',
            'objectId'      => $aArgs['id'],
            'type'          => 'MODIFICATION',
            'message'       => "{userAdded} : {$user['firstname']} {$user['lastname']}"
        ]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'users',
            'objectId'      => $body['userId'],
            'type'          => 'MODIFICATION',
            'message'       => "{groupAdded} : {$group['label']}"
        ]);

        return $response->withStatus(204);
    }

    public function removeUser(Request $request, Response $response, array $aArgs)
    {
        if (!PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_groups']) && !PrivilegeController::hasPrivilege(['userId' => $GLOBALS['id'], 'privilege' => 'manage_users'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Privilege forbidden']);
        }

        if (!Validator::intVal()->notEmpty()->validate($aArgs['id'])) {
            return $response->withStatus(400)->withJson(['errors' => 'Id must be an integer']);
        } elseif (!Validator::intVal()->notEmpty()->validate($aArgs['userId'])) {
            return $response->withStatus(400)->withJson(['errors' => 'userId must be an integer']);
        }

        $group = GroupModel::getById(['id' => $aArgs['id']]);
        $user  = UserModel::getById(['id' => $aArgs['userId'], 'select' => ['firstname', 'lastname']]);
        
        if (empty($group)) {
            return $response->withStatus(400)->withJson(['errors' => 'Group not found']);
        } elseif (empty($user)) {
            return $response->withStatus(400)->withJson(['errors' => 'User not found']);
        }

        UserGroupModel::removeUser([
            'groupId' => $aArgs['id'],
            'userId'  => $aArgs['userId']
        ]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'groups',
            'objectId'      => $aArgs['id'],
            'type'          => 'MODIFICATION',
            'message'       => "{removedFromGroup} : {$user['firstname']} {$user['lastname']}"
        ]);

        HistoryController::add([
            'code'          => 'OK',
            'objectType'    => 'users',
            'objectId'      => $aArgs['userId'],
            'type'          => 'MODIFICATION',
            'message'       => "{removedFromGroup} : {$group['label']}"
        ]);

        return $response->withStatus(204);
    }
}
