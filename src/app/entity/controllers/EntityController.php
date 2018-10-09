<?php

/**
* Copyright Maarch since 2008 under licence GPLv3.
* See LICENCE.txt file at the root folder for more details.
* This file is part of Maarch software.
*
*/

/**
* @brief Entity Controller
* @author dev@maarch.org
*/

namespace Entity\controllers;

use Basket\models\BasketModel;
use Entity\models\EntityModel;
use Entity\models\ListInstanceModel;
use Entity\models\ListTemplateModel;
use Group\models\ServiceModel;
use History\controllers\HistoryController;
use Resource\models\ResModel;
use Respect\Validation\Validator;
use Slim\Http\Request;
use Slim\Http\Response;
use Template\models\TemplateAssociationModel;
use User\models\UserEntityModel;
use User\models\UserModel;

class EntityController
{
    public function get(Request $request, Response $response)
    {
        return $response->withJson(['entities' => EntityModel::getAllowedEntitiesByUserId(['userId' => $GLOBALS['userId']])]);
    }

    public function getById(Request $request, Response $response, array $aArgs)
    {
        $entity = EntityModel::getById(['entityId' => $aArgs['id']]);
        if (empty($entity)) {
            return $response->withStatus(400)->withJson(['errors' => 'Entity not found']);
        }

        return $response->withJson(['entity' => $entity]);
    }

    public function getDetailledById(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'manage_entities', 'userId' => $GLOBALS['userId'], 'location' => 'entities', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $entity = EntityModel::getById(['entityId' => $aArgs['id']]);
        if (empty($entity)) {
            return $response->withStatus(400)->withJson(['errors' => 'Entity not found']);
        }

        $aEntities = EntityModel::getAllowedEntitiesByUserId(['userId' => $GLOBALS['userId']]);
        foreach ($aEntities as $aEntity) {
            if ($aEntity['entity_id'] == $aArgs['id'] && $aEntity['allowed'] == false) {
                return $response->withStatus(403)->withJson(['errors' => 'Entity out of perimeter']);
            }
        }

        $unneededRoles = ['visa', 'sign'];
        $entity['types'] = EntityModel::getTypes();
        $entity['roles'] = EntityModel::getRoles();
        $listTemplateTypes = ListTemplateModel::getTypes(['select' => ['difflist_type_roles'], 'where' => ['difflist_type_id = ?'], 'data' => ['entity_id']]);
        $rolesForService = empty($listTemplateTypes[0]['difflist_type_roles']) ? [] : explode(' ', $listTemplateTypes[0]['difflist_type_roles']);
        foreach ($entity['roles'] as $key => $role) {
            if (in_array($role['id'], $unneededRoles)) {
                unset($entity['roles'][$key]);
                continue;
            }
            if (in_array($role['id'], $rolesForService)) {
                $entity['roles'][$key]['available'] = true;
            } else {
                $entity['roles'][$key]['available'] = false;
            }
            if ($role['id'] == 'copy') {
                $entity['roles'][$key]['id'] = 'cc';
            }
        }

        $listTemplates = ListTemplateModel::get([
            'select'    => ['id', 'object_type', 'item_id', 'item_type', 'item_mode', 'title', 'description', 'sequence'],
            'where'     => ['object_id = ?'],
            'data'      => [$aArgs['id']]
        ]);

        $entity['listTemplate'] = [];
        foreach ($rolesForService as $role) {
            $role == 'copy' ? $entity['listTemplate']['cc'] = [] : $entity['listTemplate'][$role] = [];
        }
        $entity['visaTemplate'] = [];
        foreach ($listTemplates as $listTemplate) {
            if ($listTemplate['object_type'] == 'entity_id' && !empty($listTemplate['item_id'])) {
                $entity['listTemplate']['id'] = $listTemplate['id'];
                if ($listTemplate['item_type'] == 'user_id') {
                    $statusUser = UserModel::getByUserId(['select' => ['status', 'firstname', 'lastname'], 'userId' => $listTemplate['item_id']]);
                    if ($statusUser['status'] != 'DEL') {
                        $entity['listTemplate'][$listTemplate['item_mode']][] = [
                            'item_type'             => $listTemplate['item_type'],
                            'item_id'               => $listTemplate['item_id'],
                            'sequence'              => $listTemplate['sequence'],
                            'title'                 => $listTemplate['title'],
                            'description'           => $listTemplate['description'],
                            'labelToDisplay'        => $statusUser['firstname']. ' ' .$statusUser['lastname'],
                            'descriptionToDisplay'  => UserModel::getPrimaryEntityByUserId(['userId' => $listTemplate['item_id']])['entity_label']
                        ];
                    }
                } elseif ($listTemplate['item_type'] == 'entity_id') {
                    $entity['listTemplate'][$listTemplate['item_mode']][] = [
                        'item_type'             => $listTemplate['item_type'],
                        'item_id'               => $listTemplate['item_id'],
                        'sequence'              => $listTemplate['sequence'],
                        'title'                 => $listTemplate['title'],
                        'description'           => $listTemplate['description'],
                        'labelToDisplay'        => EntityModel::getById(['entityId' => $listTemplate['item_id'], 'select' => ['entity_label']])['entity_label'],
                        'descriptionToDisplay'  => ''
                    ];
                }
            }
            if ($listTemplate['object_type'] == 'VISA_CIRCUIT' && !empty($listTemplate['item_id'])) {
                $entity['visaTemplate'][] = [
                    'id'                    => $listTemplate['id'],
                    'item_type'             => $listTemplate['item_type'],
                    'item_id'               => $listTemplate['item_id'],
                    'item_mode'             => $listTemplate['item_mode'],
                    'sequence'              => $listTemplate['sequence'],
                    'title'                 => $listTemplate['title'],
                    'description'           => $listTemplate['description'],
                    'idToDisplay'           => UserModel::getLabelledUserById(['userId' => $listTemplate['item_id']]),
                    'descriptionToDisplay'  => UserModel::getPrimaryEntityByUserId(['userId' => $listTemplate['item_id']])['entity_label']
                ];
            }
        }

        $entity['users'] = EntityModel::getUsersById(['id' => $entity['entity_id'], 'select' => ['users.id','users.user_id', 'users.firstname', 'users.lastname', 'users.status']]);
        $children = EntityModel::get(['select' => [1], 'where' => ['parent_entity_id = ?'], 'data' => [$aArgs['id']]]);
        $entity['hasChildren'] = count($children) > 0;
        $documents = ResModel::get(['select' => [1], 'where' => ['destination = ?'], 'data' => [$aArgs['id']]]);
        $entity['documents'] = count($documents);
        $templates = TemplateAssociationModel::get(['select' => [1], 'where' => ['value_field = ?'], 'data' => [$aArgs['id']]]);
        $entity['templates'] = count($templates);
        $instances = ListInstanceModel::get(['select' => [1], 'where' => ['item_id = ?', 'item_type = ?'], 'data' => [$aArgs['id'], 'entity_id']]);
        $entity['instances'] = count($instances);
        $redirects = BasketModel::getGroupActionRedirect(['select' => [1], 'where' => ['entity_id = ?'], 'data' => [$aArgs['id']]]);
        $entity['redirects'] = count($redirects);
        $entity['canAdminUsers'] = ServiceModel::hasService(['id' => 'admin_users', 'userId' => $GLOBALS['userId'], 'location' => 'apps', 'type' => 'admin']);

        return $response->withJson(['entity' => $entity]);
    }

    public function create(Request $request, Response $response)
    {
        if (!ServiceModel::hasService(['id' => 'manage_entities', 'userId' => $GLOBALS['userId'], 'location' => 'entities', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['entity_id']) && preg_match("/^[\w-]*$/", $data['entity_id']) && (strlen($data['entity_id']) < 33);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['entity_label']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['short_label']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['entity_type']);
        if (!empty($data['email'])) {
            $check = $check && preg_match("/^[a-zA-Z0-9_.+-]+@[a-zA-Z0-9-]+\.[a-zA-Z0-9-.]+$/", $data['email']);
        }
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $existingEntity = EntityModel::getById(['entityId' => $data['entity_id'], 'select' => [1]]);
        if (!empty($existingEntity)) {
            return $response->withStatus(400)->withJson(['errors' => _ENTITY_ID_ALREADY_EXISTS]);
        }

        EntityModel::create($data);
        HistoryController::add([
            'tableName' => 'entities',
            'recordId'  => $data['entity_id'],
            'eventType' => 'ADD',
            'info'      => _ENTITY_CREATION . " : {$data['entity_id']}",
            'moduleId'  => 'entity',
            'eventId'   => 'entityCreation',
        ]);

        if (empty($data['parent_entity_id']) && $GLOBALS['userId'] != 'superadmin') {
            $user = UserModel::getByUserId(['userId' => $GLOBALS['userId'], 'select' => ['id']]);
            $primaryEntity = UserModel::getPrimaryEntityByUserId(['userId' => $GLOBALS['userId']]);
            $pEntity = 'N';
            if (empty($primaryEntity)) {
                $pEntity = 'Y';
            }

            UserEntityModel::addUserEntity(['id' => $user['id'], 'entityId' => $data['entity_id'], 'role' => '', 'primaryEntity' => $pEntity]);
            HistoryController::add([
                'tableName' => 'users',
                'recordId'  => $GLOBALS['userId'],
                'eventType' => 'UP',
                'info'      => _USER_ENTITY_CREATION . " : {$GLOBALS['userId']} {$data['entity_id']}",
                'moduleId'  => 'user',
                'eventId'   => 'userModification',
            ]);
        }

        return $response->withJson(['entities' => EntityModel::getAllowedEntitiesByUserId(['userId' => $GLOBALS['userId']])]);
    }

    public function update(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'manage_entities', 'userId' => $GLOBALS['userId'], 'location' => 'entities', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $entity = EntityModel::getById(['entityId' => $aArgs['id'], 'select' => [1]]);
        if (empty($entity)) {
            return $response->withStatus(400)->withJson(['errors' => 'Entity not found']);
        }

        $aEntities = EntityModel::getAllowedEntitiesByUserId(['userId' => $GLOBALS['userId']]);
        foreach ($aEntities as $aEntity) {
            if ($aEntity['entity_id'] == $aArgs['id'] && $aEntity['allowed'] == false) {
                return $response->withStatus(403)->withJson(['errors' => 'Entity out of perimeter']);
            }
        }

        $data = $request->getParams();

        $check = Validator::stringType()->notEmpty()->validate($data['entity_label']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['short_label']);
        $check = $check && Validator::stringType()->notEmpty()->validate($data['entity_type']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        $fatherAndSons = EntityModel::getEntityChildren(['entityId' => $aArgs['id']]);
        if (in_array($data['parent_entity_id'], $fatherAndSons)) {
            return $response->withStatus(400)->withJson(['errors' => _CAN_NOT_MOVE_IN_CHILD_ENTITY]);
        }

        $neededData = [
            'entity_label', 'short_label', 'entity_type', 'adrs_1', 'adrs_2', 'adrs_3',
            'zipcode', 'city', 'country', 'email', 'business_id', 'parent_entity_id',
            'entity_path', 'ldap_id', 'archival_agreement', 'archival_agency', 'entity_full_name'
        ];
        foreach ($data as $key => $value) {
            if (!in_array($key, $neededData)) {
                unset($data[$key]);
            }
        }
        EntityModel::update(['set' => $data, 'where' => ['entity_id = ?'], 'data' => [$aArgs['id']]]);
        HistoryController::add([
            'tableName' => 'entities',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _ENTITY_MODIFICATION . " : {$aArgs['id']}",
            'moduleId'  => 'entity',
            'eventId'   => 'entityModification',
        ]);

        if (empty($data['parent_entity_id']) && $GLOBALS['userId'] != 'superadmin') {
            $hasEntity = UserEntityModel::get(['select' => [1], 'where' => ['user_id = ?', 'entity_id = ?'], 'data' => [$GLOBALS['userId'], $aArgs['id']]]);
            if (empty($hasEntity)) {
                $user = UserModel::getByUserId(['userId' => $GLOBALS['userId'], 'select' => ['id']]);
                $primaryEntity = UserModel::getPrimaryEntityByUserId(['userId' => $GLOBALS['userId']]);
                $pEntity = 'N';
                if (empty($primaryEntity)) {
                    $pEntity = 'Y';
                }

                UserEntityModel::addUserEntity(['id' => $user['id'], 'entityId' => $aArgs['id'], 'role' => '', 'primaryEntity' => $pEntity]);
                HistoryController::add([
                    'tableName' => 'users',
                    'recordId'  => $GLOBALS['userId'],
                    'eventType' => 'UP',
                    'info'      => _USER_ENTITY_CREATION . " : {$GLOBALS['userId']} {$aArgs['id']}",
                    'moduleId'  => 'user',
                    'eventId'   => 'userModification',
                ]);
            }
        }

        return $response->withJson(['entities' => EntityModel::getAllowedEntitiesByUserId(['userId' => $GLOBALS['userId']])]);
    }

    public function delete(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'manage_entities', 'userId' => $GLOBALS['userId'], 'location' => 'entities', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $entity = EntityModel::getById(['entityId' => $aArgs['id'], 'select' => [1]]);
        if (empty($entity)) {
            return $response->withStatus(400)->withJson(['errors' => 'Entity not found']);
        }

        $aEntities = EntityModel::getAllowedEntitiesByUserId(['userId' => $GLOBALS['userId']]);
        foreach ($aEntities as $aEntity) {
            if ($aEntity['entity_id'] == $aArgs['id'] && $aEntity['allowed'] == false) {
                return $response->withStatus(403)->withJson(['errors' => 'Entity out of perimeter']);
            }
        }

        $listTemplates = ListTemplateModel::get(['select' => [1], 'where' => ['object_id = ?'], 'data' => [$aArgs['id']]]);
        $children = EntityModel::get(['select' => [1], 'where' => ['parent_entity_id = ?'], 'data' => [$aArgs['id']]]);
        $documents = ResModel::get(['select' => [1], 'where' => ['destination = ?'], 'data' => [$aArgs['id']]]);
        $users = EntityModel::getUsersById(['select' => [1], 'id' => $aArgs['id']]);
        $templates = TemplateAssociationModel::get(['select' => [1], 'where' => ['value_field = ?'], 'data' => [$aArgs['id']]]);
        $instances = ListInstanceModel::get(['select' => [1], 'where' => ['item_id = ?', 'item_type = ?'], 'data' => [$aArgs['id'], 'entity_id']]);
        $redirects = BasketModel::getGroupActionRedirect(['select' => [1], 'where' => ['entity_id = ?'], 'data' => [$aArgs['id']]]);

        $allowedCount = count($listTemplates) + count($children) + count($documents) + count($users) + count($templates) + count($instances) + count($redirects);
        if ($allowedCount > 0) {
            return $response->withStatus(400)->withJson(['errors' => 'Entity is still used']);
        }

        EntityModel::delete(['where' => ['entity_id = ?'], 'data' => [$aArgs['id']]]);
        HistoryController::add([
            'tableName' => 'entities',
            'recordId'  => $aArgs['id'],
            'eventType' => 'DEL',
            'info'      => _ENTITY_SUPPRESSION . " : {$aArgs['id']}",
            'moduleId'  => 'entity',
            'eventId'   => 'entitySuppression',
        ]);

        return $response->withJson(['entities' => EntityModel::getAllowedEntitiesByUserId(['userId' => $GLOBALS['userId']])]);
    }

    public function reassignEntity(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'manage_entities', 'userId' => $GLOBALS['userId'], 'location' => 'entities', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $dyingEntity = EntityModel::getById(['entityId' => $aArgs['id'], 'select' => ['parent_entity_id']]);
        $successorEntity = EntityModel::getById(['entityId' => $aArgs['newEntityId'], 'select' => [1]]);
        if (empty($dyingEntity) || empty($successorEntity)) {
            return $response->withStatus(400)->withJson(['errors' => 'Entity does not exist']);
        }
        $entities = EntityModel::getAllowedEntitiesByUserId(['userId' => $GLOBALS['userId']]);
        foreach ($entities as $entity) {
            if (($entity['entity_id'] == $aArgs['id'] && $entity['allowed'] == false) || ($entity['entity_id'] == $aArgs['newEntityId'] && $entity['allowed'] == false)) {
                return $response->withStatus(403)->withJson(['errors' => 'Entity out of perimeter']);
            }
        }

        //Documents
        ResModel::update(['set' => ['destination' => $aArgs['newEntityId']], 'where' => ['destination = ?', 'status != ?'], 'data' => [$aArgs['id'], 'DEL']]);

        //Users
        $users = UserEntityModel::get(['select' => ['user_id', 'entity_id', 'primary_entity'], 'where' => ['entity_id = ? OR entity_id = ?'], 'data' => [$aArgs['id'], $aArgs['newEntityId']]]);
        $tmpUsers = [];
        $doubleUsers = [];
        foreach ($users as $user) {
            if (in_array($user['user_id'], $tmpUsers)) {
                $doubleUsers[] = $user['user_id'];
            }
            $tmpUsers[] = $user['user_id'];
        }
        foreach ($users as $user) {
            if (in_array($user['user_id'], $doubleUsers)) {
                if ($user['entity_id'] == $aArgs['id'] && $user['primary_entity'] == 'N') {
                    UserEntityModel::delete(['where' => ['user_id = ?', 'entity_id = ?'], 'data' => [$user['user_id'], $aArgs['id']]]);
                } elseif ($user['entity_id'] == $aArgs['id'] && $user['primary_entity'] == 'Y') {
                    UserEntityModel::delete(['where' => ['user_id = ?', 'entity_id = ?'], 'data' => [$user['user_id'], $aArgs['newEntityId']]]);
                }
            }
        }
        UserEntityModel::update(['set' => ['entity_id' => $aArgs['newEntityId']], 'where' => ['entity_id = ?'], 'data' => [$aArgs['id']]]);

        //Entities
        $entities = EntityModel::get(['select' => ['entity_id', 'parent_entity_id'], 'where' => ['parent_entity_id = ?'], 'data' => [$aArgs['id']]]);
        foreach ($entities as $entity) {
            if ($entity['entity_id'] = $aArgs['newEntityId']) {
                EntityModel::update(['set' => ['parent_entity_id' => $dyingEntity['parent_entity_id']], 'where' => ['entity_id = ?'], 'data' => [$aArgs['newEntityId']]]);
            } else {
                EntityModel::update(['set' => ['parent_entity_id' => $aArgs['newEntityId']], 'where' => ['entity_id = ?'], 'data' => [$entity['entity_id']]]);
            }
        }

        //Baskets
        BasketModel::updateGroupActionRedirect(['set' => ['entity_id' => $aArgs['newEntityId']], 'where' => ['entity_id = ?'], 'data' => [$aArgs['id']]]);
        //ListInstances
        ListInstanceModel::update(['set' => ['item_id' => $aArgs['newEntityId']], 'where' => ['item_id = ?', 'item_type = ?'], 'data' => [$aArgs['id'], 'entity_id']]);
        //ListTemplates
        ListTemplateModel::delete(['where' => ['object_id = ?'], 'data' => [$aArgs['id']]]);
        //Templates
        TemplateAssociationModel::update(['set' => ['value_field' => $aArgs['newEntityId']], 'where' => ['value_field = ?'], 'data' => [$aArgs['id']]]);


        EntityModel::delete(['where' => ['entity_id = ?'], 'data' => [$aArgs['id']]]);
        HistoryController::add([
            'tableName' => 'entities',
            'recordId'  => $aArgs['id'],
            'eventType' => 'DEL',
            'info'      => _ENTITY_SUPPRESSION . " : {$aArgs['id']}",
            'moduleId'  => 'entity',
            'eventId'   => 'entitySuppression',
        ]);

        return $response->withJson(['entities' => EntityModel::getAllowedEntitiesByUserId(['userId' => $GLOBALS['userId']])]);
    }

    public function updateStatus(Request $request, Response $response, array $aArgs)
    {
        if (!ServiceModel::hasService(['id' => 'manage_entities', 'userId' => $GLOBALS['userId'], 'location' => 'entities', 'type' => 'admin'])) {
            return $response->withStatus(403)->withJson(['errors' => 'Service forbidden']);
        }

        $entity = EntityModel::getById(['entityId' => $aArgs['id'], 'select' => [1]]);
        if (empty($entity)) {
            return $response->withStatus(400)->withJson(['errors' => 'Entity not found']);
        }

        $aEntities = EntityModel::getAllowedEntitiesByUserId(['userId' => $GLOBALS['userId']]);
        foreach ($aEntities as $aEntity) {
            if ($aEntity['entity_id'] == $aArgs['id'] && $aEntity['allowed'] == false) {
                return $response->withStatus(403)->withJson(['errors' => 'Entity out of perimeter']);
            }
        }

        $data = $request->getParams();
        $check = Validator::stringType()->notEmpty()->validate($data['method']);
        if (!$check) {
            return $response->withStatus(400)->withJson(['errors' => 'Bad Request']);
        }

        if ($data['method'] == 'disable') {
            $status = 'N';
        } else {
            $status = 'Y';
        }
        $fatherAndSons = EntityModel::getEntityChildren(['entityId' => $aArgs['id']]);

        EntityModel::update(['set' => ['enabled' => $status], 'where' => ['entity_id in (?)'], 'data' => [$fatherAndSons]]);
        HistoryController::add([
            'tableName' => 'entities',
            'recordId'  => $aArgs['id'],
            'eventType' => 'UP',
            'info'      => _ENTITY_MODIFICATION . " : {$aArgs['id']}",
            'moduleId'  => 'entity',
            'eventId'   => 'entityModification',
        ]);

        return $response->withJson(['success' => 'success']);
    }

    public function getTypes(Request $request, Response $response)
    {
        return $response->withJson(['types' => EntityModel::getTypes()]);
    }
}
